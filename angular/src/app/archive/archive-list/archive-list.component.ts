import {
  Component,
  HostListener,
  OnInit,
  ViewChild,
  ElementRef,
  AfterViewInit,
  NgZone,
  OnChanges
} from "@angular/core";
import * as moment from "moment";
import { ArchiveService } from "src/app/shared/services/archive.service";
import {
  Client,
  Organization,
  Permissions
} from "src/app/shared/models/client.model";
import { ChatService } from "src/app/shared/services/chat.service";
import { PerfectScrollbarComponent } from "ngx-perfect-scrollbar";
import { TicketService } from "src/app/shared/services/ticket.service";
import { debug } from "util";
import { ActivatedRoute, Router } from "@angular/router";
import { WebsocketService } from "../../shared/services/websocket.service";
import * as XLSX from "xlsx";

@Component({
  selector: "app-archive-list",
  templateUrl: "./archive-list.component.html",
  styleUrls: [
    "./../../chats/chat-list/chat-list.component.scss",
    "./archive-list.component.scss"
  ]
})
export class ArchiveListComponent implements OnInit, AfterViewInit {
  isSuperadmin: boolean = this.wsService.isSuperadmin;
  gettingLanguage: object;
  gettingLanguageArchive: object;
  gettingLanguageClassified: object;
  isLoading: boolean = true;
  showTagsDropdown: boolean = false;
  toggleDropdown: boolean = false;
  selectedTagList: string[] = [];
  selectedDates: { startDate: moment.Moment; endDate: moment.Moment };
  searchFilter: string = "";
  days: number[] = [15, 30, 45];
  tagList: any[] = [];
  Classification: string[] = ["Business", "Services"];
  selectedDay: number = 0;
  clients: Client[] = [];
  haveZeroRecords: boolean = false;
  colorCodes: string[] = this.chatService.colorCodes;
  startDate: string = "";
  endDate: string = "";
  pageCounter: number = 1;
  isLimitReached: boolean = false;
  searchTypes: string[];
  selectedSearchType: number = 0;
  gotResponse: boolean = false;
  clickedChat: number = -1;
  showDropdownOrg: boolean = false;
  route: string = this.chatService.route;
  selectedItem: string = "Business";
  teamDropdown: any[] = [];
  selectedMember: any = "";
  statusDropdown: boolean = false;
  organizations: Organization[] = [];
  shownOrganizations: Organization[] = [];
  isSuperAdmin: boolean = this.chatService.isSuperAdmin;
  selectedClassification: string = "Business";
  selectedOrg: Organization = {
    id: null,
    name: "--Select Organization--"
  };
  showDropdownBool: boolean = true;
  paramURL: any;
  sessionStartDate: any;
  sessionEndDate: any;
  placeholder: any;
  startTime: any = "";
  endTime: any = "";
  userInfo;
  @ViewChild("scroll") scroll: PerfectScrollbarComponent;
  @ViewChild("input") input;

  constructor(
    private _router: Router,
    private eRef: ElementRef,
    public archiveService: ArchiveService,
    public chatService: ChatService,
    private ngZone: NgZone,
    private ticketService: TicketService,
    private elRef: ElementRef,
    private router: ActivatedRoute,
    private wsService: WebsocketService
  ) {}

  @HostListener("document:click", ["$event"])
  clickout(event) {
    if (this.eRef.nativeElement.contains(event.target)) {
      !event.target.classList.contains("clickable")
        ? (this.showTagsDropdown = false)
        : null;
    } else {
      this.showTagsDropdown = false;
    }
  }

  ngOnInit() {
    this.getLanguage();
    if (
      sessionStorage.getItem("startDate") &&
      sessionStorage.getItem("endDate") &&
      sessionStorage.getItem("startTime")
    ) {
      let startDate = sessionStorage.getItem("startDate");
      let startdatearray = startDate.split("-");
      let newstartDate =
        startdatearray[0] + "-" + startdatearray[1] + "-" + startdatearray[2];
      let endDate = sessionStorage.getItem("endDate");
      let enddatearray = endDate.split("-");
      let newendDate =
        enddatearray[0] + "-" + enddatearray[1] + "-" + enddatearray[2];
      this.sessionStartDate = newstartDate;
      this.sessionEndDate = newendDate;
      this.startTime = sessionStorage.getItem("startTime");
      this.endTime = sessionStorage.getItem("endTime");
      this.startDate = this.sessionStartDate;
      this.endDate = this.sessionEndDate;
      this.selectedDates = {
        startDate: this.sessionStartDate,
        endDate: this.sessionEndDate
      };
      this.archiveFinder();
    } else {
      this.placeholder = "dd/mm/yyyy - dd/mm/yyyy";
      this.startDate = moment()
        .subtract(this.days[this.selectedDay], "days")
        .format("DD-MM-YYYY");
      this.endDate = moment().format("DD-MM-YYYY");
      this.startTime = "";
      this.endTime = "";
      this.archiveFinder();
    }
    this.userInfo = JSON.parse(localStorage.getItem("currentUserInfo"));
    // console.log(this.userInfo);

    // setTimeout(()=> {
    //
    //   if(this.sessionStartDate && this.sessionEndDate){
    //     this.startDate = this.sessionStartDate;
    //     this.endDate = this.sessionEndDate;
    //     this.selectedDates = {
    //       startDate: this.sessionStartDate,
    //       endDate: this.sessionEndDate
    //     }
    //     this.archiveFinder();
    //   }
    //   else{
    //     this.placeholder = 'dd/mm/yyyy - dd/mm/yyyy';
    //     this.startDate = moment().subtract(this.days[this.selectedDay], 'days').format('DD-MM-YYYY');
    //     this.endDate = moment().format('DD-MM-YYYY');
    //     this.startTime = '';
    //     this.endTime = '';
    //     this.archiveFinder();
    //   }
    // },1000)

    this.getChatQueueCount();
    this.wsService.informQueueCountPrivateChannel();

    // update chat queue count from socket
    this.wsService.chatQueueObservable.subscribe(res => {
      if (res["type"] === "queueCount") {
        this.ngZone.run(() => {
          this.chatService.chatQueueCount = res["queueCount"];
        });
      }
    });
  }

  ngAfterViewInit() {
    this.scroll.directiveRef.scrollToTop(1, 100);
  }
  archiveFinder() {
    if (this.route === "banned-users") {
      this.searchTypes = ["Text", "Agent"];
      this.archiveService.getOrganization().subscribe((response: any) => {
        response.data.map(org => {
          let organization = new Organization();
          organization.id = org.id;
          organization.name = org.company_name;
          this.organizations.push(organization);
        });
        this.shownOrganizations = this.organizations;
      });
    } else {
      this.searchTypes = ["Text", "Tags", "Comment"];
    }
    this.chatService.closeObservable.subscribe((response: any) => {
      this.toggleDropdown = false;
      this.showDropdownOrg = false;
      this.statusDropdown = false;
    });
    if (this.route === "ticket" || this.route === "banned-users") {
      this.ticketService.archiveRemoveObservable.subscribe((input: string) => {
        if (input === "warningOutput") {
          this.clients.splice(this.clickedChat, 1);
          let length = this.clients.length;
          if (length === 0) {
            this.onSelect(-1);
            //this.haveZeroRecords = true;
          } else if (length === this.clickedChat) {
            this.onSelect(this.clickedChat - 1);
          } else {
            this.onSelect(this.clickedChat);
          }
        }
      });
    }

    this.getArchivedClients(1);
    this.getMembersData();
    setTimeout(() => {
      this.perfectScrollFlag = true;
    }, 1000);
  }

  perfectScrollFlag: boolean = false;
  keydownSearch(event) {
    // if (event.keyCode === 51) {
    //   event.preventDefault();
    // }
    if (event.keyCode === 13) {
      this.filterData();
    }
  }

  showDropdown(event, type) {
    event.stopPropagation();
    let reportee;
    this.toggleDropdown = !this.toggleDropdown;
    if (type === "Tags") {
      this.archiveService.getTags(reportee).subscribe(data => {
        this.tagList = data["data"];
      });
    }
  }

  filterData() {
    this.startTime = "";
    this.endTime = "";
    this.clients = [];
    if (this.selectedDates.startDate) {
      if (this.selectedDates.startDate.toString().length > 10) {
        this.startDate = this.selectedDates.startDate.format("DD-MM-YYYY");
        this.endDate = this.selectedDates.endDate.format("DD-MM-YYYY");
      } else {
        this.startDate = this.selectedDates.startDate.toString();
        this.endDate = this.selectedDates.endDate.toString();
      }

      this.selectedDay = -1;
    } else {
      this.startDate = moment()
        .subtract(this.days[this.selectedDay], "days")
        .format("DD-MM-YYYY");
      this.endDate = moment().format("DD-MM-YYYY");
    }
    this.pageCounter = 0;
    this.isLimitReached = false;
    this.clickedChat = -1;
    if (this.haveZeroRecords) this.getArchivedClients(1);
  }

  changeDays(index: number) {
    this.selectedDates = null;
    if (this.selectedDay !== index) {
      this.ngZone.run(() => {
        this.selectedDay = index;
        this.clients = [];
        this.pageCounter = 0;
        this.isLimitReached = false;
      });
      this.startDate = moment()
        .subtract(this.days[index], "days")
        .format("DD-MM-YYYY");
      this.endDate = moment().format("DD-MM-YYYY");
      if (this.haveZeroRecords) this.getArchivedClients(1);
    }
  }

  changeDropdownOrg(index: number) {
    this.clients = [];
    this.selectedOrg = this.shownOrganizations[index];
    this.getArchivedClients(1);
  }

  showExcelDownload: boolean = false;
  changeSearchType(index: number) {
    this.selectedSearchType = index;
    // this.updateExcelFlag(false);
  }

  onScroll(event) {
    event.preventDefault();
    if (!this.perfectScrollFlag) {
      return;
    }

    if (!this.isLimitReached && this.gotResponse && this.perfectScrollFlag) {
      this.getArchivedClients(++this.pageCounter);
      this.gotResponse = false;
    }
  }

  onSelect(index: number) {
    if (index === -1) {
      let client = new Client();
      client.isClosed = true;
      this.chatService.chatSubject.next(client);
    } else {
      this.clickedChat = index;
      this.clients[index].colorCode = this.colorCodes[
        this.clients[index].clientId % 26
      ];
      this.chatService.chatSubject.next(this.clients[index]);
    }

    if (this._router.url === "/archive") {
      console.log(this.clients);
      console.log(this.archiveService.channelId);
      console.log(this.chatService);
      this.chatService
        .getClientInfo(
          this.clients[index].clientId,
          this.archiveService.channelId
        )
        .subscribe(res => {
          console.log(res);
          if (res["status"]) {
            this.clients[index].clientInfo = res["data"];
          }
        });
    }
  }
  // to change the clients and their data as per filter
  getArchivedClients(page: number) {
    console.log(page);
    let ticketType =
      this.selectedItem === "Business" ? "BUSINESS TICKETS" : "SERVICE TICKETS";
    let selectedMemberKey =
      typeof this.selectedMember === "object" ? this.selectedMember.key : "";
    if (this.route === "banned-users") {
      let orgId = this.isSuperAdmin
        ? this.selectedOrg.id
        : this.chatService.organizationId;
      this.archiveService
        .getBannedUsers(
          orgId,
          this.startDate,
          this.endDate,
          this.selectedSearchType,
          this.searchFilter
        )
        .subscribe((response: any) => {
          if (response.status) {
            this.convertResponse(response, page);
          } else {
            this.ngZone.run(() => {
              this.isLoading = false;
              this.haveZeroRecords = true;
            });
          }
        });
    } else {
      let searchField;
      if (this.searchTypes[this.selectedSearchType] === "Tags") {
        searchField = [...this.selectedTagList];
      } else {
        searchField = this.searchFilter;
      }

      this.archiveService
        .getArchivedClients(
          this.startDate,
          this.endDate,
          page,
          searchField,
          this.selectedSearchType + 1,
          ticketType,
          selectedMemberKey,
          this.startTime,
          this.endTime
        )
        .subscribe(
          (response: any) => {
            if (response.status) {
              this.convertResponse(response, page);
              sessionStorage.clear();
              this.updateExcelFlag(true);
            } else {
              this.ngZone.run(() => {
                this.isLoading = false;
                this.haveZeroRecords = true;
                this.updateExcelFlag(false);
              });
            }
          },
          error => {
            // console.log(error)
          },
          () => {
            this.checkChatTagged();
            this.updateExcelFlag(false);
          }
        );
    }
  }

  updateExcelFlag(flag) {
    this.ngZone.run(() => {
      setTimeout(() => {
        this.showExcelDownload = flag;
      }, 0);
    });
  }

  checkChatTagged() {
    this.archiveService.chatTaggedObservable.subscribe((bool: any) => {
      if (bool !== -1) this.clients[this.clickedChat].isTagged = bool;
    });
  }

  clearCalender(event) {
    if (event.keyCode === 8) {
      this.selectedDates = null;
    }
  }

  chatClassification(event, newValue) {
    if (this.selectedItem !== newValue) {
      this.selectedItem = newValue;
      this.clients = [];
      this.getArchivedClients(1);
      this.selectedClassification = newValue;
      this.archiveService.classificationChats(this.selectedItem);
      this.chatService.chatTicketTypeSubject.next(newValue);
    }
  }
  getMembersData() {
    this.archiveService.getReportData().subscribe((response: any) => {
      if (response.status !== 201) {
        let keys = Object.keys(response.body);
        this.teamDropdown.push({
          key: "",
          value: "Team"
        });
        keys.forEach(key => {
          this.teamDropdown.push({
            key: key,
            value: response.body[key]
          });
        });
        this.selectedMember = this.teamDropdown[0];
      } else this.showDropdownBool = false;
    });
  }

  onChangReport(event: any, item): void {
    this.selectedMember = item;
  }

  selectDropDown(event: any) {
    event.stopPropagation();
    this.statusDropdown = !this.statusDropdown;
  }

  convertResponse(response: any, page: number) {
    this.isLoading = false;
    if (response.data.length < 1 && this.clients.length < 1) {
      this.ngZone.run(() => {
        this.haveZeroRecords = true;
        return false;
      });
    } else if (response.data.length < 15) {
      this.ngZone.run(() => {
        this.isLimitReached = true;
        this.haveZeroRecords = false;
      });
    } else {
      this.haveZeroRecords = false;
    }
    response.data.forEach(data => {
      let client = new Client();
      client.clientDisplayNumber = data.client_display_name;
      client.chatDate = data.date;
      client.clientId = data.id;
      client.clientInfo = data.client_raw_info;
      client.isTagged = data.is_tagged;
      client.channelId = data.channel_id;
      client.sourceType = data.source_type;
      this.wsService.seperateClientUsernameAndNumber(
        client,
        client.clientDisplayNumber
      );
      this.ngZone.run(() => {
        this.clients.push(client);
      });
    });
    this.gotResponse = true;
    if (!this.haveZeroRecords && page === 1) {
      this.ngZone.run(() => {
        this.onSelect(0);
      });
    }
  }

  searchOrg(key: any) {
    this.shownOrganizations = this.organizations.filter(org => {
      if (org.name.toLowerCase().indexOf(key.value.toLowerCase()) > -1) {
        return org;
      }
    });
  }

  toggleDropdownOrg(event: any) {
    event.stopPropagation();
    this.showDropdownOrg = !this.showDropdownOrg;
    setTimeout(() => {
      this.input.nativeElement.focus();
    }, 10);
  }

  getLanguage() {
    this.chatService.getLanguage().subscribe(res => {
      this.gettingLanguage = res["data"];
      this.gettingLanguageArchive = res["data"]["interpretation"];
      this.gettingLanguageClassified =
        res["data"]["interpretation"]["classified"]["ui_elements_messages"];
      this.chatService.updateLanguage(res);
      this.chatService.currentLanguage = res["data"]["language"];
    });
  }
  handleTagsSelection(event: any) {
    event.stopPropagation();
    event.target.id === "select-tag"
      ? (this.showTagsDropdown = !this.showTagsDropdown)
      : null;
  }

  updateTagArray(event, tagId) {
    const index = this.selectedTagList.indexOf(tagId);
    if (event.target["checked"]) {
      if (index < 0) {
        this.selectedTagList.push(tagId);
      }
    } else {
      if (index > -1) {
        this.selectedTagList.splice(index, 1);
      }
    }
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

  chatExcelDownload() {
    const reportee = this.selectedMember.key;
    this.archiveService
      .downloadArchivedExcelChats(
        this.startDate,
        this.endDate,
        this.chatService.agentId,
        reportee
      )
      .subscribe(
        res => {
          // console.log(res);
          if (res["body"]["status"]) {
            this.showNotifierMessage(res["body"], false);
          } else {
            this.showNotifierMessage(res["body"], true);
          }

          // var ws = XLSX.utils.json_to_sheet(res["body"]["reports"]);
          // /* add to workbook */
          // var wb = XLSX.utils.book_new();
          // XLSX.utils.book_append_sheet(wb, ws, "Chat List");

          // XLSX.writeFile(wb, res["body"]["file_name"] + ".xlsx");
        },
        err => {
          this.showNotifierMessage(
            {
              message: "Something went wrong"
            },
            true
          );
        }
      );
  }

  chatTagDownload() {
    const reportee = this.selectedMember.key;
    this.archiveService
      .downloadTagedArchivedChats(
        this.startDate,
        this.endDate,
        2,
        this.selectedTagList,
        this.chatService.agentId,
        reportee
      )
      .subscribe(res => {
        var ws = XLSX.utils.json_to_sheet(res["body"]["reports"]);
        /* add to workbook */
        var wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Chat List");

        XLSX.writeFile(wb, res["body"]["file_name"] + ".xlsx");
      });
  }

  getChatQueueCount() {
    this.chatService.getChatQueueCount().subscribe(res => {
      if (res["status"]) {
        this.chatService.chatQueueCount = res["data"].count;
      } else {
        this.chatService.chatQueueCount = null;
      }
    });
  }
}
