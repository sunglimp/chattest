 <div class="popup popup__container" id="archive_chat__popup">
    <div class="popup__wrapper">
        <a class="close-btn close-btn-tags" ><i class="fas fa-times"></i></a>
        <div class="popup__wrapper__heading">{{default_trans($organizationId.'/permission.ui_elements_messages.archive_chat', __('default/permission.ui_elements_messages.archive_chat'))}}</div>
        <div class="popup__content">
        <div class="popup__permissions--switch">
            <label class="active">
                <input type="radio" id="archive-chat-type-hierarchical" name="archiveType" value="1" @if(isset($data->archive_type)) @if($data->archive_type == 1) checked @endif @endif/>
                <span>
                    {{default_trans($organizationId.'/permission.ui_elements_messages.hierarchical_archive', __('default/permission.ui_elements_messages.hierarchical_archive'))}}
                </span>
            </label>
        </div>
            <div class="popup__permissions--switch">
            <label>
                <input type="radio" id="archive-chat-type-complete" name="archiveType" value="2" @if(isset($data->archive_type)) @if($data->archive_type == 2) checked @endif @endif/>
                <span>
                    {{default_trans($organizationId.'/permission.ui_elements_messages.complete_archive', __('default/permission.ui_elements_messages.complete_archive'))}}
                </span>
            </label>
        </div> 
                    <div class="buttons__all">
                    <button type="button" class="custom-button custom-button-green close-btn-tags" id="cancel">{{default_trans($organizationId.'/permission.ui_elements_messages.cancel', __('default/permission.ui_elements_messages.cancel'))}}</button>
                    <button type="submit" class="custom-button custom-button-blue" id="update-archive-chat-button" value="Submit">{{default_trans($organizationId.'/permission.ui_elements_messages.submit', __('default/permission.ui_elements_messages.submit'))}}</button>
                </div>
        </div>
    </div>
</div>