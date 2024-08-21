import { Injectable } from "@angular/core";
import { ChatService } from "./chat.service";
import { BehaviorSubject } from "rxjs";
import { Client } from "../models/client.model";
import { WebsocketService } from "./websocket.service";
import { HttpClient, HttpHeaders } from "@angular/common/http";

const httpOptionsWithResponse = {
  headers: new HttpHeaders({
    Authorization: "Bearer " + window["USER"].api_token
  }),
  observe: "response" as "body"
};

@Injectable({
  providedIn: "root"
})
export class ArchiveService {
  classificationMoves: string;
  archiveSubject = new BehaviorSubject(<Client>{});
  archiveObservable = this.archiveSubject.asObservable();
  chatTaggedSubject = new BehaviorSubject(<any>-1);
  chatTaggedObservable = this.chatTaggedSubject.asObservable();
  chatMoveSubject = new BehaviorSubject(<Object>{});
  chatMoveObservable = this.chatMoveSubject.asObservable();
  clientId: number;
  channelId: number;
  userId: number;

  constructor(
    private chatService: ChatService,
    private wsService: WebsocketService,
    private http: HttpClient
  ) {
    // console.log(this.today);
  }

  getArchivedClients(
    startDate: string,
    endDate: string,
    page: number,
    searchKey: any,
    searchType: number,
    chatClassification: string,
    reportee: string,
    startTime: any,
    endTime: any
  ) {
    this.classificationMoves = chatClassification;
    if (this.wsService.isTicket) {
      // ticket page
      return this.chatService.get(
        `/v1/chats/archive/${this.chatService.agentId}/client?start_date=${startDate}&end_date=${endDate}&page=${page}&search=${searchKey}&type=${searchType}&is_ticket=${this.wsService.isTicket}&ticket_type=${chatClassification}`
      );
    } else {
      // archive page
      return this.chatService.get(
        `/v1/chats/archive/${this.chatService.agentId}/client?start_date=${startDate}&end_date=${endDate}&page=${page}&search=${searchKey}&type=${searchType}&is_ticket=${this.wsService.isTicket}&reportee=${reportee}&startTime=${startTime}&endTime=${endTime}`
      );
    }
  }
  getArchivedChats(
    clientId: number,
    channelId: number,
    startDate: string,
    endDate: string,
    page: number
  ) {
    return this.chatService.get(
      `/v1/chats/archive/${this.chatService.agentId}/client/${clientId}/chat?channel_id=${channelId}&start_date=${startDate}&end_date=${endDate}&page=${page}&is_ticket=${this.wsService.isTicket}`
    );
  }

  downloadArchivedChats(
    clientId: number,
    agentId: number,
    channelId: number,
    startDate: string,
    endDate: string
  ) {
    return this.http.get(
      `/api/v1/chats/archive/${agentId}/client/${clientId}/download-chat?channel_id=${channelId}&start_date=${startDate}&end_date=${endDate}`,
      httpOptionsWithResponse
    );
  }

  downloadTagedArchivedChats(
    startDate: string,
    endDate: string,
    searchType: number,
    searchList: any,
    userId: number,
    reportee?: number
  ) {
    let url = `/api/v1/chats/archive/${userId}/download-tag-report?start_date=${startDate}&end_date=${endDate}&search=${searchList}&type=${searchType}`;
    if (reportee) {
      url = `/api/v1/chats/archive/${userId}/download-tag-report?start_date=${startDate}&end_date=${endDate}&search=${searchList}&type=${searchType}&reportee=${reportee}`;
    }
    return this.http.get(url, httpOptionsWithResponse);
  }

  downloadArchivedExcelChats(
    startDate: string,
    endDate: string,
    userId: number,
    reportee?: number
  ) {
    let url = `/api/v1/chats/archive/${userId}/download-chat?start_date=${startDate}&end_date=${endDate}`;
    if (reportee) {
      url = `/api/v1/chats/archive/${userId}/download-chat?start_date=${startDate}&end_date=${endDate}&reportee=${reportee}`;
    }
    return this.http.get(url, httpOptionsWithResponse);
  }

  getReportData() {
    return this.http.get(
      `${this.chatService.hostUrl}/api/v1/chats/get_reportees_dropdown`,
      httpOptionsWithResponse
    );
  }

  getBannedUsers(
    organizationId: number,
    startDate: string,
    endDate: string,
    keyword: number,
    searchKey: string
  ) {
    return this.chatService.get(
      `/v1/bannedClients?organization_id=${organizationId}&start_date=${startDate}&end_date=${endDate}&keyword=${keyword}&search=${searchKey}`
    );
    // return this.chatService.get(`/v1/bannedClients?organization_id=${organizationId}&start_date=10-01-2019&end_date=25-04-2019&keyword=1&search=sda`);
  }

  getBannedUsersChats(clientId: number) {
    return this.chatService.get(`/v1/bannedClients/${clientId}`);
  }

  revokeUser(clientId: number) {
    return this.chatService.delete(`/v1/bannedClients/${clientId}`);
  }

  getOrganization() {
    return this.chatService.get(`/v1/organizations`);
  }
  /*for move to LMS and TMS*/
  classificationChats(classificationMoves) {
    this.chatMoveSubject.next(classificationMoves);
  }

  getTags(reportee) {
    const url = !reportee
      ? `/v1/tags/get_chat_tags`
      : "/v1/tags/get_chat_tags?reportee=" + reportee;
    return this.chatService.get(url);
  }
}
