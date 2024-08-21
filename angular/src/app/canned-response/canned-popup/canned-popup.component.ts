import { Component, Input, OnInit, Output, EventEmitter } from "@angular/core";
import { CannedResponse } from "src/app/shared/models/canned-response.model";
import { ChatService } from "src/app/shared/services/chat.service";
import { CannedResponseService } from "../canned-response.service";

@Component({
  selector: "canned-popup",
  templateUrl: "./canned-popup.component.html",
  styleUrls: ["./canned-popup.component.scss"]
})
export class CannedPopupComponent implements OnInit {
  @Input() cannedResponse: CannedResponse = new CannedResponse();
  @Input() isShowCannedDetail: boolean;
  @Input() header: string = "";
  isCannedResponseDataNeedRefreshed: boolean = false;
  @Output() cannedResponseValueChange = new EventEmitter();
  @Output() closePopUp = new EventEmitter();
  gettingLanguage: any;
  constructor(
    private _cannedResponseService: CannedResponseService,
    public chatService: ChatService
  ) {}

  ngOnInit() {
    this.getLanguage();
  }

  cannedError = "";
  addCannedResponse(isValid) {
    this.cannedError = "";
    if (isValid) {
      if (this.cannedResponse.cannedResponseId) {
        this._cannedResponseService
          .put("/v1/cannedResponses/update", this.cannedResponse)
          .subscribe(
            (res: any) => {
              if (res["status"]) {
                this.showNotifierMessage(res, false);
                this.isCannedResponseDataNeedRefreshed = !this
                  .isCannedResponseDataNeedRefreshed;
                this.cannedResponseValueChange.emit(
                  this.isCannedResponseDataNeedRefreshed
                );
              } else {
                // this.showNotifierMessage(res["error"], true);
              }
              this.onCancel();
            },
            error => {
              console.log(error);
              this.cannedError = error["error"]["message"];
              // this.showNotifierMessage(error[error], true);
            }
          );
      } else {
        this._cannedResponseService
          .post("/v1/cannedResponses/add", this.cannedResponse)
          .subscribe(
            (res: any) => {
              if (res["status"]) {
                this.showNotifierMessage(res, false);
                this.isCannedResponseDataNeedRefreshed = !this
                  .isCannedResponseDataNeedRefreshed;
                this.cannedResponseValueChange.emit(
                  this.isCannedResponseDataNeedRefreshed
                );
              } else {
                // this.showNotifierMessage(res["error"], true);
              }
              this.onCancel();
            },
            error => {
              console.log(error);
              this.cannedError = error["error"]["message"];
              // this.showNotifierMessage(error["error"], true);
            }
          );
      }
    }
  }

  getLanguage() {
    this.chatService.getLanguage().subscribe(res => {
      this.gettingLanguage = res["data"]["interpretation"];
    });
  }

  onCancel() {
    this.isShowCannedDetail = !this.isShowCannedDetail;
    this.closePopUp.emit(this.isShowCannedDetail);
    this.cannedError = "";
  }

  notifier: any = {};
  showNotifierMessage(res, type) {
    this.notifier.text = res.errors ? res.errors : res.message;
    this.notifier.show = true;
    this.notifier.iserror = type;
    setTimeout(() => {
      this.notifier = {};
    }, 5000);
  }
}
