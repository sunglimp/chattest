import { NgModule } from "@angular/core";
import { CommonModule } from "@angular/common";
import { SharedPipeModule } from "../shared/pipes/shared-pipes.module";
import { SharedFaturesModule } from "../shared/features/shared-features.module";
import { FormsModule } from "@angular/forms";
import { PerfectScrollbarModule } from "ngx-perfect-scrollbar";
import { LeadStatusComponent } from "./lead-status.component";
import { RouterModule, Routes } from "@angular/router";

const routes: Routes = [
  {
    path: "",
    component: LeadStatusComponent
  }
];

@NgModule({
  imports: [
    CommonModule,
    SharedPipeModule,
    SharedFaturesModule,
    FormsModule,
    RouterModule.forChild(routes),
    PerfectScrollbarModule
  ],
  declarations: [LeadStatusComponent],
  exports: [LeadStatusComponent],
  providers: []
})
export class LeadStatusModule {}
