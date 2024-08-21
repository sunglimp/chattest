
<div class="popup popup__container" id="group-creation__popup" >
    <div class="popup__wrapper">
        <a class="close-btn close-btn-tags" ><i class="fas fa-times"></i></a>
        <div class="popup__wrapper__heading">{{default_trans($organizationId.'/permission.ui_elements_messages.groups', __('default/permission.ui_elements_messages.groups'))}}</div>
        <div class="popup__permissions--addtags add-permission-tags" >
            <button type="button" class="custom-button">
                <i class="fas fa-plus-square"></i>
                {{default_trans($organizationId.'/permission.ui_elements_messages.group', __('default/permission.ui_elements_messages.group'))}}</button>
        </div>
        <form >
            <div class="popup__permissions--addtags add-permission-input" style="display: none" >
                <input type="text" name="name" class="custom-input addgroup-input permissions-input" id="add_group" autocomplete="off" placeholder="{{default_trans($organizationId.'/permission.ui_elements_messages.enter_group', __('default/permission.ui_elements_messages.enter_group'))}}" >
                <button type="button" id="add_group_button" class="custom-button">{{default_trans($organizationId.'/permission.ui_elements_messages.add_group', __('default/permission.ui_elements_messages.add_group'))}}</button>
            </div>
        </form>
        <p class="warning-text"></p>
        <div class="popup__content">
            <ul class="popup__permissions--addedtags" id="group_ul">
                @foreach($group as $name)
                <li class="tag-purple" id="li{{$name->id}}"> <span>{{$name->name}}</span> @if($name->name != config('constants.GROUP_DEFAULT') && Auth::user()->can('delete', $name))<i class="fas fa-times" onclick="deleteGroup({{$name->id}})"></i>@endif</li>
                @endforeach
            </ul>
            
            <div class="buttons__all">
                <button type=button class="custom-button custom-button-green close-btn-tags" id="cancel">{{default_trans($organizationId.'/permission.ui_elements_messages.cancel', __('default/permission.ui_elements_messages.cancel'))}}</button>
            </div>
        </div>
    </div>
</div>