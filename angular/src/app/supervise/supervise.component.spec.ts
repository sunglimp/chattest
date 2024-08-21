import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { SuperviseComponent } from './supervise.component';

describe('SuperviseComponent', () => {
  let component: SuperviseComponent;
  let fixture: ComponentFixture<SuperviseComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ SuperviseComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(SuperviseComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
