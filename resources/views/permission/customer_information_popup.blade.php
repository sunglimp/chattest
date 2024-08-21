<div class="popup popup__container" id="customer_information__popup">
    <div class="popup__wrapper">
        <a class="close-btn close-btn-tags" ><i class="fas fa-times"></i></a>
        <div class="popup__wrapper__heading">{{default_trans($organizationId.'/permission.ui_elements_messages.customer_information', __('default/permission.ui_elements_messages.customer_information'))}}</div>
        <div class="popup__content">
            <div class="popup__permissions--switch">
                <label class="active">
                    <input type="radio" id="customer-info-number" name="customerChatInfoLabel" value="{{ config('constants.CUSTOMER_CHAT_LABEL.WHATSAPP.NUMBER') }}" @if(isset($data->whatsapp)) @if($data->whatsapp->client_display_attribute == 1) checked @endif @endif/>
                    <span>
                        {{default_trans($organizationId.'/permission.ui_elements_messages.wa_number', __('default/permission.ui_elements_messages.wa_number'))}}
                    </span>
                </label>
            </div>
            <div class="popup__permissions--switch">
                <label>
                    <input type="radio" id="customer-info-name" name="customerChatInfoLabel" value="{{ config('constants.CUSTOMER_CHAT_LABEL.WHATSAPP.NAME') }}" @if(isset($data->whatsapp)) @if($data->whatsapp->client_display_attribute == 2) checked @endif @endif/>
                    <span>
                        {{default_trans($organizationId.'/permission.ui_elements_messages.wa_name', __('default/permission.ui_elements_messages.wa_name'))}}
                    </span>
                </label>
            </div>
            <div class="popup__permissions--switch">
                <label>
                    <input type="radio" id="customer-info-number-and-name" name="customerChatInfoLabel" value="{{ config('constants.CUSTOMER_CHAT_LABEL.WHATSAPP.NUMBER_NAME') }}" @if(isset($data->whatsapp)) @if($data->whatsapp->client_display_attribute == 3) checked @endif @endif/>
                    <span>
                        {{default_trans($organizationId.'/permission.ui_elements_messages.wa_number_and_name', __('default/permission.ui_elements_messages.wa_number_and_name'))}}
                    </span>
                </label>
            </div>
            <div class="warning-text"></div>
            <div class="buttons__all">
                <button type="button" class="custom-button custom-button-green close-btn-tags" id="cancel">{{default_trans($organizationId.'/permission.ui_elements_messages.cancel', __('default/permission.ui_elements_messages.cancel'))}}</button>
                <button type="submit" class="custom-button custom-button-blue" id="update-customer-information-setting-button" value="Submit">{{default_trans($organizationId.'/permission.ui_elements_messages.submit', __('default/permission.ui_elements_messages.submit'))}}</button>
            </div>
        </div>
    </div>
</div>