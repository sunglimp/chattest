import {
  Component,
  OnInit,
  Input,
  ViewChild,
  ElementRef,
  Renderer2,
  Output,
  EventEmitter,
  OnChanges
} from "@angular/core";
import { Permissions, Client, Agent } from "../../models/client.model";
import { ChatService } from "../../services/chat.service";
declare var $: any;
@Component({
  selector: "app-chat-input",
  templateUrl: "./chat-input.component.html",
  styleUrls: ["./chat-input.component.scss"]
})
export class ChatInputComponent implements OnInit, OnChanges {
  @ViewChild("chatMessage") chatMessage: ElementRef;
  @Input("client") client: Client;
  model = "some text";
  userPermissions: Permissions = this.chatService.userPermissions;
  searchKey: string = "";
  showCannedList: boolean = false;
  showInternalAgents: boolean = false;
  isInternalComment: boolean = false;
  selectedInternalAgent: Agent;
  caretPos: any;
  emoji: any = "";
  chatTransferBool: boolean = false;
  showEmojis: boolean = false;
  route: string = this.chatService.route;
  allowedIcons: number = 1;
  allowAttachment: boolean = false;
  allowTransfer: boolean = false;
  showNotifier: boolean = false;
  _uploadDetails: any;
  gettingLanguage: Object;
  languageInput: Object;
  placeholderVal: any;
  @Input("language") language: object;
  @Input()
  set uploadDetails(input: any) {
    this._uploadDetails = input;
  }
  get uploadDetails(): any {
    return this._uploadDetails;
  }

  @Output() chatMessageEmitter: EventEmitter<any> = new EventEmitter();

  constructor(public chatService: ChatService, private renderer: Renderer2) {}

  ngOnChanges() {
    this.chatMessage.nativeElement.innerText = "";
    this.chatMessage.nativeElement.focus();
    this.findCaretPos();
    this.showEmojis = false;
    this.checkPermissions();
  }

  ngOnInit() {
    this.gettingLanguage = this.language["data"]["interpretation"];
    this.model = this.gettingLanguage["chat"]["ui_elements_messages"][
      "type_msg"
    ]
      ? this.gettingLanguage["chat"]["ui_elements_messages"]["type_msg"]
      : "Type a message";
    this.closePopups();
    this.chatMessage.nativeElement.addEventListener("paste", event => {
      event.preventDefault();
      var text = (event.originalEvent || event).clipboardData.getData(
        "text/plain"
      );
      document.execCommand("insertHTML", false, text);
    });
  }
  placeholder() {
    let message = this.chatMessage.nativeElement.innerHTML;
    const emojiPattern = /(?:[\u2700-\u27bf]|(?:\ud83c[\udde6-\uddff]){2}|[\ud800-\udbff][\udc00-\udfff]|[\u0023-\u0039]\ufe0f?\u20e3|\u3299|\u3297|\u303d|\u3030|\u24c2|\ud83c[\udd70-\udd71]|\ud83c[\udd7e-\udd7f]|\ud83c\udd8e|\ud83c[\udd91-\udd9a]|\ud83c[\udde6-\uddff]|\ud83c[\ude01-\ude02]|\ud83c\ude1a|\ud83c\ude2f|\ud83c[\ude32-\ude3a]|\ud83c[\ude50-\ude51]|\u203c|\u2049|[\u25aa-\u25ab]|\u25b6|\u25c0|[\u25fb-\u25fe]|\u00a9|\u00ae|\u2122|\u2139|\ud83c\udc04|[\u2600-\u26FF]|\u2b05|\u2b06|\u2b07|\u2b1b|\u2b1c|\u2b50|\u2b55|\u231a|\u231b|\u2328|\u23cf|[\u23e9-\u23f3]|[\u23f8-\u23fa]|\ud83c\udccf|\u2934|\u2935|[\u2190-\u21ff])/g;
    if (!emojiPattern.test(message)) {
      this.model = "";
    }
  }
  closePopups() {
    this.chatService.closeObservable.subscribe(bool => {
      this.hideFeatures();
    });
  }
  focusOutFunction() {
    let messageHTML = this.chatMessage.nativeElement.innerHTML;
    if (messageHTML.length < 1) {
      this.model = this.gettingLanguage["chat"]["ui_elements_messages"][
        "type_message"
      ];
    }
    //this.model = this.gettingLanguage['chat']['ui_elements_messages']['type_message'];
  }
  sendChat(file?: File) {
    let message = this.chatMessage.nativeElement.innerHTML;
    const emojiPattern = /(?:[\u2700-\u27bf]|(?:\ud83c[\udde6-\uddff]){2}|[\ud800-\udbff][\udc00-\udfff]|[\u0023-\u0039]\ufe0f?\u20e3|\u3299|\u3297|\u303d|\u3030|\u24c2|\ud83c[\udd70-\udd71]|\ud83c[\udd7e-\udd7f]|\ud83c\udd8e|\ud83c[\udd91-\udd9a]|\ud83c[\udde6-\uddff]|\ud83c[\ude01-\ude02]|\ud83c\ude1a|\ud83c\ude2f|\ud83c[\ude32-\ude3a]|\ud83c[\ude50-\ude51]|\u203c|\u2049|[\u25aa-\u25ab]|\u25b6|\u25c0|[\u25fb-\u25fe]|\u00a9|\u00ae|\u2122|\u2139|\ud83c\udc04|[\u2600-\u26FF]|\u2b05|\u2b06|\u2b07|\u2b1b|\u2b1c|\u2b50|\u2b55|\u231a|\u231b|\u2328|\u23cf|[\u23e9-\u23f3]|[\u23f8-\u23fa]|\ud83c\udccf|\u2934|\u2935|[\u2190-\u21ff])/g;

    message = this.convertImgToEmojisUnicode(message);
    message = this.convertBrToNextLine(message);

    if (this.model && !file && !emojiPattern.test(message)) {
      this.model = "";
      return;
    }

    if (
      this.chatMessage.nativeElement.innerHTML.indexOf("agent__selected") < 0
    ) {
      this.isInternalComment = false;
    }

    if (/\S/.test(message) || file) {
      this.chatMessageEmitter.emit({
        message: message,
        isInternalComment: this.isInternalComment,
        selectedInternalAgent: this.selectedInternalAgent,
        file: file,
        source_type: this.client.sourceType
      });
      this.chatMessage.nativeElement.innerHTML = "";
      this.isInternalComment = false;
      this.showEmojis = false;
      setTimeout(() => {
        this.model = this.gettingLanguage["chat"]["ui_elements_messages"][
          "type_message"
        ];
      }, 0);
    }
  }

  chatEnter(event) {
    this.placeholder();
    let keyCode = event.keyCode;
    let messageHTML = this.chatMessage.nativeElement.innerHTML;
    if (
      event.keyCode === 13 &&
      (messageHTML === "Type a message" || messageHTML === "سوربو ايس")
    ) {
      event.preventDefault();
      return;
    }
    if (messageHTML.length < 1) {
      this.searchKey = "";
      if (
        event.shiftKey &&
        keyCode === 51 &&
        this.userPermissions.cannedResponse
      ) {
        this.showCannedList = true;
        this.showInternalAgents = false;
        this.showEmojis = false;
      }
      if (
        event.shiftKey &&
        keyCode === 50 &&
        this.userPermissions.internalComments &&
        this.client.channelType !== "internal_comment" &&
        this.route !== "supervise"
      ) {
        this.showInternalAgents = true;
        this.showCannedList = false;
        this.showEmojis = false;
      }
    }

    if (this.showCannedList || this.showInternalAgents) {
      if (keyCode === 13 || (keyCode > 36 && keyCode < 41))
        event.preventDefault();
      setTimeout(() => {
        if (keyCode === 8 && this.searchKey.length === 0) {
          this.isInternalComment = false;
          this.showInternalAgents = false;
          this.showCannedList = false;
          this.showEmojis = false;
        }
        this.searchKey = this.chatMessage.nativeElement.innerText;
        this.searchKey = this.searchKey.substr(1);

        if (this.showCannedList) {
          this.chatService.keyCodeSubject.next({
            keyCode: keyCode,
            feature: "canned",
            searchKey: this.searchKey
          });
        } else if (this.showInternalAgents) {
          this.chatService.keyCodeSubject.next({
            keyCode: keyCode,
            feature: "comment",
            searchKey: this.searchKey
          });
        }
      }, 1);
    } else if (!event.shiftKey && keyCode === 13) {
      event.preventDefault();
      if (this.chatMessage.nativeElement.innerHTML) {
        this.sendChat();
      }
    }
  }

  emojiSelected(event) {
    // this.pasteHtmlAtCaret(event.emoji.native);
    this.emoji = String.fromCodePoint(parseInt(`0x${event.emoji.unified}`));
    let emojiX = event.emoji.sheet[0] * -20 + "px";
    let emojiY = event.emoji.sheet[1] * -20 + "px";
    const emoji = this.renderer.createElement("img");
    this.renderer.setAttribute(
      emoji,
      "src",
      "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
    );
    this.renderer.setAttribute(emoji, "class", "emoji");
    this.renderer.setAttribute(emoji, "data-id", event.emoji.native);
    this.renderer.setAttribute(emoji, "alt", event.emoji.native);
    this.renderer.setStyle(emoji, "background-position", emojiX + " " + emojiY);
    this.pasteHtmlAtCaret(emoji);
    const el = this.chatMessage.nativeElement;
    let messagge = this.chatMessage.nativeElement.innerHTML;
    this.chatMessage.nativeElement.innerHTML = messagge
      .replace("Type a message", "")
      .replace("سوربو ايس", "");
  }

  outputCannedResponse(data: any) {
    this.showCannedList = false;
    if (data === "send") {
      this.sendChat();
    } else if (data !== "false") {
      this.chatMessage.nativeElement.innerText = "";
      this.caretPos = false;
      this.pasteHtmlAtCaret(data.output);
    }
  }

  commentAgent(data: any) {
    this.showInternalAgents = false;
    if (data === "send") {
      this.isInternalComment = false;
      this.sendChat();
    } else if (data !== "false") {
      this.chatMessage.nativeElement.innerText = "";
      this.caretPos = false;
      this.selectedInternalAgent = data.output;
      const agentSpan = this.renderer.createElement("input");
      // const text = this.renderer.createText(data.output.name);
      this.renderer.setAttribute(agentSpan, "class", "agent__selected");
      this.renderer.setAttribute(agentSpan, "value", data.output.name);
      this.renderer.setAttribute(agentSpan, "type", "button");
      this.renderer.setAttribute(agentSpan, "disabled", "disabled");
      const dummyspan = this.renderer.createElement("span");
      const blanktext = this.renderer.createText(":");
      this.renderer.appendChild(dummyspan, blanktext);
      this.pasteHtmlAtCaret(agentSpan);
      this.pasteHtmlAtCaret(dummyspan);
      this.isInternalComment = true;
    }
  }

  pasteHtmlAtCaret(html) {
    let elem = this.chatMessage.nativeElement;
    elem.focus();
    var sel, range;
    if (window.getSelection) {
      sel = window.getSelection();
      if (sel.getRangeAt && sel.rangeCount) {
        range = sel.getRangeAt(0);
        range.deleteContents();
        var el = document.createElement("div");
        if (typeof html === "object") {
          // if emoji
          el.innerHTML = html.outerHTML;
        } else el.innerHTML = html; // if canned response
        var frag = document.createDocumentFragment(),
          node,
          lastNode;

        // lastNode = frag.appendChild(node);
        while ((node = el.firstChild)) {
          lastNode = frag.appendChild(node);
        }
        if (this.caretPos) {
          this.caretPos.insertNode(lastNode);
          this.caretPos.collapse(false);
        } else {
          range.insertNode(lastNode);
          range.collapse(false);
        }
        if (lastNode) {
          // move to caret to actual pos
          range = range.cloneRange();
          range.setStartAfter(lastNode);
          range.collapse(true);
          sel.removeAllRanges();
          if (this.caretPos) sel.addRange(this.caretPos);
          else sel.addRange(range);
        }
      }
    }
  }

  closePopup() {
    this.chatTransferBool = false;
  }

  onFileChange(event, elem) {
    if (this.uploadDetails.isCompleted) {
      this.sendChat(elem.files[0]);
      elem.value = ""; // clear the file select
    } else {
      this.showNotifier = true;
      setTimeout(() => {
        this.showNotifier = false;
      }, 2000);
    }
  }

  findCaretPos() {
    var w3 = typeof window.getSelection != "undefined" && true;
    this.caretPos = 0;

    if (w3) {
      let range = window.getSelection().getRangeAt(0);
      let preCaretRange = range.cloneRange();
      preCaretRange.selectNodeContents(this.chatMessage.nativeElement);
      preCaretRange.setEnd(range.endContainer, range.endOffset);
      this.caretPos = preCaretRange;
      this.caretPos.collapse(false);
    }
  }

  convertBrToNextLine(html: string) {
    var regex = /<br\s*[\/]?>/gi;
    html = html.replace(regex, "\n");

    var d = document.createElement("div");
    d.innerHTML = html;
    return d.innerText;
    // return html;
  }

  convertImgToEmojisUnicode(message: string) {
    while (message.indexOf("<img") > -1) {
      let dataIdRegex = /<img.*?data-id="(.*?)"/;
      var dataId = dataIdRegex.exec(message)[1];
      message = message.replace(/\<img.*?\>/, dataId);
    }
    return message;
  }

  toggleEmojis(event) {
    event.stopPropagation();
    this.showEmojis = !this.showEmojis;
    this.showInternalAgents = false;
    this.showCannedList = false;
  }

  hideFeatures() {
    // this.showEmojis = false;
    this.showInternalAgents = false;
    this.showCannedList = false;
  }
  hideEmojis() {
    this.showEmojis = false;
  }

  checkPermissions() {
    if (
      this.userPermissions &&
      this.userPermissions.chatTransfer &&
      this.client.channelType !== "internal_comment" &&
      this.route !== "supervise"
    ) {
      this.allowTransfer = true;
    } else this.allowTransfer = false;
    if (
      this.userPermissions &&
      this.userPermissions.sendAttachments &&
      this.route !== "supervise" &&
      this.client.channelType !== "internal_comment"
    ) {
      this.allowAttachment = true;
    } else this.allowAttachment = false;
  }
}
