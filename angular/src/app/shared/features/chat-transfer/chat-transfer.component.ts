import {
  Component,
  OnInit,
  Output,
  EventEmitter,
  Input,
  OnDestroy
} from "@angular/core";
import { ChatService } from "../../services/chat.service";
import { Group, Agent, Client, Chat } from "../../models/client.model";
import { Subscription } from "rxjs";

@Component({
  selector: "app-chat-transfer",
  templateUrl: "./chat-transfer.component.html",
  styleUrls: ["./chat-transfer.component.scss"]
})
export class ChatTransferComponent implements OnInit, OnDestroy {
  gettingLanguage: object;
  searchKey: string = "";
  externalGroups: Group[] = [];
  internalAgents: Agent[] = [];
  groupId: number = -1;
  agentId: number = -1;
  lastSelectedAgentIndex: number = -1;
  lastSelectedGroupIndex: number = -1;
  showInternalAgents = true;
  selectedAgent: Agent;
  selectedGroup: Group;
  isLoading: boolean = false;
  disabled: boolean = true;
  comment: string = "";
  errorMessage: string = "";
  @Input("client") client: Client;
  @Input("language") language;
  @Output() closeEmitter: EventEmitter<boolean> = new EventEmitter();

  chatServiceSubscription: Subscription;

  constructor(public chatService: ChatService) {}

  ngOnInit() {
    this.gettingLanguage = this.language["chat"]["ui_elements_messages"];
    this.chatServiceSubscription = this.chatService.chatObservable.subscribe(
      (response: any) => {
        if (!response.isClosed) {
          this.groupId = response.groupId;
          this.getInternalAgents();
        }
      }
    );
    this.agentId = this.chatService.agentId;
  }

  closePopup() {
    this.closeEmitter.emit(true);
  }

  agentSelected(index: number) {
    // console.log(this.internalAgents[this.lastSelectedAgentIndex])
    if (
      this.internalAgents[this.lastSelectedAgentIndex] &&
      this.lastSelectedAgentIndex !== index
    ) {
      this.internalAgents[this.lastSelectedAgentIndex].agentSelected = false;
    }
    this.internalAgents[index].agentSelected = !this.internalAgents[index]
      .agentSelected;
    if (this.internalAgents[index].agentSelected) {
      this.selectedAgent = this.internalAgents[index];
      this.disabled = false;
    } else {
      this.selectedAgent = undefined;
      this.disabled = true;
    }
    this.lastSelectedAgentIndex = index;
  }

  groupSelected(index: number) {
    if (
      this.externalGroups[this.lastSelectedGroupIndex] &&
      this.lastSelectedGroupIndex !== index
    ) {
      this.externalGroups[this.lastSelectedGroupIndex].groupSelected = false;
    }
    this.externalGroups[index].groupSelected = !this.externalGroups[index]
      .groupSelected;
    if (this.externalGroups[index].groupSelected) {
      this.selectedGroup = this.externalGroups[index];
      this.disabled = false;
    } else {
      this.selectedGroup = undefined;
      this.disabled = true;
    }
    this.lastSelectedGroupIndex = index;
  }

  transferChat() {
    if (this.showInternalAgents) {
      return this.chatService
        .internalChatTransfer(
          this.client.channelId,
          this.selectedAgent.id,
          this.comment
        )
        .subscribe((response: any) => {
          if (response.status) {
            this.client.isClosed = true;
            this.chatService.clickedChat(this.client);
            this.closeEmitter.emit(true);
          } else {
            //alert('something went wrong in internal chat transfer');
            this.errorMessage = response.message;
          }
        });
    } else {
      return this.chatService
        .internalGroupTransfer(
          this.client.channelId,
          this.selectedGroup.groupId,
          this.comment
        )
        .subscribe((response: any) => {
          if (response.status) {
            this.client.isClosed = true;
            this.chatService.clickedChat(this.client);
            this.closeEmitter.emit(true);
          } else {
            alert("something went wrong in group chat transfer");
          }
        });
    }
  }

  getExternalGroups() {
    this.isLoading = true;
    this.disabled = true;
    this.searchKey = "";
    this.externalGroups = [];
    this.showInternalAgents = false;
    this.chatService.getExternalGroups().subscribe((response: any) => {
      this.isLoading = false;
      if (response.status) {
        response.data.forEach((innerResponse: any) => {
          let group = new Group();
          group.groupId = innerResponse.id;
          group.groupName = innerResponse.name;
          group.groupSelected = false;
          group.showGroup = true;
          if (group.groupId != this.groupId) {
            this.externalGroups.push(group);
          }
        });
      }
    });
  }

  getInternalAgents() {
    this.disabled = true;
    this.isLoading = true;
    this.searchKey = "";
    this.internalAgents = [];
    this.showInternalAgents = true;
    this.chatService
      .getInternalAgents(this.groupId)
      .subscribe((response: any) => {
        this.isLoading = false;
        if (response.status) {
          response.data.forEach((innerResponse: any) => {
            let agent = new Agent();
            agent.id = innerResponse.id;
            agent.name = innerResponse.name;
            agent.image = innerResponse.image;
            agent.showAgent = true;
            agent.agentSelected = false;
            if (agent.id != this.agentId) {
              this.internalAgents.push(agent);
            }
          });
        }
      });
  }

  search() {
    // console.log(this.searchKey);
    this.errorMessage = "";
    if (this.showInternalAgents) {
      this.internalAgents.forEach(agent => {
        if (
          agent.name.toLowerCase().indexOf(this.searchKey.toLowerCase()) > -1
        ) {
          agent.showAgent = true;
        } else {
          agent.showAgent = false;
        }
      });
    } else {
      this.externalGroups.forEach(group => {
        if (
          group.groupName.toLowerCase().indexOf(this.searchKey.toLowerCase()) >
          -1
        ) {
          group.showGroup = true;
        } else {
          group.showGroup = false;
        }
      });
    }
  }

  ngOnDestroy() {
    this.chatServiceSubscription.unsubscribe();
  }
}
