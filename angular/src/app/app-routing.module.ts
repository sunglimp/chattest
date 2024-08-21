import { NgModule } from "@angular/core";
import { Routes, RouterModule } from "@angular/router";
import { ArchiveListComponent } from "./archive/archive-list/archive-list.component";
import { LeadStatusComponent } from "./lead-status/lead-status.component";
const routes: Routes = [
  // { path: "", redirectTo: "chats", pathMatch: "full" },
  { path: "chats", loadChildren: "./chats/chats.module#ChatsModule" },
  { path: "archive", loadChildren: "./archive/archive.module#ArchiveModule" },
  { path: "ticket", loadChildren: "./archive/archive.module#ArchiveModule" },
  {
    path: "supervise",
    loadChildren: "./supervise/supervise.module#SuperviseModule"
  },
  {
    path: "status",
    loadChildren: "./ticket-status/ticket-status.module#TicketStatusModule"
  },
  { path: "banned-users", component: ArchiveListComponent },
  {
    path: "lead-status",
    loadChildren: "./lead-status/lead-status.module#LeadStatusModule"
  },
  {
    path: "missed",
    loadChildren: "./missed-chat/missed-chat.module#MissedChatModule"
  },
  {
    path: "canned",
    loadChildren:
      "./canned-response/canned-response.module#CannedResponseModule"
  }
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})
export class AppRoutingModule {}
