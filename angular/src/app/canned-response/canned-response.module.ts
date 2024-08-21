import { NgModule } from "@angular/core";
import { CannedComponent } from "./canned-response.component";
import { CommonModule } from "@angular/common";
import { RouterModule, Routes } from "@angular/router";
import { NgxDaterangepickerMd } from "ngx-daterangepicker-material";
import { FormsModule } from "@angular/forms";
import { SharedFaturesModule } from "../shared/features/shared-features.module";
import { PerfectScrollbarModule } from "ngx-perfect-scrollbar";
import { SharedPipeModule } from "../shared/pipes/shared-pipes.module";
import { CannedResponseService } from "./canned-response.service";
import { HttpClientModule } from "@angular/common/http";
import { CannedPopupComponent } from "./canned-popup/canned-popup.component";

const routes: Routes = [{ path: "", component: CannedComponent }];

@NgModule({
  declarations: [CannedComponent, CannedPopupComponent],
  imports: [
    SharedFaturesModule,
    SharedPipeModule,
    HttpClientModule,
    FormsModule,
    PerfectScrollbarModule,
    NgxDaterangepickerMd.forRoot(),
    CommonModule,
    RouterModule.forChild(routes)
  ],
  exports: [CannedComponent],
  providers: [CannedResponseService]
})
export class CannedResponseModule {
  constructor() {}
}
