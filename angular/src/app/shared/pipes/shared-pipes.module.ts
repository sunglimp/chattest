import { NgModule } from "@angular/core";
import { EmojipipePipe } from "./emojipipe.pipe";
import { ChatCirclePipe } from "./chat-circle.pipe";
import { LinkPipePipe } from "./link-pipe.pipe";
import { RemoveUnderscorePipe } from "./remove-underscore.pipe";
import {
  CheckTagSelectionPipe,
  RemoveAttachmentPipe,
  CheckForAttachmentPipe,
  RemoveCountryCodePipe
} from "./common.pipe";
import { PickerModule } from "@ctrl/ngx-emoji-mart";
import { EmojiModule } from "@ctrl/ngx-emoji-mart/ngx-emoji";

@NgModule({
  imports: [PickerModule, EmojiModule],
  declarations: [
    ChatCirclePipe,
    EmojipipePipe,
    LinkPipePipe,
    RemoveUnderscorePipe,
    CheckTagSelectionPipe,
    RemoveAttachmentPipe,
    RemoveCountryCodePipe,
    CheckForAttachmentPipe
  ],
  exports: [
    ChatCirclePipe,
    EmojipipePipe,
    LinkPipePipe,
    RemoveUnderscorePipe,
    CheckTagSelectionPipe,
    RemoveAttachmentPipe,
    CheckForAttachmentPipe,
    RemoveCountryCodePipe
  ],
  providers: [RemoveAttachmentPipe]
})
export class SharedPipeModule {}
