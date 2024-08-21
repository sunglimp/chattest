import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { SuperviseListComponent } from './supervise-list.component';

describe('SuperviseListComponent', () => {
  let component: SuperviseListComponent;
  let fixture: ComponentFixture<SuperviseListComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ SuperviseListComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(SuperviseListComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
