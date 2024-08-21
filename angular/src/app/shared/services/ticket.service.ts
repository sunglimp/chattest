import { Injectable } from "@angular/core";
import { ChatService } from "./chat.service";
import { BehaviorSubject, Subject } from "rxjs";

@Injectable({
  providedIn: "root"
})
export class TicketService {
  archiveRemoveSubject = new BehaviorSubject(<string>"");
  archiveRemoveObservable = this.archiveRemoveSubject.asObservable();
  ticketSubject = new Subject();
  ticketObservable = this.ticketSubject.asObservable();
  lqsSubject = new Subject();
  lqsObservable = this.lqsSubject.asObservable();

  constructor(private chatService: ChatService) {}

  getFields(appId: number) {
    return this.chatService.get(`/v1/tickets/fields/${appId}`);
  }

  createTicket(payload: any) {
    return this.chatService.post(`/v1/tickets/create-ticket`, payload);
  }

  classifyChatML(channelId: number) {
    let form = new FormData();
    form.append("channel_id", channelId.toString());
    form.append("ticket_status", "2");
    return this.chatService.post(
      `/v1/mlmodel/change-classified-chat-status`,
      form
    );
  }

  discardChatML(channelId: number) {
    let form = new FormData();
    form.append("channel_id", channelId.toString());
    form.append("ticket_status", "0");
    return this.chatService.post(
      `/v1/mlmodel/change-classified-chat-status`,
      form
    );
  }

  getStatus(ticketId: number) {
    return this.chatService.get(`/v1/tickets/ticket-details/${ticketId}`);
  }

  changeTicketType(channelId: number, type: string) {
    let form = new FormData();
    form.append("channel_id", channelId.toString());
    form.append("ticket_type", type);
    return this.chatService.post(
      `/v1/mlmodel/change-classified-chat-status`,
      form
    );
  }

  getLeadStatus(leadId: number) {
    return this.chatService.get(`/v1/tickets/lead-details/${leadId}`);
  }
  getLanguage() {
    return this.chatService.get(`/v1/chats/language`);
  }
}
