import { Injectable } from '@angular/core';
import { ChatService } from './chat.service';

@Injectable({
  providedIn: 'root'
})
export class SuperviseService {

  agentId : number;

  constructor(private chatService: ChatService) { 
    this.agentId = this.chatService.agentId;
  }

  getSupervisedChannels(){
    return this.chatService.get(`/v1/supervisors/${this.agentId}/channels`);
  }

  getSupervisedAgents(){
    return this.chatService.get(`/v1/supervisors/${this.agentId}/agents`);
  }
}
