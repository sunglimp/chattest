<div class="popup popup__container" id="tms-key__popup" >
    <div class="popup__wrapper">
        <a class="close-btn close-btn-tags" ><i class="fas fa-times"></i></a>
        <div class="popup__wrapper__heading">{{default_trans($organizationId.'/permission.ui_elements_messages.surbo_ace_integration', __('default/permission.ui_elements_messages.surbo_ace_integration'))}}</div>
        
        <div class="popup__content justify-center">
        <form>
        <div class="popup__permissions--addtags" style="width:80%; flex-wrap:wrap" >
            <input type="text" class="custom-input permissions-input" name="tms_key" id="tms_unique_key" value="{{$data}}"  style="width:100%">
            <p class="warning-text" style="width:100%;margin-top:.2rem;"></p>
        </div>
        <div class="buttons__all">
                <button type=button class="custom-button custom-button-green" id="cancel">{{default_trans($organizationId.'/permission.ui_elements_messages.cancel', __('default/permission.ui_elements_messages.cancel'))}}</button>
                <button type=button class="custom-button custom-button-blue" id="tms_key_button">{{default_trans($organizationId.'/permission.ui_elements_messages.submit', __('default/permission.ui_elements_messages.submit'))}}</button>
            </div>
        </form>
        </div>


    </div>
</div>

