import {
  Component,
  OnInit,
  ViewChild,
  ElementRef,
  AfterViewInit,
  Output,
  EventEmitter,
  Input,
  NgZone
} from "@angular/core";
import { ChatService } from "../../services/chat.service";
import { PerfectScrollbarComponent } from "ngx-perfect-scrollbar";

@Component({
  selector: "app-email",
  templateUrl: "./email.component.html",
  styleUrls: ["./email.component.scss"]
})
export class EmailComponent implements OnInit, AfterViewInit {
  emailModel;
  emailSubject: string = "";
  emails: string[] = [];
  emailsCc: string[] = [];
  emailsBcc: string[] = [];
  filesSelected: any[] = [];
  showCc: boolean = false;
  showBcc: boolean = false;
  toEmailSize: number = 10;
  ccEmailSize: number = 10;
  bccEmailSize: number = 10;
  isInvalidEmail: boolean = false;
  notifierText: string = "Mail Sent Successfully!";
  notifierHTML: string = "<i class='fas fa-check'></i>";
  notify: string = "success";
  showNotifier: boolean = false;
  hideEmail: boolean = false;
  fileList: any[] = [];
  totalSize: number;
  inputSize: number;
  sendDisabled: boolean = false;

  quillEditor = {
    toolbar: [
      ["bold", "italic", "underline", "strike", "image"],
      // ['blockquote', 'code-block'],
      [{ header: 1 }, { header: 2 }],
      [{ list: "ordered" }, { list: "bullet" }],
      // [{ 'script': 'sub' }, { 'script': 'super' }],
      // [{ 'indent': '-1' }, { 'indent': '+1' }],
      [{ direction: "rtl" }],
      // [{ 'size': ['small', false, 'large', 'huge'] }],
      [{ header: [1, 2, 3, 4, 5, 6, false] }],
      [{ color: [] }, { background: [] }],
      [{ font: [] }],
      [{ align: [] }]
    ]
  };
  gettingLanguage: object;
  @ViewChild("toEmail") toEmail: ElementRef;
  @ViewChild("ccEmail") ccEmail: ElementRef;
  @ViewChild("bccEmail") bccEmail: ElementRef;
  @ViewChild("toScroll") toScroll: PerfectScrollbarComponent;
  @ViewChild("ccScroll") ccScroll: PerfectScrollbarComponent;
  @ViewChild("bccScroll") bccScroll: PerfectScrollbarComponent;
  @ViewChild("scroll") scroll: PerfectScrollbarComponent;
  @ViewChild("attachment") attachment: ElementRef;
  @Output() emailEmitter: EventEmitter<any> = new EventEmitter();
  @Input("channelId") channelId: number;
  @Input("language") language: object;
  constructor(public chatService: ChatService) {}
  ngOnInit() {
    this.hideEmail = false;
    this.gettingLanguage = this.language["data"]["interpretation"];
  }

  ngAfterViewInit() {
    let ps = document.querySelectorAll(".ps__thumb-y");
    ps.forEach(psEl => {
      psEl.setAttribute("tabindex", "-1");
    });
    document
      .querySelectorAll(".ql-toolbar")[0]
      .addEventListener("mousedown", function(event) {
        event.preventDefault();
        event.stopPropagation();
      });
  }

  sendEmail() {
    // console.log(this.emailSubject.length)
    if (this.emails.length < 1) {
      // this.notifyUser("warning", `Please enter a receipient!`);
      this.notifyUser(
        "warning",
        `${
          this.gettingLanguage["chat"]["validation_messages"][
            "enter_receipient"
          ]
            ? this.gettingLanguage["chat"]["validation_messages"][
                "enter_receipient"
              ]
            : `Please enter a receipient!`
        }`
      );
      return false;
    }
    let allEmails = this.emails.concat(this.emailsCc).concat(this.emailsBcc);
    let invalidEmail = allEmails.filter(email => {
      return this.checkEmailValidity(email) === false;
    });

    if (invalidEmail.length > 0) {
      this.notifyUser(
        "warning",
        `${
          this.gettingLanguage["chat"]["validation_messages"]["email_invalid"]
            ? this.gettingLanguage["chat"]["validation_messages"][
                "email_invalid"
              ]
            : `Email ${invalidEmail[0]} is invalid`
        }`
      );
    } else if (this.emailSubject.length < 1) {
      this.notifyUser(
        "warning",
        `${
          this.gettingLanguage["chat"]["validation_messages"][
            "enter_email_subject"
          ]
            ? this.gettingLanguage["chat"]["validation_messages"][
                "enter_email_subject"
              ]
            : `Please enter email subject`
        }`
      );
      return false;
    } else {
      this.sendDisabled = true;
      this.chatService
        .sendEmail(
          {
            recipient: {
              to: this.emails,
              cc: this.emailsCc,
              bcc: this.emailsBcc
            },
            subject: this.emailSubject,
            body: this.emailModel,
            chatChannelId: this.channelId
          },
          this.fileList
        )
        .subscribe(
          (response: any) => {
            // console.log(response)
            if (response.status) {
              //this.notifyUser("success", `Email Sent!`);
              this.notifyUser(
                "success",
                `${
                  this.gettingLanguage["chat"]["success_messages"]["email_send"]
                    ? this.gettingLanguage["chat"]["success_messages"][
                        "email_send"
                      ]
                    : `Email Sent!`
                }`
              );
              this.hideEmail = true;
              this.sendDisabled = false;
              setTimeout(() => {
                this.emailEmitter.emit("false");
              }, 2500);
            }
          },
          error => {
            console.log(error);
            if (error.error.status === false) {
              /*this.notifyUser("warning", error.error.errors[0]);*/
              this.notifyUser(
                "warning",
                `${
                  this.gettingLanguage["chat"]["validation_messages"][
                    "email_body_required"
                  ]
                    ? this.gettingLanguage["chat"]["validation_messages"][
                        "email_body_required"
                      ]
                    : error.error.errors[0]
                }`
              );

              this.sendDisabled = false;
            } else if (error.status === 413) {
              // this.notifyUser("warning", "File size exceeded!");
              this.notifyUser(
                "warning",
                `${
                  this.gettingLanguage["chat"]["validation_messages"][
                    "file_size_exceeded"
                  ]
                    ? this.gettingLanguage["chat"]["validation_messages"][
                        "file_size_exceeded"
                      ]
                    : `File size exceeded!`
                }`
              );

              this.sendDisabled = false;
            }
          }
        );
    }
  }

  showCC($event, showEl: string) {
    $event.stopPropagation();
    if (showEl === "cc") {
      this.showCc = true;
    } else {
      this.showBcc = true;
    }
  }

  emailsClicked(field: string) {
    setTimeout(() => {
      if (field === "to") {
        this.toEmail.nativeElement.focus();
        this.toScroll.directiveRef.scrollToBottom(-100000, 500);
      } else if (field === "cc") {
        this.ccEmail.nativeElement.focus();
        this.ccScroll.directiveRef.scrollToBottom(-100000, 500);
      } else {
        this.bccEmail.nativeElement.focus();
        this.bccScroll.directiveRef.scrollToBottom(-100000, 500);
      }
    }, 1);
  }

  emailBlurred(field: string) {
    this.toEmailSize = 10;
    this.ccEmailSize = 10;
    this.bccEmailSize = 10;
    if (field === "to") {
      if (this.toEmail.nativeElement.value !== "") {
        this.emails = this.emails.concat(
          this.toEmail.nativeElement.value
            .split(" ")
            .join(",")
            .split(";")
            .join(",")
            .split(",")
        );
        this.emails = this.emails.filter(email => email !== "");
        this.toEmail.nativeElement.value = "";
        this.toScroll.directiveRef.scrollToBottom(-100000, 500);
      }
    } else if (field === "cc") {
      if (this.ccEmail.nativeElement.value !== "") {
        this.emailsCc = this.emailsCc.concat(
          this.ccEmail.nativeElement.value
            .split(" ")
            .join(",")
            .split(";")
            .join(",")
            .split(",")
        );
        this.emailsCc = this.emailsCc.filter(email => email !== "");
        this.ccEmail.nativeElement.value = "";
        this.ccScroll.directiveRef.scrollToBottom(-100000, 500);
      }
    } else {
      if (this.bccEmail.nativeElement.value !== "") {
        this.emailsBcc = this.emailsBcc.concat(
          this.bccEmail.nativeElement.value
            .split(" ")
            .join(",")
            .split(";")
            .join(",")
            .split(",")
        );
        this.emailsBcc = this.emailsBcc.filter(email => email !== "");
        this.bccEmail.nativeElement.value = "";
        this.bccScroll.directiveRef.scrollToBottom(-100000, 500);
      }
    }
  }

  deleteEmail(field: string, index: number) {
    // console.log(field, index)
    if (field === "to") {
      this.emails.splice(index, 1);
    } else if (field === "cc") {
      this.emailsCc.splice(index, 1);
    } else this.emailsBcc.splice(index, 1);
  }

  closePopup() {
    this.emailEmitter.emit("false");
  }

  checkEmailValidity(email: string) {
    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
  }

  removeAttachment(index: number) {
    // console.log(index);
    this.filesSelected.splice(index, 1);
    this.fileList.splice(index, 1);
    this.sendDisabled = false;
  }

  onFileChange(event, elem) {
    var files = elem.files;
    for (let key of Object.keys(elem.files)) {
      if (Math.round(files[key].size / 1048576) > 15) {
        this.notifyUser("warning", "File size exceeded!");
        this.sendDisabled = false;
        return false;
      }
      this.fileList.push(files[key]);
      let size;
      if (files[key].size / 1000000 < 1)
        size = `${(files[key].size / 1000).toFixed(2)}kb`;
      else size = `${(files[key].size / 1000000).toFixed(2)}mb`;

      if (files[key].type.indexOf("image") > -1) {
        this.filesSelected.push({
          name: files[key].name,
          type: "image",
          size: size
        });
      } else if (files[key].type.indexOf("zip") > -1) {
        this.filesSelected.push({
          name: files[key].name,
          type: "zip",
          size: size
        });
      } else if (files[key].type.indexOf("video") > -1) {
        this.filesSelected.push({
          name: files[key].name,
          type: "video",
          size: size
        });
      } else if (files[key].type.indexOf("sheet") > -1) {
        this.filesSelected.push({
          name: files[key].name,
          type: "excel",
          size: size
        });
      } else if (files[key].type.indexOf("pdf") > -1) {
        this.filesSelected.push({
          name: files[key].name,
          type: "pdf",
          size: size
        });
      } else {
        this.filesSelected.push({
          name: files[key].name,
          type: "file",
          size: size
        });
      }
    }
    this.scroll.directiveRef.scrollToTop(-100000, 500);
    // elem.value = ''; // clear the file select
  }

  toEmailKeydown(field: string, event) {
    if (field === "to" && this.toEmail.nativeElement.value.length > 10) {
      this.toEmailSize = this.toEmail.nativeElement.value.length;
    }
    if (field === "cc" && this.ccEmail.nativeElement.value.length > 10) {
      this.ccEmailSize = this.ccEmail.nativeElement.value.length;
    }
    if (field === "bcc" && this.bccEmail.nativeElement.value.length > 10) {
      this.bccEmailSize = this.bccEmail.nativeElement.value.length;
    }
    if (event.keyCode === 8) {
      switch (field) {
        case "to": {
          if (this.toEmail.nativeElement.value.length < 1) {
            if (this.emails.length > 0) this.emails.pop();
          }
          break;
        }
        case "cc": {
          if (this.ccEmail.nativeElement.value.length < 1) {
            if (this.emailsCc.length > 0) this.emailsCc.pop();
          }
          break;
        }
        case "bcc": {
          if (this.bccEmail.nativeElement.value.length < 1) {
            if (this.emailsBcc.length > 0) this.emailsBcc.pop();
          }
          break;
        }
      }
    }
    if (
      event.keyCode === 32 ||
      event.keyCode === 186 ||
      event.keyCode === 188
    ) {
      event.preventDefault();
      this.emailBlurred(field);
    }
  }

  notifyUser(type: string, text: string) {
    this.notify = type;
    this.notifierText = text;
    if (type === "warning") {
      this.notifierHTML = "<i class='fas fa-times'></i>";
    } else {
      this.notifierHTML = "<i class='fas fa-check'></i>";
    }
    setTimeout(() => {
      this.showNotifier = true;
    }, 1);
    setTimeout(() => {
      this.showNotifier = false;
    }, 2500);
  }

  emailPasted(type: string, obj) {
    // console.log(obj)
    setTimeout(() => {
      this.toEmailSize = this.toEmail.nativeElement.value.length;
    }, 10);
  }

  emailEditorKeyDown(event) {
    // event.preventDefault();
    // event.stopPropagation();
    setTimeout(() => {
      this.scroll.directiveRef.scrollToBottom(-100000, 500);
    }, 50);
  }
}
