import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ArchiveChatComponent } from './archive-chat.component';

describe('ArchiveChatComponent', () => {
  let component: ArchiveChatComponent;
  let fixture: ComponentFixture<ArchiveChatComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ArchiveChatComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ArchiveChatComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
