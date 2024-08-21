import { Component, OnInit, ViewChild, Input } from "@angular/core";
import { ChatService } from "../../services/chat.service";
import { Tags } from "../../models/client.model";
import { PerfectScrollbarComponent } from "ngx-perfect-scrollbar";
import { ArchiveService } from "../../services/archive.service";

@Component({
  selector: "app-tags",
  templateUrl: "./tags.component.html",
  styleUrls: ["./tags.component.scss"]
})
export class TagsComponent implements OnInit {
  gettingLanguage: object;
  isLoading: boolean = true;
  error: boolean = false;
  errorText: string = "This tag has been added already!";
  tags: Tags[] = [];
  taggedIds: number[] = [];
  isTagged: boolean;
  @Input("language") language;
  @Input() showFeature: boolean;
  @Input() showAddTag: boolean;
  @Input() channelId: number;
  @ViewChild("scroll") scroll: PerfectScrollbarComponent;
  @ViewChild("tagInput") tagInput: any;

  constructor(
    private chatService: ChatService,
    private archiveService: ArchiveService
  ) {}

  ngOnInit() {
    this.gettingLanguage = this.language;
    this.chatService.getTags(this.channelId).subscribe((tags: any) => {
      if (tags.data.length > 0) {
        tags.data.forEach(data => {
          let tag = new Tags();
          tag.id = data.id;
          tag.name = data.tag;
          tag.canDelete = data.canDelete === 1;
          tag.isSelected = data.selected === 1;
          if (tag.isSelected) {
            this.taggedIds.push(tag.id);
            this.isTagged = true;
          }
          this.tags.push(tag);
        });
      }
      this.isLoading = false;
      this.chatService.taggedIds = [...this.taggedIds];
    });
  }

  addTag() {
    this.error = false;
    let tagName = this.tagInput.nativeElement.value;
    if (tagName) {
      if (tagName.indexOf(" ") > -1) {
        setTimeout(() => (this.error = true), 1);
        // this.errorText = 'No white spaces allowed!';
        this.errorText = this.gettingLanguage["chat"]["ui_elements_messages"][
          "no_white_space"
        ]
          ? this.gettingLanguage["chat"]["ui_elements_messages"][
              "no_white_space"
            ]
          : "No white spaces allowed!";
        this.tagInput.nativeElement.value = "";
        return false;
      }
      this.chatService.addTag(tagName, this.channelId).subscribe(
        (response: any) => {
          let tag = new Tags();
          tag.name = tagName;
          tag.isSelected = true;
          tag.canDelete = true;
          tag.id = response.data.tagId;
          this.tags.push(tag);
          this.scroll.directiveRef.scrollToBottom(
            this.chatService.negetiveInfinity,
            1
          );
          this.tagInput.nativeElement.value = "";
          this.tags[this.tags.length - 1].isSelected = false;
          this.linkTag(this.tags.length - 1);
        },
        error => {
          setTimeout(() => (this.error = true), 1);
          // this.errorText = 'This tag has been added already!';
          this.errorText = this.gettingLanguage["chat"]["ui_elements_messages"][
            "tag_added"
          ]
            ? this.gettingLanguage["chat"]["ui_elements_messages"]["tag_added"]
            : "This tag has been added already!";
          this.tagInput.nativeElement.value = "";
        }
      );
    } else {
      this.error = true;
      // this.errorText = 'Please enter a valid tag!';
      this.errorText = this.gettingLanguage["chat"]["ui_elements_messages"][
        "invalid_tag"
      ]
        ? this.gettingLanguage["chat"]["ui_elements_messages"]["invalid_tag"]
        : "Please enter a valid tag!";
      return false;
    }
  }

  deleteTag(event, index: number) {
    event.stopPropagation();
    this.taggedIds.forEach((tagId, i) => {
      if (tagId === this.tags[index].id) {
        this.taggedIds.splice(i, 1);
        return false;
      }
    });
    if (this.taggedIds.length === 0) {
      this.archiveService.chatTaggedSubject.next(false);
      this.isTagged = false;
    }
    this.chatService
      .deleteTag(this.tags[index].id, this.channelId)
      .subscribe((response: any) => {
        if (response.status) {
          this.tags.splice(index, 1);
          this.error = false;
        } else {
          this.error = true;
          // this.errorText = 'This tag can\'t be deleted!';
          this.errorText = this.gettingLanguage["chat"]["ui_elements_messages"][
            "failed_tag"
          ]
            ? this.gettingLanguage["chat"]["ui_elements_messages"]["failed_tag"]
            : "This tag can't be deleted!";
          return false;
        }
      });
  }

  linkTag(index: number) {
    console.log(index);
    console.log(this.tags[index]);
    if (this.tags[index].isSelected) {
      // unlink
      this.tags[index].isSelected = false;
      this.taggedIds.forEach((tagId, i) => {
        if (tagId === this.tags[index].id) {
          this.taggedIds.splice(i, 1);
          return false;
        }
      });
      if (this.taggedIds.length === 0) {
        this.archiveService.chatTaggedSubject.next(false);
        this.isTagged = false;
      }
      this.chatService
        .unlinkTag(this.tags[index].id, this.channelId)
        .subscribe((response: any) => {
          if (response.status) {
            this.chatService.taggedIds = [...this.taggedIds];
          }
        });
    } else {
      // link
      this.tags[index].isSelected = true;
      if (this.taggedIds.indexOf(this.tags[index].id)) {
        this.taggedIds.push(this.tags[index].id);
      }
      if (this.taggedIds.length > 0 && !this.isTagged) {
        this.archiveService.chatTaggedSubject.next(true);
        this.isTagged = true;
      }
      this.chatService
        .linkTag(this.tags[index].id, this.channelId)
        .subscribe((response: any) => {
          if (response.status) {
            this.chatService.taggedIds = [...this.taggedIds];
          }
        });
    }
  }
}
