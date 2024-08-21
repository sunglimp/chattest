import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { InternalCommentComponent } from './internal-comment.component';

describe('InternalCommentComponent', () => {
  let component: InternalCommentComponent;
  let fixture: ComponentFixture<InternalCommentComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ InternalCommentComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(InternalCommentComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
