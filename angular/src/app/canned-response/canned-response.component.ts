import { Component, Output, EventEmitter } from "@angular/core";
import { ChatService } from "../shared/services/chat.service";
import { MissedChatService } from "../shared/services/missed-chat.service";
import { CannedResponseService } from "./canned-response.service";
import { CannedResponse } from "../shared/models/canned-response.model";
import { Subscription, Subject, of } from "rxjs";
import {
  debounceTime,
  distinctUntilChanged,
} from "rxjs/operators";

@Component({
  selector: "app-canned",
  templateUrl: "./canned-response.component.html",
  styleUrls: ["./canned-response.component.scss"]
})
export class CannedComponent {
  showPopup: Boolean = false;
  isShowCannedDetail: Boolean = false;
  popUpHeader: string;
  missedChatData: any;
  cannedResponseData: any = [];
  search: string;
  cannedResponse: CannedResponse = new CannedResponse();
  gettingLanguage: any;
  isLoading: boolean = true;
  pageSizeList: number[] = [10, 20, 50];
  selectedPageSize: number = 10;
  private handleCannedSearchkeyUpSubscription: Subscription;
  public handleCannedSearchkeyUp = new Subject<KeyboardEvent>();

  constructor(
    private _cannedResponseService: CannedResponseService,
    public chatService: ChatService,
    private _missedChatService: MissedChatService
  ) {

  }

  lastBackspaceVal = true;
  ngOnInit(): void {
    this.filterData(1);
    this.getLanguage();
    this.handleCannedSearch();
  }

  handleCannedSearch() {
    this.handleCannedSearchkeyUpSubscription = this.handleCannedSearchkeyUp
      .pipe(debounceTime(600), distinctUntilChanged())
      .subscribe(event => {
        console.log(event);
        console.log(event.target["value"]);
        if (
          (event.keyCode > 47 && event.keyCode < 91) ||
          event.keyCode === 13 ||
          event.keyCode === 8
        ) {
          if (!event.target["value"]) {
            if (this.lastBackspaceVal) {
              this.filterData(1);
              this.lastBackspaceVal = false;
            }
          } else {
            this.lastBackspaceVal = true;
            this.filterData();
          }
        }
      });
  }

  addResponse() {
    this.isShowCannedDetail = !this.isShowCannedDetail;
    this.cannedResponse = new CannedResponse();
    this.popUpHeader = this.gettingLanguage["canned_response"][
      "ui_elements_messages"
    ]["add_canned_response"];
  }

  selectedIndex: number;
  action;

  notifier: any = {};
  showNotifierMessage(res, type) {
    this.notifier.text = res.message;
    this.notifier.show = true;
    this.notifier.iserror = type;
    setTimeout(() => {
      this.notifier = {};
    }, 5000);
  }

  onUpdate(cannedResponse: CannedResponse) {
    this.isShowCannedDetail = !this.isShowCannedDetail;
    this.popUpHeader = this.gettingLanguage["canned_response"][
      "ui_elements_messages"
    ]["edit_canned_response"];
    this.cannedResponse = Object.assign({}, cannedResponse);
  }

  onDeleteConfirmation(cannedResponse) {
    this.cannedResponse = Object.assign({}, cannedResponse);
    this.showPopup = !this.showPopup;
  }

  onRefreshCannedResponseData(event) {
    this.filterData(this.currentPage);
  }

  onDelete() {
    this._cannedResponseService
      .delete("/v1/cannedResponses", this.cannedResponse.cannedResponseId)
      .subscribe(
        (res: any) => {
          if (res) {
            if (res["status"]) {
              this.showNotifierMessage(res, false);
              this.filterData(this.currentPage);
            } else {
              this.showNotifierMessage(res, true);
            }
          }
          this.showPopup = !this.showPopup;
        },
        error => {
          this.showNotifierMessage(error, false);
        }
      );
  }

  getLanguage() {
    this.chatService.getLanguage().subscribe(res => {
      this.gettingLanguage = res["data"]["interpretation"];
      this.chatService.updateLanguage(this.gettingLanguage);
      this.chatService.currentLanguage = res["data"]["language"];
    });
  }

  currentPage: number = 1;
  paginationArray: Array<Boolean>;
  showPagination: Boolean = false;
  createPagination(data) {
    this.showPagination = data["last_page"] > 1;
    if (this.showPagination) {
      this.currentPage = data["current_page"];
      this.paginationArray = new Array(data["last_page"]).fill(false);
      this.currentPage > 0
        ? (this.paginationArray[this.currentPage - 1] = true)
        : (this.paginationArray[0] = true);
    }
  }

  updatePage(page) {
    if (page === "prev") {
      this.filterData(this.currentPage - 1);
    } else if (page === "next") {
      this.filterData(this.currentPage + 1);
    } else {
      this.filterData(page);
    }
  }

  filterData(pageNum?) {
    if (!pageNum && !this.search) {
      return;
    }
    this.isLoading = true;
    this._cannedResponseService
      .getCannedResponses(pageNum, this.search)
      .subscribe(
        res => {
          if (res["status"]) {
            this.cannedResponseData = res["data"]["data"];
            const meta = {
              current_page: res["data"]["current_page"],
              from: res["data"]["from"],
              last_page: res["data"]["last_page"],
              per_page: res["data"]["per_page"],
              to: res["data"]["to"],
              total: res["data"]["total"]
            };
            this.createPagination(meta);
          } else {
            this.cannedResponseData = [];
            this.showNotifierMessage(res["message"], false);
          }
          this.isLoading = false;
        },
        err => {
          this.isLoading = false;
          this.showNotifierMessage(err, true);
        }
      );
  }

  ngOnDestroy(): void {
    this.handleCannedSearchkeyUpSubscription.unsubscribe();
  }
}
