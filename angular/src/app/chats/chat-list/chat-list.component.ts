import {
  Component,
  OnInit,
  OnDestroy,
  NgZone,
  HostListener,
  ViewChild,
  ElementRef
} from "@angular/core";
import { ChatService } from "src/app/shared/services/chat.service";
import { WebsocketService } from "src/app/shared/services/websocket.service";
import { Client, Chat, Permissions } from "src/app/shared/models/client.model";
import { PerfectScrollbarComponent } from "ngx-perfect-scrollbar";
import { TicketService } from "../../shared/services/ticket.service";

@Component({
  selector: "app-chat-list",
  templateUrl: "./chat-list.component.html",
  styleUrls: ["./chat-list.component.scss"]
})
export class ChatListComponent implements OnInit, OnDestroy {
  @HostListener("window:beforeunload", ["$event"])
  unloadHandler(event: Event) {
    // console.log("Processing beforeunload...");
    // if the selected client has left, dont open any other chat on page refresh
    if (this.clients[this.clickedChat].hasLeft) {
      window.localStorage.setItem("lastOpenedChat", "-1");
    } else {
      window.localStorage.setItem(
        "lastOpenedChat",
        this.clickedChat.toString()
      );
    }
    // event.returnValue = false;
  }
  gettingLanguage: object;
  agentStatus: string = "offline";
  clients: Client[] = [];
  route: string = this.chatService.route;
  channelIds = new Set();
  clickedChat: number = -1;
  colorCodes: string[] = this.chatService.colorCodes;
  selectedChats: number = -1;
  isLoading: boolean = true;
  isListLoading: boolean = false;
  closedFromList: boolean = false;
  showWarningOfflinePopup: boolean = false;
  showWarningClosePopup: boolean = false;
  warningMessage: string = "";
  @ViewChild("scroll") scroll: PerfectScrollbarComponent;
  @ViewChild("chatWindow")
  private chatWindow: any;
  chatHeightBool: any = {
    active: false,
    awaiting: false
  };
  lastOpenedChat: number = -1;
  activeCount: number = 0;
  awaitingCount: number = 0;

  closedChatData: any = {
    channelId: "",
    index: -1
  };

  constructor(
    public chatService: ChatService,
    private _ticketService: TicketService,
    private wsService: WebsocketService,
    private ngZone: NgZone
  ) {}

  ngOnInit() {
    this.getLanguage();
    this.agentStatus = this.chatService.agentOnlineStatus
      ? "online"
      : "offline";
    this.wsService.informAgentPrivateChannel();
    this.wsService.informQueueCountPrivateChannel();
    this.getInternalComments();
    this.getClients();
    this.getChatQueueCount();

    //check if any new clients have been arrived.
    this.wsService.incomingClientObservable.subscribe((client: Client) => {
      if (client.channelId) {
        this.ngZone.run(() => {
          if (!this.channelIds.has(client.channelId)) {
            this.channelIds.add(client.channelId);
            this.clients.push(client);
            if (client.status === "1") {
              this.awaitingCount++;
            } else this.activeCount++;
          }
        });
      }
    });
    // remove the  chat if it is transfered
    this.chatService.chatObservable.subscribe((client: Client) => {
      if (client.isClosed && !this.closedFromList) {
        this.isListLoading = true;
        this.chatRemove(this.clickedChat);
      }
      this.closedFromList = false;
    });

    // get chat queue count
    this.wsService.chatQueueObservable.subscribe(res => {
      if (res["type"] === "queueCount") {
        this.ngZone.run(() => {
          this.chatService.chatQueueCount = res["queueCount"];
        });
      }
    });

    // if incoming chat to be shown in list
    this.wsService.incomingChatObservable.subscribe((chat: Chat) => {
      console.log(chat);
      if (chat.message && !chat.isCurrent) {
        // console.log(chat.channelName);
        // find which chat to be appended
        let targetClient = this.clients.filter(client => {
          return client.channelName === chat.channelName;
        });
        this.ngZone.run(() => {
          targetClient[0].undreadMessage = chat.message.text;
          targetClient[0].unreadMessagesCount++;
        });
      }
    });

    // info from private channel
    this.wsService.agentOnlineObservable.subscribe((response: any) => {
      if (response.type === "notify") {
        let client = this.clients.filter(
          (client: Client) => client.channelId === response.channelId
        );
        client.forEach(cl => {
          this.ngZone.run(() => {
            if (!cl.hasLeft) cl.isImportant = true;
          });
        });
      } else if (
        response.type === "autoTransfer" ||
        response.type === "internalRemove"
      ) {
        let client = this.clients.filter(
          (client: Client) => client.channelId === response.channelId
        );
        this.ngZone.run(() => {
          if (client[0]) {
            if (this.clickedChat === this.clients.indexOf(client[0])) {
              let client = new Client();
              client.isClosed = true;
              this.closedFromList = false;
              this.chatService.chatSubject.next(client);
            } else {
              this.chatRemove(this.clients.indexOf(client[0]));
            }
          }
        });
      } else if (response.type === "hasLeft") {
        let client = this.clients.filter(
          (client: Client) => client.channelId === response.channelId
        );
        this.ngZone.run(() => {
          if (client[0]) {
            if (this.clickedChat === this.clients.indexOf(client[0])) {
              window.localStorage.setItem("lastOpenedChat", "-1");
            }
            client[0].hasLeft = true;
            client[0].isImportant = false;
            client[0].isSessionTimeout = undefined;
            if (
              (response.hasOwnProperty("isSessionTimeout") &&
                response.isSessionTimeout === true) ||
              (response.hasOwnProperty("isSessionTimeout") &&
                response.isSessionTimeout === false)
            ) {
              client[0].isSessionTimeout = response.isSessionTimeout;
            }
          }
        });
      }
    });
  }

  getInternalComments() {
    this.isListLoading = true;
    this.chatService.getInternalComments().subscribe((clients: any) => {
      clients.data.map(data => {
        let client = new Client();
        client.channelName = data.channel_name;
        client.clientDisplayName = data.client_display_name;
        client.clientDisplayNumber = data.client_display_name;
        if (
          this.chatService.userPermissions &&
          this.chatService.userPermissions.hasOwnProperty(
            "customerInformation"
          ) &&
          client.clientDisplayNumber.includes("||")
        ) {
          client.clientDisplayNumber =
            data.client_display_name.split("||")[0] || "";
          client.clientDisplayName =
            data.client_display_name.split("||")[1] || "";
        }
        client.clientId = data.client_id;
        client.channelId = data.id;
        console.log(data.recent_message);
        client.undreadMessage = data.recent_message
          ? data.recent_message.text
          : "";
        client.unreadMessagesCount = data.unread_count;
        client.clientInfo = data.client_raw_info;
        client.groupId = data.group_id;
        client.channelType = data.channel_type;
        client.channelAgentId = data.channel_agent_id;
        client.sourceType = data.source_type;
        this.activeCount++;
        this.clients.push(client);
        this.wsService.listenChannel(client.channelName);
      });
    });
    this.isListLoading = false;
  }

  getClients() {
    if (this.chatService.getClients()) {
      this.chatService.getClients().subscribe(
        (clients: any) => {
          clients.data.map(data => {
            let client = new Client();
            client.channelName = data.channel_name;
            client.clientDisplayNumber = data.client_display_name;
            client.clientId = data.client_id;
            client.channelId = data.id;
            client.undreadMessage = data.recent_message
              ? data.recent_message.text
              : "";
            client.unreadMessagesCount = data.unread_count;
            client.clientInfo = data.client_raw_info;
            client.groupId = data.group_id;
            client.channelType = data.channel_type;
            client.channelAgentId = data.channel_agent_id;
            client.hasHistory = data.has_history === 1;
            client.isHigh =
              data.parent_id !== null && data.parent_id !== undefined;
            client.sourceType = data.source_type;
            client.status = data.status; // 1 if awaiting , 2 if active

            this.wsService.seperateClientUsernameAndNumber(
              client,
              client.clientDisplayNumber
            );

            if (!this.channelIds.has(client.channelId)) {
              if (client.status === "1") {
                this.awaitingCount++;
              } else this.activeCount++;

              this.channelIds.add(client.channelId);
              this.clients.push(client);
              this.wsService.listenChannel(client.channelName);
            }
          });
        },
        error => {
          // console.log(error);
        },
        () => {
          this.lastOpenedChat = parseInt(
            window.localStorage.getItem("lastOpenedChat")
          );
          if (this.lastOpenedChat > -1) {
            if (this.clients[this.lastOpenedChat])
              this.onSelect(this.lastOpenedChat);
          }
          this.isLoading = false;
        }
      );
    }
  }

  onSelect(index: number) {
    if (this.clickedChat !== index) {
      this.clickedChat = index;
      this.clients[index].unreadMessagesCount = 0;
      this.clients[index].colorCode = this.colorCodes[
        this.clients[index].clientId % 26
      ];
      this.chatService.clickedChat(this.clients[index]);
      if (this.clients[index].status === "1") {
        this.clients[index].status = "2";
        this.awaitingCount--;
        this.activeCount++;
        this.scroll.directiveRef.scrollToBottom(
          this.chatService.negetiveInfinity,
          1
        );
      }
    }
    this.chatService.ticketIsMinimized = false;
    this.chatService.lqsIsMinimized = false;
    this._ticketService.ticketSubject.next(true);
    this._ticketService.lqsSubject.next(true);
  }

  changeAgentStatus(agentStatus: string) {
    this.isListLoading = true;
    if (agentStatus === "offline") {
      this.chatService.agentStatusOnline().subscribe((response: any) => {
        if (response.status) {
          this.isListLoading = false;
          this.agentStatus = "online";
          window.localStorage.setItem(
            "lastOpenedChat",
            this.clickedChat.toString()
          );
          this.getClients();
        }
      });
    } else if (agentStatus === "online") {
      this.chatService.agentStatusOffline(0).subscribe((response: any) => {
        if (response.status) {
          this.isListLoading = false;
          this.agentStatus = "offline";
        }
      });
    }
    // }
  }

  chatClose(
    event,
    channelId: number,
    index: number,
    tagRequired: boolean,
    tagLength: number
  ) {
    if (tagRequired && !tagLength) {
      this.chatWindow.showNotification(
        this.gettingLanguage["archive"]["validation_messages"][
          "atleast_one_tag"
        ]
      );
    } else {
      event.stopPropagation();
      this.showWarningClosePopup = true;
      this.closedChatData.channelId = channelId;
      this.closedChatData.index = index;
      // this.closedChatData.status = status;
    }
  }

  chatRemove(index?: number) {
    if (index == undefined) {
      index = this.clickedChat;
      this.closedFromList = true;
    }
    this.wsService.stopListening(this.clients[index].channelName);
    this.ngZone.run(() => {
      this.channelIds.delete(this.clients[index].channelId);
      if (this.clients[index].status === "2") {
        this.activeCount--;
      } else this.awaitingCount--;
      this.clients.splice(index, 1);
      if (this.clickedChat === index) {
        this.clickedChat = -1;
      } else if (this.clickedChat > index) this.clickedChat--;
    });
    this.isListLoading = false;
  }

  warningOfflineOutput(event: boolean) {
    this.showWarningOfflinePopup = false;
    if (event) {
      this.closedFromList = true;
      // this.changeAgentStatus(event);
      window.localStorage.setItem("lastOpenedChat", "-1");
    }
  }

  warningCloseOutput(event: boolean) {
    this.showWarningClosePopup = false;
    if (event) {
      if (this.clients[this.closedChatData.index].hasLeft) {
        if (this.clickedChat === this.closedChatData.index) {
          let client = new Client();
          client.isClosed = true;
          this.closedFromList = true;
          this.chatService.chatSubject.next(client);
        }
        this.closedFromList = true;
        this.chatRemove(this.closedChatData.index);
        return false;
      } else if (
        this.clients[this.closedChatData.index].channelType ===
        "internal_comment"
      ) {
        this.isListLoading = true;
        this.chatService
          .closeInternalCommentsChat(this.closedChatData.channelId)
          .subscribe((response: any) => {
            if (response.status) {
              this.isListLoading = false;
              if (this.clickedChat === this.closedChatData.index) {
                let client = new Client();
                client.isClosed = true;
                this.closedFromList = true;
                this.chatService.chatSubject.next(client);
              }
              this.chatRemove(this.closedChatData.index);
            }
          });
      } else {
        this.isListLoading = true;
        this.chatService
          .chatClose(this.closedChatData.channelId)
          .subscribe((response: any) => {
            if (response.status) {
              this.isListLoading = false;
              if (this.clickedChat === this.closedChatData.index) {
                this.clients[this.closedChatData.index].isClosed = true;
                this.closedFromList = true;
                this.chatService.clickedChat(
                  this.clients[this.closedChatData.index]
                );
              }
              this.chatRemove(this.closedChatData.index);
            }
          });
      }
    }
  }

  ngOnDestroy() {
    window.localStorage.setItem("lastOpenedChat", "mohit");
  }
  getLanguage() {
    this.chatService.getLanguage().subscribe(res => {
      this.gettingLanguage = res["data"]["interpretation"];
      this.chatService.updateLanguage(this.gettingLanguage);
      this.chatService.currentLanguage = res["data"]["language"];
    });
  }
  toggleDropdown(feature: string) {
    if (feature === "active") {
      this.chatHeightBool.active = !this.chatHeightBool.active;
    } else {
      this.chatHeightBool.awaiting = !this.chatHeightBool.awaiting;
    }
  }

  hasReplied(event) {
    this.clients[this.clickedChat].isImportant = false;
  }

  getChatQueueCount() {
    this.chatService.getChatQueueCount().subscribe(res => {
      if (res["status"]) {
        this.chatService.chatQueueCount = res["data"].count;
      } else {
        this.chatService.chatQueueCount = null;
      }
    });
  }
}
