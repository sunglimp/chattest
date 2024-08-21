import { Injectable, OnInit } from "@angular/core";
import { HttpClient, HttpRequest } from "@angular/common/http";
import { BehaviorSubject } from "rxjs";
import { Client, Permissions, Chat, Message } from "../models/client.model";
import { HttpHeaders } from "@angular/common/http";
import * as moment from "moment";

const httpOptions = {
  headers: new HttpHeaders({
    Authorization: "Bearer " + window["USER"].api_token
  })
};

@Injectable({
  providedIn: "root"
})
export class ChatService implements OnInit {
  taggedIds: any[] = [];
  chats: any[];
  chatSubject = new BehaviorSubject(<Client>{});
  chatObservable = this.chatSubject.asObservable();
  viewFileSubject = new BehaviorSubject(<Object>{});
  viewFileObservable = this.viewFileSubject.asObservable();
  keyCodeSubject = new BehaviorSubject(<Object>{});
  keyCodeObservale = this.keyCodeSubject.asObservable();
  userSubject = new BehaviorSubject(<Object>{});
  userObservable = this.userSubject.asObservable();
  closeSubject = new BehaviorSubject(<boolean>{});
  closeObservable = this.closeSubject.asObservable();
  chatTicketTypeSubject = new BehaviorSubject(<Object>{});
  chatTicketTypeObservable = this.chatTicketTypeSubject.asObservable();
  dataLanguage = new BehaviorSubject(<Object>{});
  languageObservable = this.dataLanguage.asObservable();
  agentId: number = -1;
  hostUrl: string = "";
  agentName: string = "";
  channelName: string = "";
  agentOnlineStatus: number = 0;
  organizationId: number = -1;
  route: string = "";
  accessToken: string = "";
  negetiveInfinity: number = -1000000;
  colorCodes: string[] = [
    "#e83e8c",
    "#0070bd",
    "#21b573",
    "#fd7e14",
    "#fab03b",
    "#1c819e",
    "#34a7b2",
    "#ffa45c",
    "#665c84",
    "#714288",
    "#843b62",
    "#69779b",
    "#db2d43",
    "#20716a",
    "#587850",
    "#26baee",
    "#7874f2",
    "#cb9b42",
    "#005792",
    "#f08181",
    "#616f39",
    "#12e6c8",
    "#fadbac",
    "#8b104e",
    "#f4a9c7",
    "#3c415e"
  ]; // color codes for chat circles
  allowedAttachmentFormats = [
    ".mp3",
    ".mpeg",
    ".wav",
    ".oga",
    ".mpga",
    ".mpeg",
    ".ogx",
    ".ogv",
    ".webm",
    "qt",
    ".jpeg",
    ".jpg",
    ".png",
    ".gif",
    ".bmp",
    ".txt",
    ".mp4",
    ".m4a",
    ".m4v",
    ".webm",
    ".mpeg4",
    ".3gp",
    ".mov",
    ".avi",
    ".mpegps",
    ".wmv",
    ".flv",
    ".ogg",
    ".doc",
    ".docx",
    ".3gpp",
    ".mpg",
    ".mkv",
    ".m4v",
    ".xls",
    ".xlsx",
    ".ppt",
    ".pptx",
    ".pdf",
    ".psd",
    ".zip",
    ".rar"
  ];
  userPermissions: Permissions;
  isSuperAdmin: boolean;
  currentLanguage: String = "";
  ticketIsMinimized: boolean = false;
  lqsIsMinimized: boolean = false;
  chatQueueCount: number;

  constructor(private http: HttpClient) {
    this.agentId = window["USER"].id;
    this.hostUrl = window["APP_URL"];
    this.agentName = window["USER"].display_name;
    this.agentOnlineStatus = window["USER"].online_status;
    this.organizationId = window["USER"].orgId;
    this.route = window["USER"].route;
    this.accessToken = window["USER"].access_token;
    this.isSuperAdmin = window["USER"].is_super_admin;
    this.getPermissions();
  }

  ngOnInit() {}

  get getTaggedIds(): any[] {
    return this.taggedIds;
  }

  get(restApi: string) {
    return this.http.get(`${this.hostUrl}/api${restApi}`, httpOptions);
  }

  post(restApi: string, payload: any) {
    return this.http.post(
      `${this.hostUrl}/api${restApi}`,
      payload,
      httpOptions
    );
  }

  put(restApi: string, payload: any) {
    return this.http.put(`${this.hostUrl}/api${restApi}`, payload, httpOptions);
  }

  delete(restApi: string) {
    return this.http.delete(`${this.hostUrl}/api${restApi}`, httpOptions);
  }

  public getClients(supervisedAgentId?: number) {
    let agentId = supervisedAgentId ? supervisedAgentId : this.agentId;
    return this.get(`/v1/agents/${agentId}/channel`);
  }

  getChats(client: Client) {
    // if (client.channelAgentId) {
    return this.get(
      `/v1/messages/channels/${client.channelId}/agents/${client.channelAgentId}`
    );
    // }
    // else {
    // return this.get(`/v1/message/${client.channelId}/${client.agentId}`);
  }

  getClientHistoryChats(client: Client, page: number) {
    return this.get(
      `/v1/messages/history/channels/${client.channelId}/agents/${client.channelAgentId}?page=${page}`
    );
  }

  getInternalComments() {
    return this.get(`/v1/internalComments/agent/${this.agentId}`);
  }

  closeInternalCommentsChat(channelId: number) {
    return this.delete(
      `/v1/internalComments/close/channels/${channelId}/agents/${this.agentId}`
    );
  }

  clickedChat(client: Client) {
    this.chatSubject.next(client);
  }
  clickedFile(file: Object) {
    this.viewFileSubject.next(file);
  }
  sendChat(message: string, client: Client, page?: string) {
    let messageType = "public";
    let recipient = "VISITOR";
    // if(recipientInput){
    //   recipient = recipientInput;
    //   messageType = "internal"
    // }
    // when the person asked for help is replying
    if (client.channelType === "internal_comment" || page === "supervise") {
      messageType = "internal";
      recipient = "AGENT";
      return this.post(`/v1/messages`, {
        chat_channel_id: client.channelId,
        message: { text: message },
        recipient: recipient,
        channel_name: client.channelName,
        message_type: messageType,
        internal_agent_id: this.agentId,
        // "internal_agent_name": this.agentName,
        sender_display_name: this.agentName,
        agent_id: client.channelAgentId,
        source_type: client.sourceType ? client.sourceType : null
      });
    }

    return this.post(`/v1/messages`, {
      chat_channel_id: client.channelId,
      message: { text: message },
      recipient: recipient,
      channel_name: client.channelName,
      message_type: messageType,
      sender_display_name: this.agentName
    });
  }

  sendInternalChat(
    receipientAgentId: number,
    client: Client,
    message: string,
    messageType: string
  ) {
    return this.post(`/v1/messages`, {
      agent_id: receipientAgentId,
      sender_display_name: this.agentName,
      channel_agent_id: this.agentId,
      group_id: client.groupId,
      chat_channel_id: client.channelId,
      channel_name: client.channelName,
      client_id: client.clientId,
      client_display_name: client.clientDisplayName,
      message: { text: message },
      recipient: "AGENT",
      message_type: messageType,
      source_type: client.sourceType ? client.sourceType : null
    });
  }

  getPermissions() {
    this.get(`/v1/agents/${this.agentId}/permissions`).subscribe(
      (response: any) => {
        if (response.status) {
          let userPermission = new Permissions();
          userPermission.cannedResponse = response.data["canned-response"];
          userPermission.chatTransfer = response.data["chat-transfer"];
          userPermission.internalComments = response.data["internal-comments"];
          userPermission.email = response.data["email"];
          userPermission.chatTags = response.data["chat-tags"];
          userPermission.chatDownload = response.data["chat_download"];
          userPermission.customerInformation =
            response.data["customer_information"];
          userPermission.settings = response.data["settings"];
          if (response.data["send-attachments"]) {
            userPermission.sendAttachments = response.data["send-attachments"];
          }
          if (
            response.data["settings"] &&
            response.data["settings"]["send-attachments"]
          ) {
            userPermission.chatAttachmentSize =
              response.data["settings"]["send-attachments"]["size"];
          }
          if (
            response.data["settings"] &&
            response.data["settings"]["tag_required"]
          ) {
            userPermission.chatAttachmentSize =
              response.data["settings"]["send-attachments"]["tag_required"];
          }
          if (
            response.data["settings"] &&
            response.data["settings"]["notification_settings"]
          ) {
            userPermission.audioToNotify =
              response.data["settings"]["notification_settings"][
                "notificationEvents"
              ];
          }
          if (
            response.data["settings"] &&
            response.data["settings"]["tag_settings"]
          ) {
            userPermission.tagSettings =
              response.data["settings"]["tag_settings"];
          }
          userPermission.banUser = response.data["ban-user"];
          userPermission.identifierMasking =
            response.data["identifier_masking"];
          userPermission.chatHistory = response.data["chat-history"];
          userPermission.tmsKey = response.data["tms"];
          userPermission.lmsKey = response.data["lms"];
          userPermission.lqsKey = response.data["lqs"];
          userPermission.audioNotification =
            response.data["audio_notification"];
          this.userPermissions = userPermission;
        }
      }
    );
  }

  getTags(channelId: number) {
    return this.get(`/v1/tags/agents/${this.agentId}/chat/${channelId}`);
  }

  addTag(tagName: string, channelID: number) {
    return this.post(`/v1/tags/add`, {
      userId: this.agentId,
      name: tagName,
      channelId: channelID,
      organizationId: this.organizationId
    });
  }

  linkTag(tagId: number, channelId: number) {
    return this.post(`/v1/tags/link`, { tagId: tagId, channelId: channelId });
  }

  unlinkTag(tagId: number, channelId: number) {
    return this.delete(`/v1/tags/${tagId}/unlink/chat/${channelId}`);
  }

  deleteTag(tagId: number, channelId: number) {
    return this.delete(`/v1/tags/${tagId}`);
  }

  agentStatusOnline() {
    return this.put(`/v1/agents/${this.agentId}/online`, "");
  }

  agentStatusOffline(consentInput: number) {
    return this.put(`/v1/agents/${this.agentId}/offline/${consentInput}`, "");
  }

  chatClose(channelId) {
    return this.post(`/v1/agents/${this.agentId}/chat/${channelId}/close`, "");
  }

  chatPick(channelId) {
    return this.post(`/v1/agents/${this.agentId}/chat/${channelId}/pick`, "");
  }

  getExternalGroups() {
    return this.get(
      `/v1/groups/organizations/${this.organizationId}?status=online&userid=${this.agentId}`
    );
  }

  getInternalAgents(groupId: number) {
    return this.get(`/v1/groups/${groupId}/agents?status=online`);
  }

  getCannedResponses() {
    return this.get(`/v1/cannedResponses?userId=${this.agentId}`);
  }

  internalChatTransfer(channelId: number, agentId: number, comment: string) {
    // agent to be transfered id and channel of the chat to be transfered
    return this.post(
      `/v1/chats/transfer/channels/${channelId}/agents/${agentId}`,
      {
        comment: comment
      }
    );
  }
  internalGroupTransfer(channelId: number, groupId: number, comment: string) {
    // group to be transfered id and channel of the chat to be transfered
    return this.post(
      `/v1/chats/transfer/channels/${channelId}/groups/${groupId}`,
      {
        comment: comment
      }
    );
  }

  sendEmail(payload: any, files: any) {
    // console.log(JSON.stringify(payload));
    let form = new FormData();
    form.append("request", JSON.stringify(payload));
    let len = files.length;
    for (let i = 0; i < files.length; i++) {
      form.append("file[]", files[i]);
    }
    return this.post(`/v1/emails/send`, form);
  }

  sendChatAttachments(file: File, client: Client) {
    let form = new FormData();
    form.append("chat_channel_id", client.channelId.toString());
    form.append("recipient", "VISITOR");
    form.append("channel_name", client.channelName);
    form.append("message_type", "public");
    form.append("sender_display_name", this.agentName);
    form.append("file", file);

    return this.post(`/v1/messages/attachments`, form);
  }

  sendAttachment(file, client: Client) {
    let form = new FormData();
    form.append("chat_channel_id", client.channelId.toString());
    form.append("recipient", "VISITOR");
    form.append("channel_name", client.channelName);
    form.append("message_type", "public");
    form.append("sender_display_name", this.agentName);
    form.append("file", file);
    const req = new HttpRequest("POST", "api/v1/messages/attachments", form, {
      headers: httpOptions.headers,
      reportProgress: true,
      responseType: "json",
      withCredentials: true
    });
    return this.http.request(req);
  }

  downloadAttachment(hash: string) {
    let url = `${this.hostUrl}/api/v1/messages/attachments/download/${hash}?api_token=${window["USER"].api_token}`;
    this.http.get(url, { responseType: "blob" }).subscribe((response: any) => {
      if (!response.status) {
        return false;
      }
    });
    return `${this.hostUrl}/api/v1/messages/attachments/download/${hash}?api_token=${window["USER"].api_token}`;
  }

  getHashUrl(hash) {
    return `${this.hostUrl}/api/v1/messages/attachments/download/${hash}?api_token=${window["USER"].api_token}`;
  }

  downloadViewAttachment(hash: string) {
    let url = `${this.hostUrl}/api/v1/messages/attachments/download/${hash}?api_token=${window["USER"].api_token}`;
    return this.http.get(url, { responseType: "blob" });
  }

  banUser(channelId: number) {
    return this.post(`/v1/bannedClients/${channelId}`, {});
  }
  getBlobTypeContent(url: string) {
    return this.http.get(url, { responseType: "blob" });
  }

  convertChats(response: any) {
    let chats: Chat[] = [];
    // console.log(response);
    response.data.forEach(innerData => {
      let chat = new Chat();
      chat.agentDisplayName = innerData.agent_display_name;
      chat.chatDate = moment(innerData.created_at.date).format("MMM DD, YYYY");
      chat.chatTime = moment(innerData.created_at.date).format("hh:mm a");
      chat.botChat = innerData.message.botChat;
      chat.filePath = innerData.message.path;
      chat.message = new Message();
      chat.sourceType = innerData.source_type;
      if (innerData.message.text) {
        chat.message.text = innerData.message.text;
        chat.message.type = "text";
        if (innerData.message.comments && innerData.message.transferred_by) {
          chat.message.comment = innerData.message.comments;
          chat.message.transferredBy = innerData.message.transferred_by;
        }
      } else if (innerData.message.location) {
        chat.message.text = innerData.message.text;
        chat.message.type = "location";
        chat.message.location = {};
        if (innerData.message.location && innerData.message.location.latitude) {
          chat.message.location.latitude = innerData.message.location.latitude;
        }
        if (
          innerData.message.location &&
          innerData.message.location.longitude
        ) {
          chat.message.location.longitude =
            innerData.message.location.longitude;
        }
        if (innerData.message.location && innerData.message.location.name) {
          chat.message.location.name = innerData.message.location.name;
        }
        if (innerData.message.location && innerData.message.location.address) {
          chat.message.location.address = innerData.message.location.address;
        }
      } else if (innerData.message.file_name) {
        chat.message.type = "file";
        chat.message.name = innerData.message.file_name
          .substr(0, innerData.message.file_name.lastIndexOf("."))
          .substr(0, 25);
        chat.message.extension = "." + innerData.message.extension;
        // chat.message.size = innerData.message.size.toFixed(2) + 'mb';
        let size = innerData.message.size;
        if (size > 1) chat.message.size = `${size.toFixed(2)}mb`;
        else chat.message.size = `${(size * 1000).toFixed(2)}kb`;
        chat.message.filehash = innerData.message.hash_name;
      } else {
        chat.message.text = "";
        chat.message.type = "text";
      }
      // chat.message.text = innerData.message.text;
      chat.messageType = innerData.message_type;
      chat.recipient = innerData.recipient;
      chats.push(chat);
    });
    return chats;
  }

  getLanguage() {
    return this.get(`/v1/chats/language`);
  }
  updateLanguage(item: any) {
    this.dataLanguage.next(item);
  }

  getChatQueueCount() {
    return this.get(`/v1/chats/queue_count/${this.agentId}`);
  }

  getUnmaskedClient(clientId) {
    return this.get(`/v1/chats/client/${clientId}`);
  }

  getSuperviseClientInfo(clientId) {
    console.log("called");
    return this.get(`/v1/supervisors/client/${clientId}`);
  }

  getClientInfo(clientId, channelId) {
    return this.get(`/v1/chats/clients/${clientId}?channel_id=${channelId}`);
  }
}
