import { NgModule } from "@angular/core";
import { ArchiveComponent } from "./archive.component";
import { CommonModule } from "@angular/common";
import { SharedFaturesModule } from "../shared/features/shared-features.module";
import { SharedPipeModule } from "../shared/pipes/shared-pipes.module";
import { FormsModule } from "@angular/forms";
import { ArchiveListComponent } from "./archive-list/archive-list.component";
import { ArchiveChatComponent } from "./archive-chat/archive-chat.component";
import { PerfectScrollbarModule } from "ngx-perfect-scrollbar";
import { NgxDaterangepickerMd } from "ngx-daterangepicker-material";
import { RouterModule, Routes } from "@angular/router";

const routes: Routes = [
  {
    path: "",
    component: ArchiveComponent
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
  declarations: [ArchiveComponent, ArchiveListComponent, ArchiveChatComponent],
  exports: [ArchiveComponent, ArchiveListComponent, ArchiveChatComponent],
  providers: []
})
export class ArchiveModule {
  constructor() {}
}
