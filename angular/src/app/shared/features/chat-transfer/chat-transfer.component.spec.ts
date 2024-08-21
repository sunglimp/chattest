import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ChatTransferComponent } from './chat-transfer.component';

describe('ChatTransferComponent', () => {
  let component: ChatTransferComponent;
  let fixture: ComponentFixture<ChatTransferComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ChatTransferComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ChatTransferComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
