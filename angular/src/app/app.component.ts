import { Component } from "@angular/core";
import { ChatService } from "./shared/services/chat.service";

@Component({
  selector: "app-root",
  templateUrl: "./app.component.html",
  styleUrls: ["./app.component.scss"]
})
export class AppComponent {
  // title = 'live-chat';
  route: string;
  constructor(private chatService: ChatService) {
    this.route = this.chatService.route;
  }
  closePopups() {
    this.chatService.closeSubject.next(true);
  }
}
