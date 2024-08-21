import { Injectable } from "@angular/core";
import { HttpHeaders, HttpClient } from "@angular/common/http";

const httpOptions = {
  headers: new HttpHeaders({
    Authorization: "Bearer " + window["USER"].api_token
  })
};

@Injectable({
  providedIn: "root"
})
export class MissedChatService {
  hostUrl: String;

  constructor(private http: HttpClient) {
    this.hostUrl = window["APP_URL"];
  }

  getMissedChats(restApi) {
    const url = `${this.hostUrl}/api${restApi}`;
    return this.http.get(url, httpOptions);
  }

  updateMissedChatStatus(channelId, data) {
    const url = `${this.hostUrl}/api/v1/chats/missed/${channelId}`;
    return this.http.post(url, data, httpOptions);
  }

  getClientQuery(agentId, clienId, channelId, startDate, endDate) {
    const url = `${this.hostUrl}/api/v1/chats/archive/${agentId}/client/${clienId}/chat?channel_id=${channelId}&start_date=${startDate}&end_date=${endDate}&page=1&missed_chat=true`;
    return this.http.get(url, httpOptions);
  }
}
