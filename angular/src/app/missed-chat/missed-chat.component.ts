import { Component, NgZone } from "@angular/core";
import { ChatService } from "../shared/services/chat.service";
import * as moment from "moment";
import { MissedChatService } from "../shared/services/missed-chat.service";
import { Subject } from "rxjs";

@Component({
  selector: "app-missed-chat",
  templateUrl: "./missed-chat.component.html",
  styleUrls: ["./missed-chat.component.scss"]
})
export class MissedChatComponent {
  showPopup: Boolean = false;
  missedChatData: any;
  gettingLanguage: any;
  isLoading: boolean = true;
  selectedDates: { startDate: moment.Moment; endDate: moment.Moment };
  days: number[] = [15, 30, 45];
  selectedDay: number = 0;
  startDate: string = "";
  endDate: string = "";
  constructor(
    public chatService: ChatService,
    private _missedChatService: MissedChatService,
    private _ngZone: NgZone
  ) {
    this.startDate = moment()
      .subtract(this.days[0], "days")
      .format("DD-MM-YYYY");
    this.endDate = moment().format("DD-MM-YYYY");
  }

  ngOnInit(): void {
    this.setCalenderDate();
    this.filterData(1);
    this.getLanguage();
  }

  selectedIndex: number;
  action;
  updateAction(action, index) {
    this.showPopup = true;
    this.selectedIndex = index;
    this.action = action;
  }

  notifier: any = {};
  showNotifierMessage(res, type) {
    this.notifier.text = res.message;
    this.notifier.show = true;
    this.notifier.iserror = type;
    setTimeout(() => {
      this.notifier = {};
    }, 5000);
  }

  updateStatus(action?, index?) {
    this.isLoading = true;
    this.showPopup = false;
    if (index !== undefined) {
      this.selectedIndex = index;
    }
    const data = {
      action: action ? action : this.action
    };

    this._missedChatService
      .updateMissedChatStatus(
        this.missedChatData[this.selectedIndex]["chat_channel_id"],
        data
      )
      .subscribe(
        res => {
          if (res["status"]) {
            this.missedChatData[this.selectedIndex]["status"] = 1;
            this.missedChatData[this.selectedIndex]["message"] = res["message"];
            this.showNotifierMessage(res, false);
          } else {
            if (data.action === 2) {
              this.missedChatData[this.selectedIndex]["status"] = 1;
              this.missedChatData[this.selectedIndex]["message"] =
                res["message"];
            }
            this.showNotifierMessage(res, true);
          }
          this.showPopup = false;
          this.isLoading = false;
        },
        err => {
          this.isLoading = false;
          this.showNotifierMessage(err, true);
        }
      );
  }

  changeDays(index) {
    this.selectedDay = index;
    this.startDate = moment()
      .subtract(this.days[index], "days")
      .format("DD-MM-YYYY");
    this.endDate = moment().format("DD-MM-YYYY");
    this.setCalenderDate();
    this.filterData(1);
  }

  getLanguage() {
    this.chatService.getLanguage().subscribe(res => {
      this.gettingLanguage = res["data"]["interpretation"];
      this.chatService.updateLanguage(this.gettingLanguage);
      this.chatService.currentLanguage = res["data"]["language"];
    });
  }

  clearCalender(event) {
    if (event.keyCode === 8) {
      this.selectedDates = null;
    }
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

  setCalenderDate() {
    this.selectedDates = {
      startDate: moment().subtract(this.days[this.selectedDay], "days"),
      endDate: moment()
    };
  }

  filterData(pageNum, fromSubmit?: Boolean) {
    this.isLoading = true;
    fromSubmit ? (this.selectedDay = 0) : null;
    if (this.selectedDates) {
      this.startDate = this.selectedDates.startDate
        ? moment(this.selectedDates.startDate.toDate()).format("DD-MM-YYYY")
        : moment()
            .subtract(this.days[this.selectedDay], "days")
            .format("DD-MM-YYYY");
      this.currentPage++;

      this.endDate = this.selectedDates.endDate
        ? moment(this.selectedDates.endDate.toDate()).format("DD-MM-YYYY")
        : moment().format("DD-MM-YYYY");
    }

    let status = "";
    if (this.selectedMember > 0) {
      status = "" + (this.selectedMember - 1);
    }

    const param =
      "/v1/chats/missed?start_date=" +
      this.startDate +
      "&end_date=" +
      this.endDate +
      "&page=" +
      pageNum +
      "&status=" +
      status;

    this._missedChatService.getMissedChats(param).subscribe(
      res => {
        if (res["status"]) {
          this.missedChatData = res["data"];
          this.createPagination(res["meta"]);
        }
        this.isLoading = false;
      },
      err => {
        this.isLoading = false;
        this.showNotifierMessage(err, true);
      }
    );
  }

  statusDropdown = false;
  selectedMember = 0;
  teamDropdown = [
    { key: "Select Status", value: -1 },
    { key: "Contact Customer", value: 0 },
    { key: "Customer Contacted", value: 1 },
    { key: "Chat Rejected", value: 2 }
  ];

  selectDropDown(event: any) {
    event.stopPropagation();
    this.statusDropdown = !this.statusDropdown;
  }

  onChangStatus(event, index) {
    this.selectedMember = index;
  }

  clientQuery: Array<any> = [];
  convertToClientQuery(data) {
    for (let i = 0; i < data.length; i++) {
      if (data[i].hasOwnProperty("message")) {
        if (
          data[i]["message"].hasOwnProperty("text") &&
          data[i]["message"]["text"]
        ) {
          const obj = {
            recipient: data[i]["recipient"],
            text: data[i]["message"]["text"]
          };
          this.clientQuery.push(obj);
        }
      }
    }
  }

  getClienQuery(index) {
    const agentId = window["USER"].id;
    const channelId = this.missedChatData[index].chat_channel_id;
    const clientId = this.missedChatData[index].client_id;
    this.clientQuery = [];
    this._missedChatService
      .getClientQuery(
        agentId,
        clientId,
        channelId,
        this.startDate,
        this.endDate
      )
      .subscribe(
        res => {
          if (res["status"]) {
            this.convertToClientQuery(res["data"]);
          }
        },
        err => {
          console.log(err);
        }
      );
  }
}
