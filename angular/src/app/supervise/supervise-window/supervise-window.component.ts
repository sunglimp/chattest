import {Component, OnInit, NgZone, ViewChild, Input} from '@angular/core';
import { ChatService } from 'src/app/shared/services/chat.service';
import { Client, Chat, Message, Permissions } from 'src/app/shared/models/client.model';
import * as moment from 'moment';
import { WebsocketService } from 'src/app/shared/services/websocket.service';
import { PerfectScrollbarComponent } from 'ngx-perfect-scrollbar';

@Component({
  selector: 'app-supervise-window',
  templateUrl: './supervise-window.component.html',
  styleUrls: ['./../../chats/chat-window/chat-window.component.scss', './supervise-window.component.scss']
})
export class SuperviseWindowComponent implements OnInit {
  showViewFilePopup: boolean = false;
  client: Client;
  chats: Chat[] = [];
  userPermissions: Permissions = this.chatService.userPermissions;
  negetiveInfinity: number = -1000000;
  chatsLoading: boolean = true;
  chatClosed: boolean = false;
  gettingLanguage: any;

  @ViewChild('scroll') scroll: PerfectScrollbarComponent;
  @Input('language') language: Object;

  constructor(private chatService: ChatService,
    private wsService: WebsocketService,
    private ngZone: NgZone) {
  }

  ngOnInit() {
    this.chatService.languageObservable.subscribe(
      (res) => {
        this.gettingLanguage = res;
      });
    this.getChats();
    this.subscribeNewChats();
  }

  subscribeNewChats() {
    this.wsService.incomingChatObservable.subscribe(
      (chat: any) => {
        if (chat.message && chat.isCurrent) {
          this.ngZone.run(() => {
            this.chats.push(chat);
            this.scrollChat();
          })
        }
      }
    )
  }

  getChats() {
    this.chatService.chatObservable.subscribe(
      (client: any) => {
        this.chatsLoading = true;
        this.client = client;
        if (this.client.isClosed) {
          this.chatClosed = this.client.isClosed;
          return false;
        }
        else this.chatClosed = false;
        if (this.client.channelId) {
          this.chatService.getChats(this.client)
            .subscribe((response: any) => {
              if (response.status) {
                this.chats = [];
                this.chats = this.chatService.convertChats(response);
                this.scrollChat();
              }
              setTimeout(() => {
                this.chatsLoading = false;
              }, 500);
            },
              error => {
                alert('no data found!');
              });
        }
      }
    )
  }

  sendChat(output: any) {
    // console.log(output);
    let message = output.message;
    let chat = new Chat;
    chat.message = new Message;
    chat.chatTime = moment().format('hh:mm a');
    chat.chatDate = moment().format('MMM DD, YYYY');
    chat.message.text = message;
    chat.messageType = "internal";
    chat.recipient = "AGENT";
    chat.agentDisplayName = this.chatService.agentName;
    this.chats.push(chat);
    this.chatService.sendChat(message, this.client, "supervise").subscribe(data => {
    });
  }


  scrollChat() {
    if (!this.scroll) {
      setTimeout(() => {
        if (this.scroll) this.scroll.directiveRef.scrollToBottom(this.negetiveInfinity, 1);
      }, 0);
    }
    else {
      this.scroll.directiveRef.scrollToBottom(this.negetiveInfinity, 1);
    }
  }
  openPopup(output: any) {
    switch (output.type) {
      case "viewFile": {
        this.showViewFilePopup = true;
        break;
      }
    }
  }
  closedPopup(output: any){
    this.showViewFilePopup = false;
  }
}
