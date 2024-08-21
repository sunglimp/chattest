import { Component, OnInit, Input, Output, EventEmitter, OnDestroy } from '@angular/core';
import { Agent } from '../../models/client.model';
import { ChatService } from '../../services/chat.service';
import { Subscription } from 'rxjs';


@Component({
  selector: 'app-internal-comment',
  templateUrl: './internal-comment.component.html',
  styleUrls: ['./internal-comment.component.scss']
})
export class InternalCommentComponent implements OnInit, OnDestroy{
  _showInternalAgents : boolean;
  @Input() set showInternalAgents(bool: boolean){
    this._showInternalAgents = bool;
    if(bool === false){
      this.agentsSet.clear();
    }
  }
  get showInternalAgents() : boolean{
    return this._showInternalAgents;
  }
  gettingLanguage: Object;
  internalAgents: Agent[] = [];
  shownAgents: Agent[] = [];
  groupId: number = -1;
  agentId: number = -1;
  agentsLength: number = 0;
  selectedIndex: number = 0;
  isLoading: boolean = true;
  searchKey: string = '';
  agentsSet = new Set;
  keySubscriber : Subscription;

  @Output() commentEvent: EventEmitter<Object> = new EventEmitter();

  constructor(private chatService: ChatService) {

  }

  ngOnInit() {
    this.getLanguage();
    this.chatService.chatObservable.subscribe(
      (response: any) => {
        this.groupId = response.groupId;
      }
    );
    this.agentId = this.chatService.agentId;
    this.keySubscriber = this.chatService.keyCodeObservale.subscribe(
      (response: any) => {
        if (response.keyCode === 50 && response.searchKey.length < 1) {
          this.isLoading = true;
          this.getInternalAgents();
          this.selectedIndex = 0;
          this.searchKey = '';
        }
        if (response.feature === 'comment') {
          this.iterateList(response.keyCode);
          if (response.searchKey.length > 0) {
            if (this.searchKey !== response.searchKey) {
              this.searchAgents(response.searchKey);
              this.searchKey = response.searchKey;
            }
          }
          else {
            this.shownAgents = this.internalAgents;
            this.agentsLength = this.internalAgents.length;
          }
        }
      }
    );
  }

  searchAgents(searchKey: string) {
    if (/\S/.test(searchKey)) {
      searchKey = searchKey.toLowerCase().replace(/\s+$/g, '');
    }
    this.shownAgents = [];
    this.internalAgents.forEach(
      (agent: Agent) => {
        if (agent.name.trim().toLowerCase().indexOf(searchKey) > -1) {
          this.shownAgents.push(agent);
        }
      });
    this.agentsLength = this.shownAgents.length;
    if (this.agentsLength < 1) {
      this.showInternalAgents = false
    }
    else {
      this.showInternalAgents = true;
    }
  }

  iterateList(keyCode) {
    if (keyCode === 38) { // up key
      this.selectedIndex !== 0 ? this.selectedIndex-- : this.selectedIndex = this.agentsLength - 1;
    }
    if (keyCode === 40) { // down key
      this.selectedIndex === this.agentsLength - 1 ? this.selectedIndex = 0 : this.selectedIndex++;
    }
    if (keyCode === 13) { // right or enter
      if (this.shownAgents[this.selectedIndex]) {
        this.commentEvent.emit({ 'output': this.shownAgents[this.selectedIndex], 'event': 'enter' });
      }
      else {
        this.commentEvent.emit('send');
      }
    }
    if (keyCode === 37) { // left
      // this.commentEvent.emit('false');
    }
    if (keyCode === 27) { // esc or backspace
      this.commentEvent.emit('false');
      this.shownAgents = [];
    }
  }


  agentSelected(index: number) {
    this.commentEvent.emit({ 'output': this.shownAgents[index], 'event': 'click' });
  }

  getInternalAgents() {
    this.internalAgents = [];
    // this.showInternalAgents = true;
    this.chatService.getInternalAgents(this.groupId).subscribe(
      (response: any) => {
        if (response.status) {
          response.data.forEach(
            (innerResponse: any) => {
              let agent = new Agent;
              agent.id = innerResponse.id;
              agent.name = innerResponse.name;
              agent.image = innerResponse.image;
              agent.agentSelected = false;
              if (agent.id !== this.agentId && !this.agentsSet.has(agent.id)) {
                this.agentsSet.add(agent.id);
                this.internalAgents.push(agent);
                this.agentsLength = this.internalAgents.length;
              }
            }
          );
          this.isLoading = false;
        }
      }
    )
  }

  ngOnDestroy(){
    this.keySubscriber.unsubscribe();
  }
  getLanguage(){
    this.chatService.getLanguage().subscribe((res)=>{
      this.gettingLanguage = res['data']['interpretation']['chat']['ui_elements_messages']['no_one_online'];
    })
  }
}
