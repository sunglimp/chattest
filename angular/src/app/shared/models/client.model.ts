export class Client {
  clientIndex: number;
  channelName: string;
  channelType: string;
  clientDisplayName: string;
  clientDisplayNumber: string;
  clientId: number;
  channelId: number;
  agentId: number;
  channelAgentId: number;
  channelAgentName: string;
  channelAgentRole: string;
  undreadMessage: string;
  unreadMessagesCount: number;
  internalId: number;
  clientInfo: object;
  groupId: number;
  isClosed: boolean;
  colorCode: string;
  chatDate: string;
  isTagged: boolean;
  isImportant: boolean;
  hasHistory: boolean;
  isHigh: boolean;
  hasLeft: boolean;
  status: string;
  botChat: string;
  sourceType: string;
  isSessionTimeout: boolean;
}

export class ChateDownload {
  chat_download: AgentWiseChatDownload;
}

export class AgentWiseChatDownload {
  agent_wise_chat_download: boolean;
}

export class Permissions {
  roles: boolean;
  cannedResponse: boolean;
  dashboardAccess: boolean;
  groupCreation: boolean;
  superviseTipoff: boolean;
  chatHistory: boolean;
  chatNotifier: boolean;
  chatTransfer: boolean;
  autoChatTransfer: boolean;
  chatTags: boolean;
  chatDownload: boolean;
  settings: ChateDownload;
  chatFeedback: boolean;
  sendAttachments: boolean;
  email: boolean;
  timeout: boolean;
  internalComments: boolean;
  downloadReport: boolean;
  chat: boolean;
  chatAttachmentSize: number;
  banUser: boolean;
  tmsKey: boolean;
  lmsKey: boolean;
  lqsKey: boolean;
  audioNotification: boolean;
  audioToNotify: string[];
  identifierMasking: boolean;
  tagSettings: Object;
  customerInformation: boolean;
}

export class Chat {
  agentDisplayName: string;
  agentId: number;
  chatDate: string;
  chatTime: string;
  message: Message;
  messageType: string;
  recipient: string;
  isCurrent: boolean;
  channelName: string;
  botChat: string;
  filePath: string;
  sourceType: string;
}

export class Message {
  type: string;
  text: string;
  name: string;
  extension: string;
  size: string;
  filePath: string;
  filehash: string;
  comment: string;
  transferredBy: string;
  location: any;
}

export class ClientInfo {
  name: string;
  email: string;
}

export class Group {
  groupId: number;
  groupName: string;
  groupSelected: boolean;
  showGroup: boolean;
}
export class Agent {
  id: number;
  name: string;
  image: string;
  agentSelected: boolean;
  showAgent: boolean;
}

export class Tags {
  id: number;
  canDelete: boolean;
  name: string;
  isSelected: boolean;
}

export class TicketFields {
  fieldName: string;
  displayName: string;
  groupName: string;
  isRequired: boolean;
  isAttachment: string;
  dropdownValues: Object;
  isDropdown: string;
  errors: string;
  attachmenterrors: string;
}

export class Organization {
  id: number;
  name: string;
}
