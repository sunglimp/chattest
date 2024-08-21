import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { SuperviseWindowComponent } from './supervise-window.component';

describe('SuperviseWindowComponent', () => {
  let component: SuperviseWindowComponent;
  let fixture: ComponentFixture<SuperviseWindowComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ SuperviseWindowComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(SuperviseWindowComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
