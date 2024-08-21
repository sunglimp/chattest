import { NgModule } from "@angular/core";
import { CommonModule } from "@angular/common";
import { SuperviseComponent } from "./supervise.component";
import { SharedPipeModule } from "../shared/pipes/shared-pipes.module";
import { SuperviseListComponent } from "./supervise-list/supervise-list.component";
import { SuperviseWindowComponent } from "./supervise-window/supervise-window.component";
import { FormsModule } from "@angular/forms";
import { PerfectScrollbarModule } from "ngx-perfect-scrollbar";
import { SharedFaturesModule } from "../shared/features/shared-features.module";
import { RouterModule, Routes } from "@angular/router";

const routes: Routes = [
  {
    path: "",
    component: SuperviseComponent
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
  exports: [
    SuperviseComponent,
    SuperviseListComponent,
    SuperviseWindowComponent
  ],
  declarations: [
    SuperviseComponent,
    SuperviseListComponent,
    SuperviseWindowComponent
  ],
  providers: []
})
export class SuperviseModule {}
