@php
$languageClass = '';
if (Auth::user()->role_id !=1 && Auth::user()->language =="ar"){
    $languageClass = 'arabic';
}
@endphp

<div class="popup popup__container" id="password__popup">
    <div class="popup__wrapper popup__small">
        <a class="close-btn {{ $languageClass }}"><i class="fas fa-times"></i></a>
        <div class="popup__wrapper__heading">{{default_trans($organizationId.'/user_list.ui_elements_messages.update_password', __('default/user_list.ui_elements_messages.update_password'))}}</div>
        <div class="popup__content">
            <div class="popup__small__content--wrap">
                <label class="text-left">{{default_trans($organizationId.'/user_list.ui_elements_messages.new_password', __('default/user_list.ui_elements_messages.new_password'))}}<span class="astrick">*</span></label>
                <input type="text" id="username" name="username" style="height:0;width:0;opacity:0" />
                <input type="password" name="password" id="password" class="custom-input" />
            </div>
            <div class="popup__small__content--wrap">
                <label class="text-left">{{default_trans($organizationId.'/user_list.ui_elements_messages.confirm_password', __('default/user_list.ui_elements_messages.confirm_password'))}}<span class="astrick">*</span></label>
                <input type="password" name="confirm_password" id="confirm_password" class="custom-input" />
            </div>
            <input type="hidden" value="{{$userId}}" id="user_id" />
            <div class="buttons__all">
                <button type=button class="custom-button custom-button-green" id="cancel">{{default_trans($organizationId.'/user_list.ui_elements_messages.close', __('default/user_list.ui_elements_messages.close'))}}</button>
                <button type="button" class="custom-button custom-button-blue" id="update_password_button">{{default_trans($organizationId.'/user_list.ui_elements_messages.update', __('default/user_list.ui_elements_messages.update'))}}</button>
            </div>
        </div>
    </div>
</div>
