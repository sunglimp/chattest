<div class="popup popup__container" id="ban__popup" >
    <div class="popup__wrapper">
        <a class="close-btn close-btn-tags" ><i class="fas fa-times"></i></a>
        <div class="popup__wrapper__heading">{{default_trans($organizationId.'/permission.ui_elements_messages.ban_client_(days)', __('default/permission.ui_elements_messages.ban_client_(days)'))}}</div>
        
        <div class="popup__content justify-center">
        <div class="popup__permissions--addtags" style="width:50%; flex-wrap:wrap">
            <input type="text" class="custom-input permissions-input" name="ban_days" id="ban_user" value="{{isset($data->days) ? $data->days : 1}}" placeholder="{{default_trans($organizationId.'/permission.ui_elements_messages.enter_days', __('default/permission.ui_elements_messages.enter_days'))}}" style="width:100%"><span class="input__mb">{{default_trans($organizationId.'/permission.ui_elements_messages.days', __('default/permission.ui_elements_messages.days'))}}</span>
            <p class="warning-text" style="width:100%;margin-top:.2rem;"></p>
        </div>
        <div class="buttons__all">
                <button type=button class="custom-button custom-button-green" id="cancel">{{default_trans($organizationId.'/permission.ui_elements_messages.cancel', __('default/permission.ui_elements_messages.cancel'))}}</button>
                <button type=button class="custom-button custom-button-blue" id="ban_user_button">{{default_trans($organizationId.'/permission.ui_elements_messages.submit', __('default/permission.ui_elements_messages.submit'))}}</button>
            </div>
        </div>


    </div>
</div>
