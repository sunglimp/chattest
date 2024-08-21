import { NgModule, InjectionToken } from "@angular/core";
import { LoaderComponent } from "./loader/loader.component";
import { ClosePopupComponent } from "./close-popup/close-popup.component";
import { CommonModule } from "@angular/common";
import { PerfectScrollbarModule } from "ngx-perfect-scrollbar";
import { CannedResponseComponent } from "./canned-response/canned-response.component";
import { ChatBoxComponent } from "./chat-box/chat-box.component";
import { ChatInputComponent } from "./chat-input/chat-input.component";
import { ChatTransferComponent } from "./chat-transfer/chat-transfer.component";
import { EmailComponent } from "./email/email.component";
import { FileViewComponent } from "./file-view/file-view.component";
import { InfoComponent } from "./info/info.component";
import { InternalCommentComponent } from "./internal-comment/internal-comment.component";
import { NoRecordsComponent } from "./no-records/no-records.component";
import { TagsComponent } from "./tags/tags.component";
import { TicketsComponent } from "./tickets/tickets.component";
import { RoundProgressModule } from "angular-svg-round-progressbar";
import { SharedPipeModule } from "../pipes/shared-pipes.module";
import { AgmCoreModule } from "@agm/core";
import { PickerModule } from "@ctrl/ngx-emoji-mart";
import { EmojiModule } from "@ctrl/ngx-emoji-mart/ngx-emoji";
import { FormsModule } from "@angular/forms";
import { QuillModule } from "ngx-quill";
// import { NgxIntlTelInputModule } from "ngx-intl-tel-input";
// import { BsDropdownModule } from "ngx-bootstrap/dropdown";
import { InternationalPhoneNumber2Module } from "ngx-international-phone-number2";

@NgModule({
  imports: [
    CommonModule,
    PerfectScrollbarModule,
    RoundProgressModule,
    SharedPipeModule,
    AgmCoreModule.forRoot({
      apiKey: "AIzaSyASBw4M_lkcJjuByWsvJHMLJrEPWBajUy4"
    }),
    // BsDropdownModule.forRoot(),
    // NgxIntlTelInputModule,
    InternationalPhoneNumber2Module,
    EmojiModule,
    PickerModule,
    FormsModule,
    QuillModule
  ],
  exports: [
    LoaderComponent,
    ClosePopupComponent,
    CannedResponseComponent,
    ChatBoxComponent,
    ChatInputComponent,
    ChatTransferComponent,
    EmailComponent,
    FileViewComponent,
    InfoComponent,
    InternalCommentComponent,
    NoRecordsComponent,
    TagsComponent,
    TicketsComponent
  ],
  declarations: [
    LoaderComponent,
    ClosePopupComponent,
    CannedResponseComponent,
    ChatBoxComponent,
    ChatInputComponent,
    ChatTransferComponent,
    EmailComponent,
    FileViewComponent,
    InfoComponent,
    InternalCommentComponent,
    NoRecordsComponent,
    TagsComponent,
    TicketsComponent
  ],
  providers: []
})
export class SharedFaturesModule {
  constructor() {}
}
