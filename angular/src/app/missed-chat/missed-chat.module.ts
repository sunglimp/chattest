import { NgModule } from "@angular/core";
import { CommonModule } from "@angular/common";
import { RouterModule, Routes } from "@angular/router";
import { MissedChatComponent } from "./missed-chat.component";
import { NgxDaterangepickerMd } from "ngx-daterangepicker-material";
import { FormsModule } from "@angular/forms";
import { SharedFaturesModule } from "../shared/features/shared-features.module";
import { PerfectScrollbarModule } from "ngx-perfect-scrollbar";
import { SharedPipeModule } from "../shared/pipes/shared-pipes.module";

const routes: Routes = [
  {
    path: "",
    component: MissedChatComponent
  }
];

@NgModule({
  imports: [
    CommonModule,
    SharedFaturesModule,
    SharedPipeModule,
    FormsModule,
    PerfectScrollbarModule,
    NgxDaterangepickerMd.forRoot(),
    RouterModule.forChild(routes)
  ],
  declarations: [MissedChatComponent],
  exports: [MissedChatComponent],
  providers: []
})
export class MissedChatModule {
  constructor() {}
}
