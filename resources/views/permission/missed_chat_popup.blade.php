@php
$disabled = '';
$hide_credential = 0;
if (Gate::allows('not-superadmin')) {
    $disabled = 'disabled';
    $hide_credential = 1;
}
@endphp
<div class="popup popup__container" id="missed_chat__popup">
    <div class="popup__wrapper">
        <a class="close-btn close-btn-tags" ><i class="fas fa-times"></i></a>
        <div class="popup__wrapper__heading">Missed Chat</div>
        <div class="missed_chat" style="padding: 20px 30px;">
            <div class="popup__content message-class">
                <label>{{default_trans($organizationId.'/permission.ui_elements_messages.missed_chat_api', __('default/permission.ui_elements_messages.missed_chat_api'))}}<span class="astrick">*</span> </label>
                <input type="text" name="api" id="missed_chat_api" class="custom-input" autocomplete="off" value="{{ isset($data->api) ? hide_service_credential($data->api, $hide_credential) : ''}}" {{ $disabled }}>
                <p class="warning-text" style="width:100%;margin:.5rem 0px; padding-left: 25%"></p>
            </div>
            <div class="popup__content message-class">
                <label>{{default_trans($organizationId.'/permission.ui_elements_messages.missed_chat_template_id', __('default/permission.ui_elements_messages.missed_chat_template_id'))}}<span class="astrick">*</span></label>
                <input type="text" name="template_id" id="missed_chat_template_id" class="custom-input" autocomplete="off"  value="{{ isset($data->templateId) ? hide_service_credential($data->templateId, $hide_credential) : ''}}" {{ $disabled }}>
                <p class="warning-text" style="width:100%;margin:.5rem 0px; padding-left: 25%"></p>
            </div>
            <div class="popup__content message-class">
                <label>{{default_trans($organizationId.'/permission.ui_elements_messages.missed_chat_bot_id', __('default/permission.ui_elements_messages.missed_chat_bot_id'))}}<span class="astrick">*</span></label>
                <input type="text" name="bot_id" id="missed_chat_bot_id" class="custom-input" autocomplete="off" value="{{ isset($data->botId) ? hide_service_credential($data->botId, $hide_credential) : '' }}" {{ $disabled }}>
                <p class="warning-text" style="width:100%;margin:.5rem 0px; padding-left: 25%"></p>
            </div>
            <div class="popup__content message-class">
                <label>{{default_trans($organizationId.'/permission.ui_elements_messages.missed_chat_token', __('default/permission.ui_elements_messages.token'))}} <span class="astrick">*</span></label>
                <input type="text" name="token" id="missed_chat_token" class="custom-input" autocomplete="off" value="{{ isset($data->token) ? hide_service_credential($data->token, $hide_credential) : ''}}" {{ $disabled }}>
                <p class="warning-text" style="width:100%;margin:.5rem 0px; padding-left: 25%"></p>
            </div>
        </div>
        <div class="buttons__all">
            @if(!$hide_credential)
            <button type="button" class="custom-button custom-button-green" id="cancel">Cancel</button>
            <button type="submit" class="custom-button custom-button-blue" id="missed_form_button">Submit</button>
            @endif
        </div>
    </div>
</div>
