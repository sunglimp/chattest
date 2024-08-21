import { Component, OnInit } from "@angular/core";
import { TicketService } from "../shared/services/ticket.service";
import { ChatService } from "../shared/services/chat.service";
import * as moment from "moment";

@Component({
  selector: "app-ticket-status",
  templateUrl: "./ticket-status.component.html",
  styleUrls: ["./ticket-status.component.scss"]
})
export class TicketStatusComponent implements OnInit {
  gettingLanguage: object;
  responseMessage: string;
  showDetails: boolean = false;
  showError: boolean = false;
  details: any[] = [];
  activities: any[] = [];
  headingColorCode: string[] = ["#fbae3a", "#21b573", "#0457FB"];
  trackColorCode: string[] = ["#ea5455", "#0070bd"];
  isLoading: boolean = false;
  ticketStatus: string = "";
  showViewFilePopup: boolean = false;

  constructor(
    private ticketService: TicketService,
    public chatService: ChatService
  ) {}

  ngOnInit() {
    this.getLanguage();
  }

  getTicketStatus(searchField) {
    if (!searchField.value.length) {
      this.showError = false;
    } else if (searchField.value.length > 0) {
      this.isLoading = true;
      this.ticketService.getStatus(searchField.value).subscribe(
        (response: any) => {
          this.responseMessage = response.message;
          if (response.status) {
            this.isLoading = false;
            this.showDetails = true;
            this.showError = false;
            this.details = [];
            this.activities = [];
            this.ticketStatus = response.data[0].status;
            let details = response.data[0].details;
            let keys = Object.keys(details);
            let count = 0;
            keys.forEach(key => {
              this.details.push({
                type: "heading",
                key: key,
                value: key,
                color: this.headingColorCode[count++ % 3]
              });
              let innerKeys = Object.keys(details[key]);
              innerKeys.forEach(k => {
                this.details.push({
                  type: "value",
                  key: k,
                  value: details[key][k]
                });
              });
            });
            count = 0;
            let activity = response.data[0].activity;
            let aKeys = Object.keys(activity);
            aKeys.forEach(key => {
              this.activities.push({
                type: "date",
                key: moment(key).format("DD MMMM"),
                value: key
              });
              let innerAkeys = Object.keys(activity[key]);
              innerAkeys.forEach(k => {
                this.activities.push({
                  type: "time",
                  key: activity[key][k]["time"],
                  value: activity[key][k]["name"],
                  remarks: activity[key][k]["remarks"],
                  color: this.trackColorCode[count++ % 2]
                });
              });
            });
            count = 0;
          }
        },
        error => {
          this.responseMessage = error.error.message;
          this.isLoading = false;
          this.showDetails = false;
          this.showError = true;
        }
      );
    }
  }

  hoverText = "";
  makeText(i) {
    this.hoverText = "";
    this.activities[i].remarks.forEach(text => {
      console.log(text);
      this.hoverText += text.dateTime + " : " + text.remark + "\n";
    });
  }

  search(event, input) {
    if (event.keyCode === 13) {
      this.getTicketStatus(input);
    }
  }
  getLanguage() {
    this.ticketService.getLanguage().subscribe(res => {
      this.gettingLanguage =
        res["data"]["interpretation"]["ticket_enquire"]["ui_elements_messages"];
      this.chatService.currentLanguage = res["data"]["language"];
    });
  }

  downloadViewAttachment(index, hash) {
    this.chatService.downloadViewAttachment(hash).subscribe((response: any) => {
      const fileURL = window.URL.createObjectURL(response);
      var a = document.createElement("a");
      document.body.appendChild(a);
      a.href = this.chatService.downloadAttachment(hash);
      let fileName, fileExtension;
      fileName = a.href;
      fileExtension = fileName
        .split(/\#|\?/)[0]
        .split(".")
        .pop()
        .trim();

      // var data = [
      //   {
      //     index: index,
      //     url: fileURL,
      //     type: response.type,
      //     file: hash,
      //     hash: hash,
      //     extension: fileExtension
      //   }
      // ];
      // console.log(hash);
      // console.log(response);
      // console.log(data);
      // this.chatService.clickedFile(data);
      // this.openPopup({ type: "viewFile" });
    });
  }

  openPopup(event: any) {
    if (event.type == "viewFile") {
      this.showViewFilePopup = true;
    } else {
      this.showViewFilePopup = false;
    }
  }

  closedPopup(output: any) {
    this.showViewFilePopup = false;
  }
}
