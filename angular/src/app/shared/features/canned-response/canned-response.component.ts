import {
  Component,
  OnInit,
  Output,
  EventEmitter,
  Input,
  AfterViewInit,
  OnChanges,
  ViewChild
} from "@angular/core";
import { ChatService } from "../../services/chat.service";
import { PerfectScrollbarComponent } from "ngx-perfect-scrollbar";

export class CannedResponse {
  shortcut: string;
  responses: string[];
}

@Component({
  selector: "app-canned-response",
  templateUrl: "./canned-response.component.html",
  styleUrls: ["./canned-response.component.scss"]
})
export class CannedResponseComponent implements OnInit, OnChanges {
  @Input() showCannedList: boolean;
  cannedResponses: CannedResponse[] = [];
  shownResponses: CannedResponse[] = [];
  selectedIndex: number = 0;
  selectedResponseIndex: number = 0;
  showResponses: boolean = false;
  iterateListBool: boolean = true;
  clLength: number = -1;
  crLength: number = -1;
  isLoading: boolean = false;
  haveZeroRecords: boolean = false;
  searchKey: string = "";

  @Output() outputEvent: EventEmitter<any> = new EventEmitter();
  @ViewChild("listScroll") listScroll: PerfectScrollbarComponent;
  @ViewChild("responseScroll") responseScroll: PerfectScrollbarComponent;

  constructor(private chatService: ChatService) {}

  ngOnChanges() {
    if (!this.showCannedList) this.showResponses = false;
  }

  ngOnInit() {
    this.getCannedResponses();

    this.chatService.keyCodeObservale.subscribe((response: any) => {
      if (response.keyCode === 51 && response.searchKey.length < 1) {
        this.selectedIndex = 0;
        this.searchKey = "";
      }

      if (response.feature === "canned") {
        this.iterateList(response.keyCode);
        if (response.searchKey.length > 0) {
          if (response.searchKey !== this.searchKey) {
            this.searchResponses(response.searchKey);
            this.searchKey = response.searchKey;
            this.selectedIndex = 0;
          }
        } else {
          this.shownResponses = this.cannedResponses;
          this.clLength = this.cannedResponses.length;
        }
      }
    });
  }

  getCannedResponses() {
    this.isLoading = true;
    this.cannedResponses = [];
    this.chatService.getCannedResponses().subscribe((response: any) => {
      this.isLoading = false;
      if (response.data.length < 1) {
        this.haveZeroRecords = true;
        return false;
      } else this.haveZeroRecords = false;
      response.data.forEach(element => {
        let cr = new CannedResponse();
        cr.shortcut = Object.keys(element)[0];
        cr.responses = element[cr.shortcut];
        this.cannedResponses.push(cr);
        this.clLength = this.cannedResponses.length;
      });
      this.isLoading = false;
    });
    this.shownResponses = this.cannedResponses;
  }

  searchResponses(searchKey: string) {
    // if string is not null and not having just whitespaces
    // remove trailing spaces from the string
    if (/\S/.test(searchKey)) {
      searchKey = searchKey.toLowerCase().replace(/\s+$/g, "");
    }
    this.shownResponses = [];
    this.showResponses = false;
    this.iterateListBool = true;
    this.cannedResponses.forEach((response: CannedResponse) => {
      // console.log(searchKey);
      if (
        response.shortcut
          .toLowerCase()
          .trim()
          .indexOf(searchKey) > -1
      ) {
        this.shownResponses.push(response);
      }
    });
    this.clLength = this.shownResponses.length;
    if (this.clLength < 1) {
      this.showResponses = false;
      this.showCannedList = false;
    } else {
      this.showCannedList = true;
    }
  }

  iterateList(keyCode) {
    if (this.iterateListBool) {
      this.showResponses = false;
      if (keyCode === 38) {
        // up key
        this.selectedIndex !== 0
          ? this.selectedIndex--
          : (this.selectedIndex = this.clLength - 1);
      }
      if (keyCode === 40) {
        // down key
        this.selectedIndex === this.clLength - 1
          ? (this.selectedIndex = 0)
          : this.selectedIndex++;
      }
      if (keyCode === 39 || keyCode === 13) {
        // right or enter
        if (this.shownResponses[this.selectedIndex]) {
          this.showResponses = true;
          this.crLength = this.shownResponses[
            this.selectedIndex
          ].responses.length;
          this.iterateListBool = false;
          this.selectedResponseIndex = 0;
        } else {
          this.showCannedList = false;
          this.showResponses = false;
          this.outputEvent.emit("send");
        }
      }
      if (keyCode === 37) {
        // left
        // this.showCannedList = false;
      }

      if (this.listScroll)
        this.listScroll.directiveRef.scrollToElement(
          `[id="cl__${this.selectedIndex}"]`,
          -170
        );
    } else {
      if (keyCode === 38) {
        // up key
        if (this.crLength === 1) {
          this.showResponses = false;
          this.iterateListBool = true;
        }
        this.selectedResponseIndex !== 0
          ? this.selectedResponseIndex--
          : (this.selectedResponseIndex = this.crLength - 1);
      }
      if (keyCode === 40) {
        // down key
        if (this.crLength === 1) {
          this.showResponses = false;
          this.iterateListBool = true;
        }
        this.selectedResponseIndex === this.crLength - 1
          ? (this.selectedResponseIndex = 0)
          : this.selectedResponseIndex++;
      }
      if (keyCode === 37 || keyCode === 8) {
        // left
        this.showResponses = false;
        this.iterateListBool = true;
        this.selectedResponseIndex = 0;
      }
      if (keyCode === 13) {
        // right or enter
        this.showResponses = false;
        this.showCannedList = false;
        this.iterateListBool = true;
        this.outputEvent.emit({
          output: this.shownResponses[this.selectedIndex].responses[
            this.selectedResponseIndex
          ],
          event: "enter"
        });
      }
      if (this.responseScroll)
        this.responseScroll.directiveRef.scrollToElement(
          `[id="cr__${this.selectedIndex}"]`,
          -170
        );
    }

    if (keyCode === 27) {
      // esc or backspace
      this.showResponses = false;
      this.iterateListBool = true;
      this.outputEvent.emit("false");
    }
  }

  showResponse(index: number) {
    this.selectedIndex = index;
    this.selectedResponseIndex = 0;
    this.showResponses = true;
    this.crLength = this.shownResponses[this.selectedIndex].responses.length;
    this.iterateListBool = false;
  }

  clickResponse(index: number) {
    this.selectedResponseIndex = index;
    this.showResponses = false;
    this.iterateListBool = true;
    this.outputEvent.emit({
      output: this.shownResponses[this.selectedIndex].responses[
        this.selectedResponseIndex
      ],
      event: "click"
    });
  }
}
