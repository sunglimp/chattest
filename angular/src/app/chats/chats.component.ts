import { Component, OnInit } from "@angular/core";
import { ChatService } from "../shared/services/chat.service";

@Component({
  selector: "app-chats",
  templateUrl: "./chats.component.html",
  styleUrls: ["./chats.component.scss"]
})
export class ChatsComponent implements OnInit {
  constructor() {}

  ngOnInit() {}
}
