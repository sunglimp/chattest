import { Component, OnInit, NgZone } from "@angular/core";
import { SuperviseService } from "src/app/shared/services/supervise.service";
import { Client, Chat } from "src/app/shared/models/client.model";
import { WebsocketService } from "src/app/shared/services/websocket.service";
import { ChatService } from "src/app/shared/services/chat.service";

@Component({
  selector: "app-supervise-list",
  templateUrl: "./supervise-list.component.html",
  styleUrls: [
    "./../../chats/chat-list/chat-list.component.scss",
    "./supervise-list.component.scss"
  ]
})
export class SuperviseListComponent implements OnInit {
  gettingLanguage: Object;
  gettingLanguageSupervise: object;

  showClients: boolean = false;
  clients: Client[] = [];
  shownClients: Client[] = this.clients;
  colorCodes: string[] = this.chatService.colorCodes;
  clickedChat: number = -1;
  isLoading: boolean = true;
  activeAgents: any[] = [];
  activeAgentsSet = new Set();
  searchKey: string;

  constructor(
    private superviseService: SuperviseService,
    private wsService: WebsocketService,
    public chatService: ChatService,
    private ngZone: NgZone
  ) {}

  ngOnInit() {
    this.getLanguage();
    this.getSupervisedChannels();
    this.appendNewChats();
    this.getSupervisedAgents();
    this.appendNewMessages();
    this.checkNewClientOnline();
  }

  getSupervisedChannels() {
    this.superviseService.getSupervisedChannels().subscribe((clients: any) => {
      clients.data.map(data => {
        if (data.event !== "new_internal_comment") {
          let client = this.wsService.convertClient(data);
          // client.channelName = data.channel_name;
          // client.clientDisplayName = data.client_display_name;
          // client.clientId = data.client_id;
          // client.channelId = data.id;
          // client.undreadMessage = data.recent_message.text;
          // client.unreadMessagesCount = data.unread_count;
          // client.clientInfo = data.client_raw_info;
          // client.groupId = data.group_id;
          // client.channelType = data.channel_type;
          // client.channelAgentId = data.channel_agent_id;
          // client.channelAgentName = data.agent_name;
          // client.channelAgentRole = data.role;
          client.sourceType = data.source_type;
          this.appendChatsInAgents(client);
          this.clients.push(client);
          this.wsService.listenChannel(client.channelName);
        }
      });
      this.isLoading = false;
      if (this.clients.length > 0) this.onSelect(0);
    });
  }

  appendChatsInAgents(client: Client) {
    if (!this.activeAgentsSet.has(client.channelAgentId)) {
      this.activeAgentsSet.add(client.channelAgentId);
      this.activeAgents.push({
        id: client.channelAgentId,
        name: client.channelAgentName,
        role: client.channelAgentRole,
        showClients: true,
        clients: 1
      });
    } else {
      let index = this.activeAgents.findIndex(
        agent => agent.id === client.channelAgentId
      );
      this.activeAgents[index].clients++;
    }
  }
  appendNewChats() {
    this.wsService.incomingClientObservable.subscribe((client: Client) => {
      if (client.channelId && client.channelType !== "internal_comment") {
        this.ngZone.run(() => {
          this.appendChatsInAgents(client);

          if (this.chatService.userPermissions.customerInformation) {
            this.chatService
              .getSuperviseClientInfo(client.clientId)
              .subscribe(res => {
                if (res["data"] && res["data"].length > 0) {
                  client.clientDisplayNumber = res["data"][0].identifier;

                  this.wsService.seperateClientUsernameAndNumber(
                    client,
                    client.clientDisplayNumber
                  );
                }
              });
          } else if (!this.chatService.userPermissions.identifierMasking) {
            // Idemtifier masking false
            this.chatService
              .getUnmaskedClient(client.clientId)
              .subscribe(res => {
                if (res["data"] && res["data"].length > 0) {
                  client.clientDisplayNumber = res["data"][0].identifier;
                  client.clientDisplayName = "";
                }
              });
          } else {
            this.wsService.seperateClientUsernameAndNumber(
              client,
              client.clientDisplayNumber,
              true
            );

            client.clientDisplayNumber = this.maskeClientNumber(
              client.clientDisplayNumber
            );

            if (client.clientDisplayName) {
              client.clientDisplayName = this.maskeClientNumber(
                client.clientDisplayName
              );
            }
          }
          this.clients.push(client);
        });
      }
    });
  }

  maskeClientNumber(num) {
    let maskedNumber = "";
    if (num.length > 0) {
      maskedNumber = Array(num.length - 1).join("*");
      maskedNumber = this.replaceCharacterInString(maskedNumber, 0, num[0]);
      maskedNumber =
        num.length > 1
          ? this.replaceCharacterInString(maskedNumber, 1, num[1])
          : "*";
      maskedNumber =
        num.length > 2
          ? this.replaceCharacterInString(
              maskedNumber,
              num.length - 2,
              num[num.length - 2]
            )
          : "*";
      maskedNumber =
        num.length > 2
          ? this.replaceCharacterInString(
              maskedNumber,
              num.length - 1,
              num[num.length - 1]
            )
          : "*";
    }
    return maskedNumber;
  }

  replaceCharacterInString(strng, index, character) {
    return strng.substring(0, index) + character + strng.substring(index + 1);
  }

  checkNewClientOnline() {
    this.wsService.agentOnlineObservable.subscribe((info: any) => {
      // console.log(info);
      if (info.type) {
        if (info.type === "online") {
          this.ngZone.run(() => {
            info.clients.forEach(client => {
              this.clients.push(client);
              this.wsService.listenChannel(client.channelName);
            });
          });
        } else if (info.type === "remove" || info.type === "autoTransfer") {
          this.removeClient(info);
        } else if (info.type === "hasLeft") {
          let client = this.clients.filter(
            (client: Client) => client.channelId === info.channelId
          );
          this.ngZone.run(() => {
            client[0].hasLeft = true;
          });
        }
      }
    });
  }

  removeClient(info: any) {
    let removedClient = this.clients.filter(
      (client: Client) => client.channelId === info.channelId
    );
    let removedChannelId = removedClient[0].channelId;
    let removedClientIndex = this.shownClients.indexOf(removedClient[0]);
    this.ngZone.run(() => {
      if (removedChannelId === this.clickedChat) {
        let client = new Client();
        client.isClosed = true;
        this.chatService.chatSubject.next(client);
        this.clickedChat = -1;
      }
      this.shownClients.splice(removedClientIndex, 1);
      let index = this.activeAgents.findIndex(
        agent => agent.id == info.agentId
      );
      this.activeAgents[index].clients--;
      // console.log(removedClient)
    });
  }

  appendNewMessages() {
    this.wsService.incomingChatObservable.subscribe((chat: Chat) => {
      if (chat.message && !chat.isCurrent) {
        let targetClient = this.clients.filter(client => {
          return client.channelName === chat.channelName;
        });
        this.ngZone.run(() => {
          targetClient[0].undreadMessage = chat.message.text;
          targetClient[0].unreadMessagesCount++;
        });
      }
    });
  }

  getSupervisedAgents() {
    this.superviseService.getSupervisedAgents().subscribe((response: any) => {
      response.data.map(agent => {
        this.wsService.informAgentPrivateChannel(agent.id);
      });
    });
  }

  onSelect(index: number) {
    this.clickedChat = this.shownClients[index].channelId;
    this.shownClients[index].unreadMessagesCount = 0;
    this.shownClients[index].colorCode = this.colorCodes[
      this.shownClients[index].clientId % 26
    ];
    this.chatService.clickedChat(this.shownClients[index]);
  }

  searchAgents() {
    if (this.searchKey.length > 0) {
      this.shownClients = this.clients.filter(client => {
        return (
          client.clientDisplayName
            .toLowerCase()
            .indexOf(this.searchKey.toLowerCase()) > -1
        );
      });
    } else {
      this.shownClients = this.clients;
    }
  }

  getLanguage() {
    this.chatService.getLanguage().subscribe(res => {
      this.gettingLanguage = res["data"];
      this.gettingLanguageSupervise = res["data"];
      this.chatService.updateLanguage(res);
      this.chatService.currentLanguage = res["data"]["language"];
    });
  }
}
