import { Component, OnInit, Input, ViewChild } from '@angular/core';
import { ArchiveService } from 'src/app/shared/services/archive.service';
// import * as moment from 'moment';
import { Client, Chat, Message } from 'src/app/shared/models/client.model';
import { PerfectScrollbarComponent } from 'ngx-perfect-scrollbar';
import { ChatService } from 'src/app/shared/services/chat.service';
import { TicketService } from 'src/app/shared/services/ticket.service';

@Component({
  selector: 'app-archive-chat',
  templateUrl: './archive-chat.component.html',
  styleUrls: ['./../../chats/chat-window/chat-window.component.scss', './archive-chat.component.scss']

})
export class ArchiveChatComponent implements OnInit {
  @Input('language') language;
  @Input('startDate') startDate;
  @Input('endDate') endDate;
  @Input('selectedType') selectedType;
  gettingLanguage: object;
  chats: Chat[] = [];
  client: Client;
  chatsLoading: boolean = true;
  @ViewChild('scroll') scroll: PerfectScrollbarComponent;
  negetiveInfinity: number = -1000000;
  showTicketPopup: boolean = false;
  route: string = this.chatService.route;
  showWarningPopup: boolean = false;
  showViewFilePopup: boolean = false;
  warningMessage = 'Do you really want to discard this chat?';
  popupClicked: string;
  selectedTypeTicket: any = '';
  appId: string = '';
  isSuperAdmin: boolean = this.chatService.isSuperAdmin;
  constructor(private archiveService: ArchiveService, private chatService: ChatService, private ticketService: TicketService) { }

  ngOnInit() {

    this.chatService.languageObservable.subscribe(
      (res) => {
        this.gettingLanguage = res['data'];
        /*console.log("archieve-chat",this.gettingLanguage);*/
      });

    this.getClientInfo();
    this.chatService.chatTicketTypeObservable.subscribe(
      (data: Object) => {
        this.selectedTypeTicket = data;
        if (this.selectedTypeTicket == 'Services') {
          this.appId = '3';
        } else {
          this.appId = '1';
        }
      }
    );
    if (this.route === 'ticket') {
      this.ticketService.archiveRemoveObservable.subscribe((input: string) => {
        if (input === 'showDiscardWarning') {
          // this.warningMessage = `Do you really want to discard this chat?`
          this.warningMessage = this.gettingLanguage['interpretation']['chat']['ui_elements_messages']['discard_chat'] ? this.gettingLanguage['interpretation']['chat']['ui_elements_messages']['discard_chat'] : 'Do you really want to discard this chat?';
          this.showWarningPopup = true;
          // this.warningMessage = 'Do you really want to discard this chat?';
          this.warningMessage = this.gettingLanguage['interpretation']['chat']['ui_elements_messages']['discard_chat'] ? this.gettingLanguage['interpretation']['chat']['ui_elements_messages']['discard_chat'] : 'Do you really want to discard this chat?';
          this.popupClicked = 'discard';
        }
        else if (input === 'showMoveWarning') {
          this.showWarningPopup = true;
          let category = this.selectedType === 'Services' ? 'Business Ticket' : 'Service Ticket';
          if(category == 'Business Ticket'){
            this.warningMessage = this.gettingLanguage['interpretation']['chat']['ui_elements_messages']['msg_change_ticket_category'] ? this.gettingLanguage['interpretation']['chat']['ui_elements_messages']['msg_change_ticket_category'] : 'Do you want to change this ticket category to Business Ticket?';
          }else{
            this.warningMessage = this.gettingLanguage['interpretation']['chat']['ui_elements_messages']['service_ticket_confirm'] ? this.gettingLanguage['interpretation']['chat']['ui_elements_messages']['service_ticket_confirm'] : 'Do you want to change this ticket category to Service Ticket?';
          }
          // this.warningMessage = `Do you want to change this ticket category to ${category}?`;
          this.popupClicked = 'move';
        }
        else if (input === 'showTicketForm') {
          this.showTicketPopup = true;
        }
      })
    }
  }



  getClientInfo() {
    this.chatService.chatObservable.subscribe(
      (client: Client) => {
        if (client.isClosed) {
          this.chats = [];
          return false;
        }
        this.chatsLoading = true;
        this.client = client;
        this.archiveService.clientId = client.clientId;
        this.archiveService.channelId = client.channelId;
        if (client.clientId) this.getArchivedChats(client.clientId);
      });
  }

  getArchivedChats(clientId: number) {
    if (this.route === 'banned-users') {
      this.archiveService.getBannedUsersChats(clientId).subscribe(
        (response: any) => {
          if (response.status) {
            this.chats = [];
            this.chats = this.chatService.convertChats(response);
          }
          this.chatsLoading = false;
        }
      )
    }
    else {
      this.archiveService.getArchivedChats(clientId, this.client.channelId, this.startDate, this.endDate, 1).subscribe(
        (response: any) => {
          if (response.status) {
            this.chats = [];
            this.chats = this.chatService.convertChats(response);
          }
          this.chatsLoading = false;
        });
    }

    if (this.scroll) this.scroll.directiveRef.scrollToBottom(this.negetiveInfinity, 500);
  }

  ticketDiscardOutput(event) {
    if (event) {
      if (this.route === 'banned-users') {
        this.archiveService.revokeUser(this.client.clientId).subscribe(
          (response: any) => {
            if (response.status) {
              this.ticketService.archiveRemoveSubject.next('warningOutput');
            }
          }
        )
      }
      else {
        if (this.popupClicked === 'discard') {
          this.ticketService.discardChatML(this.client.channelId).subscribe(
            (response: any) => {
              if (response.status) {
                this.ticketService.archiveRemoveSubject.next('warningOutput');
              }
            }
          );
        }
        else {
          let category = this.selectedType === 'Services' ? 'BUSINESS TICKETS' : 'SERVICE TICKETS';
          this.ticketService.changeTicketType(this.client.channelId, category).subscribe(
            (response: any) => {
              if (response.status) {
                this.ticketService.archiveRemoveSubject.next('warningOutput');
              }
            }
          );
        }
      }
    }
    this.showWarningPopup = false;
  }

  ticketOutput(output: any) {
    if (output.ticketCreated) {
      this.ticketService.classifyChatML(this.client.channelId).subscribe(
        (response: any) => {
          if (response.status) {
            this.ticketService.archiveRemoveSubject.next('warningOutput');
          }
        }
      );
    }
    else if (output.close) {
      this.showTicketPopup = false;
    }
    if (output.closeSuccess) {
      this.showTicketPopup = false;
      let category = this.selectedType === 'Services' ? 'BUSINESS TICKETS' : 'SERVICE TICKETS';
      this.ticketService.changeTicketType(this.client.channelId, category).subscribe(
        (response: any) => {
          if (response.status) {
            this.ticketService.archiveRemoveSubject.next('warningOutput');
          }
        }
      );
    }
  }

  openPopup(event: any) {
    if (event.type == 'viewFile') {
      this.showViewFilePopup = true;
     }else{
      if(this.isSuperAdmin){
        this.warningMessage = `Are you sure want to remove the user from banned list?`
      }else{
        this.warningMessage = this.gettingLanguage['interpretation']['banned_users']['ui_elements_messages']['are_you_sure_want_to_remove_the_user_from_banned_list'] ? this.gettingLanguage['interpretation']['banned_users']['ui_elements_messages']['are_you_sure_want_to_remove_the_user_from_banned_list'] : 'Are you sure want to remove the user from banned list?';
      }
      this.showWarningPopup = true;
    }

  }
  closedPopup(output: any) {
    this.showViewFilePopup = false;
  }

}
