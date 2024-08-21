import { NgModule } from "@angular/core";
import { ChatsComponent } from "./chats.component";
import { ChatListComponent } from "./chat-list/chat-list.component";
import { ChatWindowComponent } from "./chat-window/chat-window.component";
import { SharedFaturesModule } from "../shared/features/shared-features.module";
import { CommonModule } from "@angular/common";
import { PerfectScrollbarModule } from "ngx-perfect-scrollbar";
import { SharedPipeModule } from "../shared/pipes/shared-pipes.module";
import { PickerModule } from "@ctrl/ngx-emoji-mart";
import { EmojiModule } from "@ctrl/ngx-emoji-mart/ngx-emoji";
import { RouterModule, Routes } from "@angular/router";

const routes: Routes = [
  {
    path: "",
    component: ChatsComponent
  }
];

@NgModule({
  imports: [
    CommonModule,
    SharedPipeModule,
    SharedFaturesModule,
    PerfectScrollbarModule,
    PickerModule,
    RouterModule.forChild(routes),
    EmojiModule
  ],
  exports: [ChatsComponent, ChatListComponent, ChatWindowComponent],
  declarations: [ChatsComponent, ChatListComponent, ChatWindowComponent],
  providers: []
})
export class ChatsModule {
  constructor() {}
}
