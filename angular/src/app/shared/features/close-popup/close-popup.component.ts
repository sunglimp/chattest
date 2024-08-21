import { Component, OnInit, Output, EventEmitter, Input } from "@angular/core";
import { ChatService } from "../../services/chat.service";
@Component({
  selector: "app-close-popup",
  templateUrl: "./close-popup.component.html",
  styleUrls: ["./close-popup.component.scss"]
})
export class ClosePopupComponent implements OnInit {
  route: string;
  @Output() outputEvent: EventEmitter<boolean> = new EventEmitter();
  @Input("warningMessage") warningMessage: string;
  @Input("language") language: string;
  isSuperAdmin: boolean = this.chatService.isSuperAdmin;
  gettingLanguage: Object;
  constructor(public chatService: ChatService) {
    this.route = this.chatService.route;
  }

  ngOnInit() {
    this.getLanguage();
  }
  getLanguage() {
    this.chatService.getLanguage().subscribe(res => {
      this.gettingLanguage = res["data"]["interpretation"];
    });
  }
  output(bool: boolean) {
    this.outputEvent.emit(bool);
  }
}
