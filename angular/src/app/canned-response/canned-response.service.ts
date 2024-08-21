import { Injectable } from "@angular/core";
import { HttpHeaders, HttpClient } from "@angular/common/http";
import { CannedResponse } from "../shared/models/canned-response.model";

const httpOptions = {
  headers: new HttpHeaders({
    Authorization: "Bearer " + window["USER"].api_token
  })
};

@Injectable({
  providedIn: "root"
})
export class CannedResponseService {
  hostUrl: String;
  cannedResponse: CannedResponse = new CannedResponse();
  constructor(private http: HttpClient) {
    this.hostUrl = window["APP_URL"];
  }

  getCannedResponses(pageNum, searchKey?) {
    const page = pageNum ? pageNum : 1;
    const search = searchKey ? searchKey : "";
    const url = `${this.hostUrl}/api/v1/cannedResponses/canned-responses?page=${page}&search=${search}`;
    return this.http.get(url, httpOptions);
  }

  // get(restApi: string) {
  //   return this.http.get(`${this.hostUrl}/api${restApi}`, httpOptions);
  // }

  post(restApi: string, payload: CannedResponse) {
    return this.http.post(
      `${this.hostUrl}/api${restApi}`,
      payload,
      httpOptions
    );
  }

  put(restApi: string, payload: any) {
    return this.http.put(`${this.hostUrl}/api${restApi}`, payload, httpOptions);
  }

  delete(restApi: string, cannedResponseId: string) {
    return this.http.delete(
      `${this.hostUrl}/api${restApi}/${cannedResponseId}`,
      httpOptions
    );
  }
}
