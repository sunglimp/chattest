import { Component, EventEmitter, Input, OnInit, Output } from "@angular/core";
import { ChatService } from "../../services/chat.service";
import { DomSanitizer } from "@angular/platform-browser";
import { Subscription } from "rxjs";
import * as $ from "jquery";

@Component({
  selector: "app-file-view",
  templateUrl: "./file-view.component.html",
  styleUrls: ["./file-view.component.scss"]
})
export class FileViewComponent implements OnInit {
  fileURL: any;
  gettingLanguage: object;
  botChat: boolean = true;
  checkType: String;
  selectedFileView;
  selectedFileData;
  printButton: boolean = false;
  noPreViewAvailable: boolean = false;
  videoPreViewAvailable: boolean = false;
  audioPreViewAvailable: boolean = false;
  firefoxBrowser: boolean = false;
  supportedFileFormat = ["jpeg", "jpg", "png", "gif", "txt", "pdf"];
  videoFileFormat = ["mov", "webm", "mp4"];
  audioFileFormat = ["mp3", "ogg", "wav"];
  notSupportedFormat = [
    "flv",
    "3gp",
    "mkv",
    "m4v",
    "3gpp",
    "mpg",
    "m4v",
    "wmv",
    "mpegps",
    "avi",
    "mpeg4",
    "m4a",
    "qt",
    "ogx",
    "mpeg",
    "mpga",
    "oga",
    "ppt",
    "psd",
    "zip",
    "rar",
    "xls",
    "ppt",
    "pptx",
    "doc",
    "docx",
    "ogv"
  ];
  sub: Subscription;
  @Output() outputEvent: EventEmitter<boolean> = new EventEmitter();
  constructor(
    public chatService: ChatService,
    private sanitizer: DomSanitizer
  ) {}

  ngOnInit() {
    this.getLanguage();
    this.sub = this.chatService.viewFileObservable.subscribe((res: Object) => {
      console.log(res);
      if (Object.keys(res).length != 0) {
        let url = res[0].url;
        this.selectedFileData = res[0];
        this.botChat = res[0].botChat;
        this.fileURL = this.sanitizer.bypassSecurityTrustResourceUrl(
          url.toString() + "#toolbar=0"
        );
        if (this.supportedFileFormat.includes(res[0].extension)) {
          this.noPreViewAvailable = false;
          this.videoPreViewAvailable = false;
          this.audioPreViewAvailable = false;
          this.printButton = true;
          var extension = res[0].extension;
          if (this.botChat && extension == "pdf") {
            document.getElementById("file-content").innerHTML =
              '<embed src="' +
              url.toString() +
              '" type="application/' +
              extension +
              '"  id="pdf"  name="pdf" width="700" height="500" />';
          } else if (this.botChat && extension != "pdf") {
            document.getElementById("file-content").innerHTML =
              '<embed src="' +
              url.toString() +
              '" type="image/' +
              extension +
              '" width="700" height="500" />';
          } else {
            document.getElementById("file-content").innerHTML =
              '<embed src="' +
              url.toString() +
              '#toolbar=0" type=' +
              this.selectedFileData.type +
              ' width="700" height="500" />';
          }
        } else if (this.videoFileFormat.includes(res[0].extension)) {
          this.audioPreViewAvailable = false;
          this.noPreViewAvailable = false;
          this.videoPreViewAvailable = true;
          this.printButton = false;
        } else if (this.audioFileFormat.includes(res[0].extension)) {
          this.noPreViewAvailable = false;
          this.videoPreViewAvailable = false;
          this.audioPreViewAvailable = true;
          this.printButton = false;
        } else if (this.notSupportedFormat.includes(res[0].extension)) {
          this.videoPreViewAvailable = false;
          this.audioPreViewAvailable = false;
          this.noPreViewAvailable = true;
          this.printButton = false;
        }
      }
    });
    this.dectectBrowser();
  }

  output(bool: boolean) {
    document.getElementById("file-content").innerHTML = "";
    this.outputEvent.emit(bool);
  }
  downloadAttachment(index: number, hash: string) {
    const url = this.chatService.downloadAttachment(hash);
    if (hash && url) {
      var a = document.createElement("a");
      document.body.appendChild(a);
      a.href = url;
      a.download = "angular";
      a.click();
      document.body.removeChild(a);
      window.URL.revokeObjectURL(url);
    }
  }
  printContent() {
    if (this.botChat == true) {
      let printContents, popupWin;
      printContents = document.getElementById("file-content").innerHTML;
      popupWin = window.open(
        "",
        "_blank",
        "top=0,left=0,height=100%,width=auto"
      );
      popupWin.document.open();
      popupWin.document.write(`
      <html>
        <head>
          <title>Print tab</title>
          <style>
          //........Customized style.......
          </style>
        </head>
    <body onload="window.print();window.close()">${printContents}</body>
      </html>`);
      popupWin.document.close();
      location.reload();
    } else {
      const iframe = document.createElement("iframe");
      iframe.style.display = "none";
      iframe.src = this.selectedFileData.url;
      document.body.appendChild(iframe);
      iframe.contentDocument.title = "New title!";
      iframe.contentWindow.print();
      location.reload();
    }

    // let doc = <HTMLEmbedElement> document.getElementById('file-content');

    //Wait until PDF is ready to print
    // if (typeof doc === 'undefined') {
    //   setTimeout(function(){this.printContent();}, 1000);
    // } else {
    //   doc.print();
    // }

    /*  var pdfFrame = window.frames["pdf"];
      pdfFrame.print();*/

    //  const iframe = document.createElement('iframe');
    //  iframe.style.display = 'none';
    //  iframe.src =  this.selectedFileData.url
    //  document.body.appendChild(iframe);
    //  iframe.contentDocument.title = 'New title!';
    // if (typeof iframe.contentWindow.print === 'undefined') {
    //   setTimeout(function(){this.printContent();}, 1000);
    // } else {
    //   iframe.contentWindow.print();
    // }
    //
    //
    //
    // const url = 'blob:http://127.0.0.1:8000/1ab49803-817a-42e4-b2df-4db080508b31#toolbar=0' // e.g localhost:3000 + "/download?access_token=" + "sample access token";
    //  this.chatService.getBlobTypeContent(url).subscribe((response) => { // download file
    //     var blob = new Blob([response], {type: 'application/pdf'});
    //     const blobUrl = URL.createObjectURL(blob);
    //     const iframe = document.createElement('iframe');
    //     iframe.style.display = 'none';
    //     iframe.src = blobUrl;
    //     document.body.appendChild(iframe);
    //     iframe.contentWindow.print();
    //   });

    // fetch('https://surbo-s3bucket.s3.amazonaws.com/intents/responses/images/5d89bef7c348013edd633f2a/5d89bef7c348013edd633f2a-1569312223.189236-ValueFirstDataCollection.pdf')
    //   .then(
    //     function(response) {
    //       if (response.status !== 200) {
    //         console.log('Looks like there was a problem. Status Code: ' +
    //           response.status);
    //         return;
    //       }
    //
    //       // Examine the text in the response
    //       response.json().then(function(data) {
    //         console.log(data);
    //       });
    //     }
    //   )
    //   .catch(function(err) {
    //     console.log('Fetch Error :-S', err);
    //   });
  }
  dectectBrowser() {
    let browser = /chrom(e|ium)/.test(navigator.userAgent.toLowerCase());
    if (browser) {
      this.firefoxBrowser = false;
    } else {
      this.firefoxBrowser = true;
    }
  }
  downloadContent(index: number, hash: string) {
    var a = document.createElement("a");
    document.body.appendChild(a);
    a.href = hash["url"];
    a.download = "angular";
    a.click();
    document.body.removeChild(a);
  }
  getLanguage() {
    this.chatService.getLanguage().subscribe(res => {
      this.gettingLanguage = res["data"]["interpretation"];
    });
  }
}
