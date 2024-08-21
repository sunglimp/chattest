import { Component, OnInit, Input, ViewEncapsulation } from "@angular/core";
import { ChatService } from "../../services/chat.service";
import { DomSanitizer } from "@angular/platform-browser";

@Component({
  selector: "app-info",
  templateUrl: "./info.component.html",
  styleUrls: ["./info.component.scss"],
  encapsulation: ViewEncapsulation.None
})
export class InfoComponent implements OnInit {
  clientInfo: Object[] = [];
  @Input("showFeature") showFeature: boolean;
  @Input("info") info: Object;
  infoHtml: string;
  constructor(
    public chatService: ChatService,
    private sanitizer: DomSanitizer
  ) {}

  ngOnInit() {
    // let keys = Object.keys(this.info);
    // debugger;
    // keys.forEach(key => {
    //   if(this.info[key]){
    //     this.clientInfo.push({
    //       'keyName': key.toString(),
    //       'keyValue': this.info[key].toString()
    //     });
    //     console.log("this.cline", this.clientInfo);
    //     this.clientInfo.forEach(keyValue => {
    //       if(typeof keyValue['keyValue'] === 'object'){
    //         console.log("1",1);
    //       }
    //     })
    //   }
    // })
    this.getValue(this.info);
    this.getLanguage();
  }

  getValue(data) {
    let keys = Object.keys(data);
    this.infoHtml = '<ul class="info__ul" >';
    keys.forEach(key => {
      if (data[key]) {
        if (this.chatService.currentLanguage === "ar") {
          this.infoHtml +=
            ' <li class="info__li" dir="ltr">' +
            this.getArrayObject(data[key]) +
            ":" +
            key.toString() +
            "</li>";
        } else {
          this.infoHtml +=
            ' <li class="info__li" dir="ltr">' +
            key.toString() +
            ":" +
            this.getArrayObject(data[key]) +
            "</li>";
        }
      }
    });
    this.infoHtml += "</ul>";
  }

  getArrayObject(data) {
    if (typeof data != "object") {
      return data.toString();
    } else {
      let infoHtml = '<ul class="infos__ul" >';
      let keys = Object.keys(data);
      keys.forEach(key => {
        if (data[key]) {
          if (this.chatService.currentLanguage === "ar") {
            infoHtml +=
              ' <li class="infos__li" dir="ltr">' +
              this.getArrayObject(data[key]) +
              ":" +
              key.toString() +
              "</li>";
          } else {
            infoHtml +=
              ' <li class="infos__li" dir="ltr">' +
              key.toString() +
              ":" +
              this.getArrayObject(data[key]) +
              "</li>";
          }
        }
      });
      infoHtml += "</ul>";
      return infoHtml;
    }
  }
  isObject(data) {
    return typeof data === "object";
  }

  getLanguage() {
    this.chatService.getLanguage().subscribe(res => {
      this.chatService.currentLanguage = res["data"]["language"];
    });
  }
}
