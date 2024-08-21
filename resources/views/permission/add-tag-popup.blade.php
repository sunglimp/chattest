 <div class="popup popup__container" id="tags__popup">
    <div class="popup__wrapper">
        <a class="close-btn close-btn-tags" ><i class="fas fa-times"></i></a>
        <div class="popup__wrapper__heading">{{default_trans($organizationId.'/permission.ui_elements_messages.chat-tags', __('default/permission.ui_elements_messages.chat-tags'))}}</div>
        <div class="popup__permissions--switch">
            <label class="active">
                <input type="radio" name="tagType" value=false @if(isset($tagSettings['tag_required'])) @if($tagSettings['tag_required'] == false) checked @endif @endif/>
                <span>
                    {{default_trans($organizationId.'/permission.ui_elements_messages.optional', __('default/permission.ui_elements_messages.optional'))}}
                </span>
            </label>
            <label>
                <input type="radio" name="tagType" value=true @if(isset($tagSettings['tag_required'])) @if($tagSettings['tag_required'] == true) checked @endif @endif/>
                <span>
                    {{default_trans($organizationId.'/permission.ui_elements_messages.mandatory', __('default/permission.ui_elements_messages.mandatory'))}}
                </span>
            </label>
        </div>

        <div class="popup__permissions--addtags add-permission-tags" >
            @if($canAddTag)
            <button type="button" class="custom-button">
                <i class="fas fa-plus-square"></i>
                {{default_trans($organizationId.'/permission.ui_elements_messages.tag', __('default/permission.ui_elements_messages.tag'))}}</button>
           @endif
        </div>
        <div class="popup__permissions--addtags add-permission-input" style="display: none" id="tag-input-div">
            <input type="text" class="custom-input addtag-input permissions-input" autocomplete="off"  placeholder="{{default_trans($organizationId.'/permission.ui_elements_messages.enter_tag', __('default/permission.ui_elements_messages.enter_tag'))}}" id="add_tag">
            <button type="button" class="custom-button" id="add_tag_button" onClick="addTags()">{{default_trans($organizationId.'/permission.ui_elements_messages.add_tag', __('default/permission.ui_elements_messages.add_tag'))}}</button>
        </div>
        <p class="warning-text"></p>
        <div class="popup__content">
            <ul class="popup__permissions--addedtags" id="add-tags-show">
            </ul>

            <form action="{{route('update.tag-settings')}}" id="tag-settings-form" type="post">
                <div class="popup__permissions--checkbox">
                    <p class="title">{{default_trans($organizationId.'/permission.ui_elements_messages.tag_creation', __('default/permission.ui_elements_messages.tag_creation'))}}</p> 
                    @foreach ($userRoles as $role)
                    <label class="checkbox__container">
                        <input type="checkbox" class="notification-event" name="roles[{{$role->id}}]"  value="true" @if(isset($tagSettings['tag_creation'][$role->id])) @if($tagSettings['tag_creation'][$role->id]) checked @endif @endif>
                        <span class="checkmark"></span>
                        <span class="checkbox__title">{{$role->name}}</span>
                    </label>
                    @endforeach
                </div>
                <input type="hidden" name="organization_id" value="{{$organizationSelectedId}}" />
                <input type="hidden" name="permission_id" value="{{$permissionId}}" />
                <div class="buttons__all">
                    <button type="button" class="custom-button custom-button-green close-btn-tags" id="cancel">{{default_trans($organizationId.'/permission.ui_elements_messages.cancel', __('default/permission.ui_elements_messages.cancel'))}}</button>
                    <button type="submit" class="custom-button custom-button-blue" id="submit" value="Submit">{{default_trans($organizationId.'/permission.ui_elements_messages.submit', __('default/permission.ui_elements_messages.submit'))}}</button>
                </div>
            </form>
        </div>
    </div>
</div>