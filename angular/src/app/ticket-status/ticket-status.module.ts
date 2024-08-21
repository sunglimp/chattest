import { NgModule } from "@angular/core";
import { CommonModule } from "@angular/common";
import { SharedPipeModule } from "../shared/pipes/shared-pipes.module";
import { SharedFaturesModule } from "../shared/features/shared-features.module";
import { FormsModule } from "@angular/forms";
import { PerfectScrollbarModule } from "ngx-perfect-scrollbar";
import { TicketStatusComponent } from "./ticket-status.component";
import { RouterModule, Routes } from "@angular/router";

const routes: Routes = [
  {
    path: "",
    component: TicketStatusComponent
  }
];

@NgModule({
  imports: [
    CommonModule,
    SharedPipeModule,
    SharedFaturesModule,
    RouterModule.forChild(routes),
    FormsModule,
    PerfectScrollbarModule
  ],
  declarations: [TicketStatusComponent],
  exports: [TicketStatusComponent],
  providers: []
})
export class TicketStatusModule {}
