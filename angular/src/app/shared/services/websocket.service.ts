import { Injectable } from "@angular/core";
import Echo from "laravel-echo";
import { BehaviorSubject, Subject } from "rxjs";
import { ChatService } from "./chat.service";
import { Client, Chat, Message } from "src/app/shared/models/client.model";
import * as moment from "moment";
import { Howl } from "howler";

declare global {
  interface Window {
    io: any;
  }
  interface Window {
    Echo: any;
  }
}

window.io = window.io || require("socket.io-client");
window.Echo =
  window.Echo ||
  new Echo({
    broadcaster: "socket.io",
    //host: 'http://localhost:6001'
    host: window["APP_URL"] + window["USER"].broadcast_port
  });

@Injectable({
  providedIn: "root"
})
export class WebsocketService {
  private incomingChatSubject = new BehaviorSubject(<Object>[]);
  private incomingClientSubject = new BehaviorSubject(<Object>[]);
  private agentOnlineSubject = new BehaviorSubject(<Object>{});
  chatQueueSubject = new Subject();
  incomingChatObservable = this.incomingChatSubject.asObservable();
  incomingClientObservable = this.incomingClientSubject.asObservable();
  agentOnlineObservable = this.agentOnlineSubject.asObservable();
  chatQueueObservable = this.chatQueueSubject.asObservable();

  selectedChannel: string = "";
  selectedChannelType: string = "";
  ownerDisplayName: string = "";
  route: string = "";
  agentId: number;
  isTicket: boolean = false;
  isSuperadmin: boolean = false;
  audio: any;
  sound = new Howl({
    src: ["./../../../sounds/notify.mp3"]
  });

  constructor(private chatService: ChatService) {
    this.route = this.chatService.route;
    this.isTicket = window["USER"].is_ticket;
    this.isSuperadmin = window["USER"].is_super_admin;

    console.log("this.isSuperadmin", this.isSuperadmin);
    this.agentId = this.chatService.agentId;
    this.chatService.chatObservable.subscribe((chatInfo: any) => {
      if (chatInfo.isClosed) {
        this.selectedChannel = null;
      } else {
        this.selectedChannel = chatInfo.channelName;
        this.selectedChannelType = chatInfo.channelType;
      }
    });
  }

  listenChannel(channelName: string) {
    window.Echo.channel(channelName).listen("MessageArrived", info => {
      if (
        this.selectedChannelType === "internal_comment" ||
        this.route === "supervise" ||
        (info.recipient === "AGENT" && info.message_type !== "internal")
      ) {
        console.log(info);
        let chat = new Chat();
        chat.message = new Message();
        if (info.message.text) {
          chat.message.text = info.message.text;
          chat.message.type = "text";
        } else if (info.message.location) {
          chat.message.text = info.message.text;
          chat.message.type = "location";
          chat.message.location = {};
          if (info.message.location && info.message.location.latitude) {
            chat.message.location.latitude = info.message.location.latitude;
          }
          if (info.message.location && info.message.location.longitude) {
            chat.message.location.longitude = info.message.location.longitude;
          }
          if (info.message.location && info.message.location.name) {
            chat.message.location.name = info.message.location.name;
          }
          if (info.message.location && info.message.location.address) {
            chat.message.location.address = info.message.location.address;
          }
        } else if (info.message.file_name) {
          chat.message.type = "file";
          chat.message.name = info.message.file_name
            .substr(0, info.message.file_name.lastIndexOf("."))
            .substr(0, 25);
          chat.message.extension = "." + info.message.extension;
          let size = info.message.size;
          if (size > 1) chat.message.size = `${size.toFixed(2)}mb`;
          else chat.message.size = `${(size * 1000).toFixed(2)}kb`;
          chat.message.filehash = info.message.hash_name;
        } else {
          chat.message.text = info.message.text;
          chat.message.type = "text";
        }
        chat.channelName = channelName;
        chat.isCurrent = chat.channelName === this.selectedChannel;
        chat.recipient = info.recipient;
        chat.agentDisplayName = info.sender_display_name;
        chat.messageType = info.message_type;
        chat.chatTime = moment().format("hh:mm a");
        chat.chatDate = moment().format("MMM DD, YYYY");
        chat.sourceType = info.source_type;
        console.log(chat);
        this.incomingChatSubject.next(chat);
      }
    });
  }

  informQueueCountPrivateChannel(agentId?: number) {
    let idToListen;
    if (agentId) {
      idToListen = agentId;
    } else idToListen = this.agentId;

    window.Echo.channel(`chat-queue-count-${idToListen}`).listen(
      "InformQueueCountPrivateChannel",
      info => {
        console.log("hurreey got chat-queue-count event : ", info);
        switch (info.event) {
          case "chat_queue_count": {
            const obj = {
              type: "queueCount",
              queueCount: parseInt(info.queue_count),
              agentId: parseInt(info.agent_id)
            };
            this.chatQueueSubject.next(obj);
            break;
          }
        }
      }
    );
  }

  informAgentPrivateChannel(agentId?: number) {
    let idToListen;
    if (agentId) {
      idToListen = agentId;
    } else idToListen = this.agentId;

    window.Echo.channel(`hash-agent-${idToListen}`).listen(
      "InformAgentPrivateChannel",
      info => {
        // if new chat
        setTimeout(() => {
          if (this.chatService.userPermissions) {
            if (this.chatService.userPermissions.audioNotification) {
              if (info.channel_type === "internal_comment") {
                this.playAudio("new_internal_comment");
              } else this.playAudio(info.channel_type);
            }
          }
        }, 100);

        switch (info.event) {
          case "new_chat": {
            let client = this.convertClient(info);
            this.incomingClientSubject.next(client);
            this.listenChannel(client.channelName);
            break;
          }
          case "new_internal_comment": {
            if (info.internal_agent_id !== this.agentId) {
              let chat = new Chat();
              chat.message = new Message();
              chat.message.text = info.message.text;
              chat.channelName = info.channel_name;
              chat.isCurrent = chat.channelName === this.selectedChannel;
              chat.recipient = "AGENT";
              chat.messageType = "internal";
              chat.agentId = info.internal_agent_id;
              chat.agentDisplayName = info.sender_display_name;
              chat.chatTime = moment().format("hh:mm a");
              chat.chatDate = moment().format("MMM DD, YYYY");
              this.ownerDisplayName = chat.agentDisplayName;
              this.incomingChatSubject.next(chat);
            }
            break;
          }
          case "chat_transfer": {
            this.agentOnlineSubject.next({
              type: "autoTransfer",
              channelId: parseInt(info.id)
            });
            break;
          }
          case "bulk_channels_assign": {
            this.chatService
              .getClients(info.agent_id)
              .subscribe((innerInfo: any) => {
                let clients = [];
                innerInfo.data.forEach(clientInfo => {
                  let client = this.convertClient(clientInfo);
                  clients.push(client);
                });
                this.agentOnlineSubject.next({
                  type: "online",
                  clients: clients,
                  agentId: parseInt(info.agent_id)
                });
              });
            break;
          }
          case "agent_offline": {
            this.chatService
              .getClients(info.agent_id)
              .subscribe((innerInfo: any) => {
                let clients = [];
                innerInfo.data.forEach(clientInfo => {
                  let client = this.convertClient(clientInfo);
                  clients.push(client);
                });
                this.agentOnlineSubject.next({
                  type: "offline",
                  clients: clients,
                  agentId: parseInt(info.agent_id)
                });
              });
            break;
          }
          case "chat_removed": {
            this.agentOnlineSubject.next({
              type: "remove",
              channelId: parseInt(info.id),
              agentId: parseInt(info.agent_id)
            });
            break;
          }

          case "new_important_notifier": {
            this.agentOnlineSubject.next({
              type: "notify",
              channelId: parseInt(info.id),
              agentId: parseInt(info.agent_id)
            });
            break;
          }

          case "chat_removed_by_visitor": {
            this.agentOnlineSubject.next({
              type: "hasLeft",
              channelId: parseInt(info.id),
              agentId: parseInt(info.agent_id),
              isSessionTimeout: info.hasOwnProperty("is_session_timeout")
                ? info.is_session_timeout
                : undefined
            });
            break;
          }

          case "internal_comment_remove": {
            this.agentOnlineSubject.next({
              type: "internalRemove",
              channelId: parseInt(info.id),
              agentId: parseInt(info.agent_id)
            });
            break;
          }
        }
      }
    );
  }

  stopListening(channelName: string) {
    window.Echo.leave(channelName);
  }

  seperateClientUsernameAndNumber(client, data, additionalFlag?: boolean): any {
    if (client.sourceType === "whatsapp" && data.includes("||")) {
      client.clientDisplayNumber = data.split("||")[0] || "";
      client.clientDisplayName = data.split("||")[1] || "";
      if (additionalFlag) {
        client.clientDisplayName = "";
      }
    }
  }

  convertClient(clientInfo: any): Client {
    let client = new Client();
    client.channelName = clientInfo.channel_name;
    client.clientDisplayNumber = clientInfo.client_display_name;
    client.clientId = clientInfo.client_id;
    client.channelId = clientInfo.id;
    console.log(clientInfo.recent_message);
    client.undreadMessage = clientInfo.recent_message
      ? clientInfo.recent_message.text
      : "";
    client.unreadMessagesCount = clientInfo.unread_count;
    client.groupId = clientInfo.group_id;
    client.channelType = clientInfo.channel_type;
    if (client.channelType === "internal_comment") client.status = "2";
    else client.status = "1";
    client.agentId = clientInfo.agent_id;
    client.channelAgentId = parseInt(clientInfo.channel_agent_id);
    client.clientInfo = clientInfo.client_raw_info;
    client.hasHistory = clientInfo.has_history;
    client.isHigh =
      clientInfo.parent_id !== null && clientInfo.parent_id !== undefined;
    client.channelAgentName = clientInfo.agent_name;
    client.channelAgentRole = clientInfo.role;
    client.sourceType = clientInfo.source_type;
    debugger;
    this.seperateClientUsernameAndNumber(client, client.clientDisplayNumber);
    return client;
  }

  playAudio(eventToNotify: string) {
    if (
      this.chatService.userPermissions.audioToNotify.indexOf(eventToNotify) > -1
    ) {
      this.sound.play();
    }
  }
}
