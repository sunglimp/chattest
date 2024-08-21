<div class="popup popup__container" id="send-attachments__popup" >
    <div class="popup__wrapper">
        <a class="close-btn close-btn-tags" ><i class="fas fa-times"></i></a>
        <div class="popup__wrapper__heading">{{default_trans($organizationId.'/permission.ui_elements_messages.upload_attachment_size', __('default/permission.ui_elements_messages.upload_attachment_size'))}}</div>
        
        <div class="popup__content justify-center">
        <form>
        <div class="popup__permissions--addtags" style="width:50%; flex-wrap:wrap" >
            <input type="text" class="custom-input permissions-input" name="size" id="send_attachment" value="{{$data->size}}" placeholder="Enter size in mb" style="width:100%"><span class="input__mb">{{default_trans($organizationId.'/permission.ui_elements_messages.mb', __('default/permission.ui_elements_messages.mb'))}}</span>
            <p class="warning-text" style="width:100%;margin-top:.2rem;"></p>
        </div>
        <div class="buttons__all">
                <button type=button class="custom-button custom-button-green" id="cancel">{{default_trans($organizationId.'/permission.ui_elements_messages.cancel', __('default/permission.ui_elements_messages.cancel'))}}</button>
                <button type=button class="custom-button custom-button-blue" id="send_attachment_button">{{default_trans($organizationId.'/permission.ui_elements_messages.submit', __('default/permission.ui_elements_messages.submit'))}}</button>
            </div>
        </form>
        </div>


    </div>
</div>
