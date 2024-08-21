import { BrowserModule } from "@angular/platform-browser";
import { NgModule } from "@angular/core";
import { FormsModule } from "@angular/forms";
import { AppRoutingModule } from "./app-routing.module";
import { AppComponent } from "./app.component";
import { HttpClientModule, HTTP_INTERCEPTORS } from "@angular/common/http";
import { AngularFontAwesomeModule } from "angular-font-awesome";
import { PerfectScrollbarModule } from "ngx-perfect-scrollbar";
import { PERFECT_SCROLLBAR_CONFIG } from "ngx-perfect-scrollbar";
import { PerfectScrollbarConfigInterface } from "ngx-perfect-scrollbar";
import { EscCloseDirective } from "./shared/directives/esc.close.directive";
import { BrowserAnimationsModule } from "@angular/platform-browser/animations";
import { RoundProgressModule } from "angular-svg-round-progressbar";
import { HttpInterceptorService } from "./shared/http/http-interceptor.service";
import { LeadStatusComponent } from "./lead-status/lead-status.component";
import { MissedChatModule } from "./missed-chat/missed-chat.module";
import { ChatsModule } from "./chats/chats.module";
import { SharedFaturesModule } from "./shared/features/shared-features.module";
import { SharedPipeModule } from "./shared/pipes/shared-pipes.module";
import { ArchiveModule } from "./archive/archive.module";
import { SuperviseModule } from "./supervise/supervise.module";
import { TicketStatusModule } from "./ticket-status/ticket-status.module";
import { LeadStatusModule } from "./lead-status/lead-status.module";
import { CannedResponseModule } from "./canned-response/canned-response.module";

const DEFAULT_PERFECT_SCROLLBAR_CONFIG: PerfectScrollbarConfigInterface = {
  suppressScrollX: true
};

@NgModule({
  declarations: [AppComponent, EscCloseDirective],
  imports: [
    SharedPipeModule,
    BrowserModule,
    FormsModule,
    HttpClientModule,
    BrowserAnimationsModule,
    AngularFontAwesomeModule,
    PerfectScrollbarModule,
    RoundProgressModule,
    MissedChatModule,
    CannedResponseModule,
    AppRoutingModule,
    ChatsModule,
    ArchiveModule,
    SuperviseModule,
    TicketStatusModule,
    SharedFaturesModule,
    LeadStatusModule
  ],
  providers: [
    {
      provide: HTTP_INTERCEPTORS,
      useClass: HttpInterceptorService,
      multi: true
    },
    {
      provide: PERFECT_SCROLLBAR_CONFIG,
      useValue: DEFAULT_PERFECT_SCROLLBAR_CONFIG,
      multi: true
    }
  ],
  bootstrap: [AppComponent]
})
export class AppModule {}
