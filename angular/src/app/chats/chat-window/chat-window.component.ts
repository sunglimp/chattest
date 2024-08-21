import {
  Component,
  OnInit,
  ViewChild,
  Renderer2,
  ChangeDetectorRef,
  NgZone,
  ElementRef,
  AfterContentChecked,
  ViewEncapsulation,
  EventEmitter,
  Output,
  HostListener,
  Input
} from "@angular/core";
import { ChatService } from "src/app/shared/services/chat.service";
import { WebsocketService } from "src/app/shared/services/websocket.service";
import { PerfectScrollbarComponent } from "ngx-perfect-scrollbar";
import { trigger, transition, style, animate } from "@angular/animations";
import {
  Chat,
  Message,
  Client,
  Permissions
} from "src/app/shared/models/client.model";
import * as moment from "moment";
import { HttpEventType, HttpResponse } from "@angular/common/http";
import { Subscription } from "rxjs";

@Component({
  selector: "app-chat-window",
  templateUrl: "./chat-window.component.html",
  styleUrls: ["./chat-window.component.scss"],
  animations: [
    trigger("enterAnimation", [
      transition(":enter", [
        style({ transform: "translateX(100%)", opacity: 0 }),
        animate("300ms", style({ transform: "translateX(0)", opacity: 1 }))
      ]),
      transition(":leave", [
        style({ transform: "translateX(0)", opacity: 1 }),
        animate("300ms", style({ transform: "translateX(100%)", opacity: 0 }))
      ])
    ])
  ]
})
export class ChatWindowComponent implements OnInit {
  // @HostListener("window:beforeunload", ["$event"]) unloadHandler(event: Event) {
  //   console.log("Processing beforeunload...");
  //   this.sendChat({"message":'rouewqhjehkjwhewqhewqkjd'});
  //   // event.returnValue = false;
  // }

  gettingLanguage: object;
  chats: Chat[] = [];
  client: Client;
  agent_name: string;
  colorCodes: any = this.chatService.colorCodes;
  featureBool: boolean = false;
  chatTransferBool: boolean = false;
  featureShown: string = "";
  negetiveInfinity: number = -1000000;
  chatClosed: boolean = false;
  showEmojis: boolean = false;
  showCannedList: boolean = false;
  showInternalAgents: boolean = false;
  isInternalComment: boolean = false;
  selectedInternalAgent: any = "-10000";
  userPermissions: Permissions;
  showEmail: boolean = false;
  showTicket: boolean = false;
  showBanPopup: boolean = false;
  chatsLoading: boolean = false;
  sendAttachment: Subscription;
  showNotifier: boolean = false;
  notifierText: string = "Attachment size can't be more than 15mb!";
  uploadDetails: any = {};
  showPreviousButton: boolean = true;
  nextHistoryPage: number = 1;
  showViewFilePopup: boolean = false;
  appIdKey: any;
  showTicketLQS: boolean;
  fileFormat: any;
  // uploadPercentage: number = 0;
  @Output() hasRepliedEmitter: EventEmitter<boolean> = new EventEmitter();
  @ViewChild("chatMessage") chatMessage: ElementRef;
  @ViewChild("scroll") scroll: PerfectScrollbarComponent;

  constructor(
    public chatService: ChatService,
    private wsService: WebsocketService,
    private ngZone: NgZone
  ) {
    this.agent_name = this.chatService.agentName;
  }

  ngOnInit() {
    this.getLanguage();
    this.getPermissions();
    // if any new chats are coming
    this.wsService.incomingChatObservable.subscribe((chat: any) => {
      // console.log(chat);
      if (chat.isCurrent) {
        this.ngZone.run(() => {
          this.chats.push(chat);
        });
        this.scrollChat();
      }
    });

    this.uploadDetails.isCompleted = true;

    // observable sent by chat list component with all the info
    this.chatService.chatObservable.subscribe((client: any) => {
      this.chatsLoading = true;
      this.client = client;
      this.showPreviousButton = true; // reseting the history button
      this.nextHistoryPage = 1; // reseting the history button params
      if (this.client.isClosed) {
        this.chatClosed = this.client.isClosed;
        return false;
      } else {
        this.chatClosed = false;
      }

      if (this.client.channelId) {
        if (this.client.hasLeft) {
          this.chatService.getChats(this.client).subscribe((response: any) => {
            if (response.status) {
              this.chats = [];
              this.chats = this.chatService.convertChats(response);
              this.scrollChat();
            }
            this.chatsLoading = false;
          });
        } else {
          this.chatService
            .chatPick(this.client.channelId)
            .subscribe((response: any) => {
              if (response.status) {
                this.chatService
                  .getChats(this.client)
                  .subscribe((response: any) => {
                    if (response.status) {
                      this.chats = [];
                      this.chats = this.chatService.convertChats(response);
                      this.scrollChat();
                    }
                    this.chatsLoading = false;
                  });
              }
            });
        }
      }
    });
  }
  getLanguage() {
    this.chatService.getLanguage().subscribe(res => {
      this.gettingLanguage = res;
      this.fileFormat =
        res["data"]["interpretation"]["chat"]["validation_messages"];
      this.chatService.updateLanguage(this.gettingLanguage);
      this.chatService.currentLanguage = res["data"]["language"];
    });
  }
  getPermissions() {
    this.chatService.userObservable.subscribe((permission: Permissions) => {
      if (Object.keys(permission).length > 1) {
        this.ngZone.run(() => {
          this.userPermissions = permission;
        });
      }
    });
  }

  showNotification(text) {
    this.notifierText = text;
    this.showNotifier = true;
    setTimeout(() => {
      this.showNotifier = false;
    }, 2500);
  }

  sendChat(output: any) {
    let message = output.message;
    if (!window.navigator.onLine) {
      this.notifierText = "You are offline!";
      this.showNotifier = true;
      setTimeout(() => {
        this.showNotifier = false;
      }, 2500);
      this.uploadDetails.isCompleted = true;
      return false;
    }
    this.hasRepliedEmitter.emit(true);
    message = message.replace(/</g, "&#60;");
    message = message.replace(/>/g, "&#62;");
    this.isInternalComment = output.isInternalComment;
    let chat = new Chat();
    chat.agentDisplayName = this.agent_name;
    chat.message = new Message();
    chat.chatTime = moment().format("hh:mm a");
    chat.chatDate = moment().format("MMM DD, YYYY");
    chat.message.type = "text";
    if (output.file) {
      let filesize = output.file.size;
      let allowedSize =
        this.chatService.userPermissions.chatAttachmentSize * 1000 * 1000;
      if (filesize > allowedSize) {
        // 20mb
        //this.notifierText = "File size exceeded!";
        this.notifierText = this.fileFormat["file_size_exceeded"]
          ? this.fileFormat["file_size_exceeded"]
          : "File size exceeded!";
        this.showNotifier = true;
        setTimeout(() => {
          this.showNotifier = false;
        }, 2500);
        return false;
      }

      chat.message.type = "file";
      chat.messageType = "dummy";
      chat.recipient = "VISITOR";
      chat.message.name = output.file.name
        .substr(0, output.file.name.lastIndexOf("."))
        .substr(0, 25);
      chat.message.extension = output.file.name.substr(
        output.file.name.lastIndexOf(".")
      );
      let extension = chat.message.extension.toLowerCase();
      chat.message.filehash = null;
      let allowedFiles = this.chatService.allowedAttachmentFormats;
      if (allowedFiles.indexOf(extension) < 0) {
        // this.notifierText = "This file format is not allowed!";
        this.notifierText = this.fileFormat["file_format_not_allowed"]
          ? this.fileFormat["file_format_not_allowed"]
          : "This file format is not allowed!";
        this.showNotifier = true;
        setTimeout(() => {
          this.showNotifier = false;
        }, 2500);
        return false;
      }
      let size = output.file.size / 1000000;
      if (size > 1) chat.message.size = `${size.toFixed(2)}mb`;
      else chat.message.size = `${(size * 1000).toFixed(2)}kb`;
      // chat.message.size = (output.file.size / 1048576).toFixed(2).toString() + 'mb';

      this.sendAttachment = this.chatService
        .sendAttachment(output.file, this.client)
        .subscribe(
          (event: any) => {
            if (event.body && event.body.status) {
              chat.message.filehash = event.body.data.hash_name;
              if (
                this.client.channelId ===
                parseInt(event.body.data.chat_channel_id)
              ) {
                this.chats.push(chat);
                this.uploadDetails.isCompleted = true;
              }
            }
            if (event.type === HttpEventType.UploadProgress) {
              const percentDone = Math.round(
                (100 * event.loaded) / event.total
              );
              console.log(`File is ${percentDone}% uploaded.`);
              this.uploadDetails.uploadPercentage = percentDone;
              if (chat.message.name.length === 25) {
                this.uploadDetails.fileName =
                  chat.message.name + "..." + chat.message.extension;
              } else {
                this.uploadDetails.fileName =
                  chat.message.name + chat.message.extension;
              }
              this.uploadDetails.isCompleted = false;
            } else if (event instanceof HttpResponse) {
              // console.log('File is completely uploaded!');
            }
          },
          error => {
            this.notifierText = error.error.errors[0];
            this.showNotifier = true;
            setTimeout(() => {
              this.showNotifier = false;
            }, 2500);
            this.uploadDetails.isCompleted = true;
            return false;
          }
        );
      return false;
    }
    if (this.isInternalComment) {
      this.selectedInternalAgent = output.selectedInternalAgent;
      chat.message.text = this.selectedInternalAgent.name + message;
      chat.recipient = "dummy";
      chat.messageType = "internal";
      this.chats.push(chat);
      this.chatService
        .sendInternalChat(
          this.selectedInternalAgent.id,
          this.client,
          chat.message.text,
          "internal"
        )
        .subscribe(data => {
          // console.log(data);
        });
    } else {
      chat.message.text = message;
      if (this.client.channelType === "internal_comment") {
        chat.messageType = "internal";
        chat.recipient = "AGENT";
      } else {
        chat.messageType = "dummy";
        chat.recipient = "VISITOR";
      }
      this.chats.push(chat);
      this.chatService.sendChat(message, this.client).subscribe(data => {});
    }
  }

  showFeatures(event, feature: string) {
    event.stopPropagation();
    this.featureBool = true;
    this.featureShown = feature;
  }

  scrollChat() {
    if (!this.scroll) {
      setTimeout(() => {
        if (this.scroll)
          this.scroll.directiveRef.scrollToBottom(this.negetiveInfinity, 1);
      }, 0);
    } else {
      this.scroll.directiveRef.scrollToBottom(this.negetiveInfinity, 1);
    }
  }

  emailOutput(output: any) {
    if (output === "false") this.showEmail = false;
  }

  ticketOutput(output: any) {
    if (output.close) {
      this.showTicket = false;
      this.showTicketLQS = false;
    }
  }

  openPopup(output: any) {
    switch (output.type) {
      case "email": {
        this.showEmail = output;
        break;
      }
      case "banUser": {
        this.showBanPopup = true;
        break;
      }
      case "history": {
        this.chatService
          .getClientHistoryChats(this.client, this.nextHistoryPage)
          .subscribe((response: any) => {
            console.log(response);
            if (response.links.next) {
              this.nextHistoryPage = parseInt(
                this.getQueryParams("page", response.links.next)
              );
            } else {
              this.showPreviousButton = false;
            }
            this.ngZone.run(() => {
              this.chats.unshift(
                ...this.chatService.convertChats(response).reverse()
              );
            });
          });
        break;
      }
      case "ticket": {
        this.showTicket = true;
        this.appIdKey = this.userPermissions
          ? this.userPermissions["tmsKey"]
          : "";
        this.appIdKey = this.appIdKey == true ? 3 : "";
        this.chatService.ticketIsMinimized = false;
        break;
      }
      case "viewFile": {
        this.showViewFilePopup = true;
        break;
      }
      case "ticketLQS": {
        console.log("called lqs");
        this.showTicketLQS = true;
        this.chatService.lqsIsMinimized = false;
        break;
      }
      default:
        break;
    }
  }

  getQueryParams(params, url) {
    let href = url;
    //this expression is to get the query strings
    let reg = new RegExp("[?&]" + params + "=([^&#]*)", "i");
    let queryString = reg.exec(href);
    return queryString ? queryString[1] : null;
  }

  warningBanOutput(output: any) {
    // console.log(output);
    if (output) {
      this.chatService
        .banUser(this.client.channelId)
        .subscribe((response: any) => {
          if (response.status) {
            this.client.isClosed = true;
            this.chatService.clickedChat(this.client);
          }
        });
    }
    this.showBanPopup = false;
  }

  cancelAttachment(output: any) {
    if (output) {
      this.sendAttachment.unsubscribe();
    }
  }
  closedPopup(output: any) {
    this.showViewFilePopup = false;
  }
}
