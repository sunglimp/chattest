import { Component, OnInit, Input } from '@angular/core';

@Component({
  selector: 'app-no-records',
  templateUrl: './no-records.component.html',
  styleUrls: ['./no-records.component.scss']
})
export class NoRecordsComponent implements OnInit {

  @Input('message') message: string;

  constructor() { }

  ngOnInit() {
  }

}
