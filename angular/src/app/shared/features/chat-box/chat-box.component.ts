import {
  Component,
  OnInit,
  Input,
  OnChanges,
  ViewChild,
  Output,
  EventEmitter,
  NgZone
} from "@angular/core";
import { Client, Chat, Permissions } from "../../models/client.model";
import { PerfectScrollbarComponent } from "ngx-perfect-scrollbar";
import { trigger, transition, style, animate } from "@angular/animations";
import { ChatService } from "../../services/chat.service";
import { WebsocketService } from "../../services/websocket.service";
import { TicketService } from "../../services/ticket.service";
import { ArchiveService } from "../../services/archive.service";
import { Router } from "@angular/router";

@Component({
  selector: "app-chat-box",
  templateUrl: "./chat-box.component.html",
  styleUrls: ["./chat-box.component.scss"],
  animations: [
    trigger("enterAnimation", [
      transition(":enter", [
        style({ transform: "translateX(100%)", opacity: 0 }),
        animate("300ms", style({ transform: "translateX(0)", opacity: 1 }))
      ]),
      transition(":leave", [
        style({ transform: "translateX(0)", opacity: 1 }),
        animate("300ms", style({ transform: "translateX(100%)", opacity: 0 }))
      ])
    ])
  ]
})
export class ChatBoxComponent implements OnInit, OnChanges {
  fileURL: any;
  showViewFilePopup: boolean = false;
  featureBool: boolean = false;
  featureShown: string = "";
  featureShownText: string = "";
  featureShownTag: string = "";
  isLoading: boolean = true;
  route: string = this.chatService.route;
  showBanPopup: boolean = false;
  showAddTag: Boolean = true;
  canCreateTicket: boolean = this.wsService.isTicket;
  showTicketDropdown: boolean = false;
  classificationMoves: any = "Business";
  chatAgent: boolean = true;
  clientSourceType: any;
  userPermissions: Permissions = this.chatService.userPermissions;
  currentTime = new Date();
  chatsIcon = [];
  isSuperAdmin: boolean = this.chatService.isSuperAdmin;
  @Output() openPopup: EventEmitter<{}> = new EventEmitter();
  @Output() cancelAttachment: EventEmitter<{}> = new EventEmitter();
  _client: Client;
  link: string;
  currentScrollPos: number;
  ticketType: string;
  @Input("startDate") startDate;
  @Input("endDate") endDate;

  @Input()
  set client(input: Client) {
    this._client = input;
    this.featureBool = false;
    this.clickedHistoryButton = false;
  }
  get client(): Client {
    return this._client;
  }
  @Input("language") language;
  @Input() chats: Chat;
  @Input() chatsLength: number;
  @Input() chatsLoading: number;
  @Input() showPreviousButton: boolean;
  @Input() keyEnter: any;
  _uploadDetails: any;
  clickedHistoryButton: boolean = false;
  @Input()
  set uploadDetails(input: any) {
    this._uploadDetails = input;
  }
  @Input() chatClassificationForMove: string;
  get uploadDetails(): any {
    return this._uploadDetails;
  }
  @ViewChild("scroll") scroll: PerfectScrollbarComponent;
  gettingLanguage: any;

  constructor(
    private archiveService: ArchiveService,
    public chatService: ChatService,
    private ngZone: NgZone,
    private _ticketService: TicketService,
    private wsService: WebsocketService,
    private ticketService: TicketService,
    private _router: Router
  ) {}

  ngOnInit() {
    // this.gettingLanguage = this.language ;
    // console.log("chat-box-chat", this.gettingLanguage)
    // this.chatService.languageObservable.subscribe(
    //   (res) => {
    //     this.gettingLanguage = res['data']['interpretation'];
    //     console.log("chat-box-chat2", this.gettingLanguage)
    //
    //   });
    this.getLanguage();
    this.clientSourceType = this.client;
    this.chatService.chatTicketTypeObservable.subscribe((data: Object) => {
      this.classificationMoves = data;
      if (this.classificationMoves == "Services") {
        this.ticketType = "TMS";
      } else {
        this.ticketType = "LQS";
      }
    });

    if (this._router.url === "/archive") {
      this.chatService
        .getClientInfo(this.client.clientId, this.archiveService.channelId)
        .subscribe(res => {
          if (res["status"]) {
            this.client.clientInfo = res["data"];
          }
        });
    }
  }

  ngOnChanges() {
    if (!this.clickedHistoryButton) {
      this.scrollChat();
    }
    this.clickedHistoryButton = false;
  }
  getLanguage() {
    this.chatService.getLanguage().subscribe(res => {
      this.gettingLanguage = res["data"]["interpretation"];
    });
  }
  showFeatures(event, feature: string) {
    event.stopPropagation();
    this.featureBool = true;
    this.featureShown = feature;
    this.featureShownText = this.gettingLanguage["chat"][
      "ui_elements_messages"
    ]["info"]
      ? this.gettingLanguage["chat"]["ui_elements_messages"]["info"]
      : "info";
    this.featureShownTag = this.gettingLanguage["chat"]["ui_elements_messages"][
      "tags"
    ]
      ? this.gettingLanguage["chat"]["ui_elements_messages"]["tags"]
      : "tags";
    const userInfo = JSON.parse(localStorage.getItem("currentUserInfo"));
    const roleId = userInfo["role_id"];
    this.showAddTag = this.userPermissions["tagSettings"]["tag_creation"][
      roleId
    ];
  }

  scrollChat() {
    if (!this.scroll) {
      setTimeout(() => {
        if (this.scroll)
          this.scroll.directiveRef.scrollToBottom(
            this.chatService.negetiveInfinity,
            1
          );
      }, 0);
    } else {
      this.scroll.directiveRef.scrollToBottom(
        this.chatService.negetiveInfinity,
        1
      );
    }
  }

  hideFeatures() {
    this.featureBool = false;
    this.showTicketDropdown = false;
  }

  showEmail() {
    this.openPopup.emit({ type: "email" });
  }

  createTicket() {
    this.chatService.lqsIsMinimized = false;
    // console.log(this.chatService.lqsIsMinimized);
    // console.log(this.chatService.ticketIsMinimized);
    if (this.chatService.ticketIsMinimized) {
      // when ticket is minimised repoen lqs
      this.chatService.ticketIsMinimized = false;
    } else {
      this._ticketService.lqsSubject.next(true);
      setTimeout(() => {
        this.openPopup.emit({ type: "ticket" });
      }, 0);
    }
  }

  createTicketLQS() {
    // console.log(this.chatService.lqsIsMinimized);
    // console.log(this.chatService.ticketIsMinimized);
    this.chatService.ticketIsMinimized = false;
    if (this.chatService.lqsIsMinimized) {
      // when ticket is minimised repoen lqs
      this.chatService.lqsIsMinimized = false;
    } else {
      this._ticketService.ticketSubject.next(true);
      setTimeout(() => {
        this.openPopup.emit({ type: "ticketLQS" });
      }, 0);
    }
  }

  downloadViewAttachment(chat, index: number, hash: string, SelectedFile) {
    if (chat == "botChat") {
      let fileExtension = hash["message"]["extension"]
        .split(/\#|\?/)[0]
        .split(".")
        .pop()
        .trim();
      var data = [
        {
          index: index,
          url: hash["filePath"],
          file: SelectedFile,
          extension: fileExtension,
          botChat: hash["botChat"]
        }
      ];
      this.chatService.clickedFile(data);
      this.openPopup.emit({ type: "viewFile" });
    } else {
      this.chatService
        .downloadViewAttachment(hash)
        .subscribe((response: any) => {
          this.fileURL = window.URL.createObjectURL(response);
          var a = document.createElement("a");
          document.body.appendChild(a);
          a.href = this.chatService.getHashUrl(hash);
          let fileName, fileExtension;
          fileName = a.href;
          fileExtension = fileName
            .split(/\#|\?/)[0]
            .split(".")
            .pop()
            .trim();
          var data = [
            {
              index: index,
              url: this.fileURL,
              type: response.type,
              file: SelectedFile,
              hash: hash,
              extension: fileExtension
            }
          ];
          this.chatService.clickedFile(data);
          this.openPopup.emit({ type: "viewFile" });
        });
    }
  }

  stopAttachment(event) {
    event.stopPropagation();
    this.ngZone.run(() => {
      this.uploadDetails.uploadPercentage = 100;
      this.uploadDetails.isCompleted = true;
    });
    this.cancelAttachment.emit(true);
  }

  banUser() {
    this.openPopup.emit({ type: "banUser" });
  }

  revokeUser() {
    this.openPopup.emit({ type: "revokeUser" });
  }

  getClientHistoryChats() {
    this.clickedHistoryButton = true;
    this.scroll.directiveRef.scrollToTop(this.chatService.negetiveInfinity, 1);
    this.openPopup.emit({ type: "history" });
  }

  toggleTicketDropdown(event) {
    event.stopPropagation();
    this.showTicketDropdown = !this.showTicketDropdown;
  }

  changeTicketStatus(event, response: string) {
    event.stopPropagation();
    this.showTicketDropdown = false;
    if (response === "accept") {
      this.ticketService.archiveRemoveSubject.next("showTicketForm");
    } else if (response === "reject") {
      this.ticketService.archiveRemoveSubject.next("showDiscardWarning");
    } else if (response === "move") {
      this.ticketService.archiveRemoveSubject.next("showMoveWarning");
    }
  }

  checkOldChat(chat) {
    var messageTime = new Date(chat.chatDate + " " + chat.chatTime);
    messageTime.setMinutes(messageTime.getMinutes() + 1);
    if (messageTime.getTime() < this.currentTime.getTime()) {
      return true;
    } else {
      return false;
    }
  }
  checkNewChat(chat) {
    var messageTime = new Date(chat.chatDate + " " + chat.chatTime);
    messageTime.setMinutes(messageTime.getMinutes() + 1);
    if (messageTime.getTime() >= this.currentTime.getTime()) {
      return true;
    } else {
      return false;
    }
  }

  downloadChat() {
    // console.log(document.querySelector("span.response-message"));
    this.archiveService
      .downloadArchivedChats(
        this.archiveService.clientId,
        this.chatService.agentId,
        this.archiveService.channelId,
        this.startDate,
        this.endDate
      )
      .subscribe(res => {
        if (res["body"]["status"] === 200) {
          const blob = new Blob([res["body"]["chats"]], {
            type: "text/plain"
          });
          const url = window.URL.createObjectURL(blob);
          const a = document.createElement("a");
          a.style.display = "none";
          a.href = url;
          // the filename you want
          a.download = res["body"]["file_name"] + ".txt";
          document.body.appendChild(a);
          a.click();
          window.URL.revokeObjectURL(url);
        } else {
          alert(res["body"]["message"]);
        }
      });
  }

  openInNewWindow(location) {
    let url =
      "http://www.google.com/maps/place/@" +
      location.latitude +
      "," +
      location.longitude +
      ",17z/";

    if (location.name && location.address) {
      url =
        "http://www.google.com/maps/place/" +
        location.name +
        "," +
        location.address +
        "/@" +
        location.latitude +
        "," +
        location.longitude +
        ",17z/";
    }
    window.open(url);
  }

  openedWindow: number = 0; // alternative: array of numbers

  openWindow(id) {
    this.openedWindow = id; // alternative: push to array of numbers
  }

  isInfoWindowOpen(id) {
    return this.openedWindow == id; // alternative: check if id is in array
  }
}
