@php
$languageClass = '';
if (Auth::user()->role_id !=1 && Auth::user()->language =="ar"){
    $languageClass = 'arabic';
}
@endphp

<div class="popup popup__container" id="timeout__popup">
    <div class="popup__wrapper">
        <a class="close-btn {{ $languageClass }}" id="close-btn-tags"><i class="fas fa-times"></i></a>
        <div class="popup__wrapper__heading">{{default_trans($organizationId.'/permission.ui_elements_messages.chat_timeout', __('default/permission.ui_elements_messages.chat_timeout'))}}</div>
        <div class="popup__content">
            <div class="popup__timer__heading">
                {{default_trans($organizationId.'/permission.ui_elements_messages.please_select_time_for_timeout', __('default/permission.ui_elements_messages.please_select_time_for_timeout'))}}
            </div>
            <div class="popup__timer__content">
                <div class="popup__timer__wrapper popup__permissions--addtags">
                    <label for="">{{default_trans($organizationId.'/permission.ui_elements_messages.hours', __('default/permission.ui_elements_messages.hours'))}}<span class="astrick">*</span></label>
                    <input id="timeout-hour" name="hour" value={{$data->hour}} class="custom-input timeout-input" type="number"  />
                </div>
                <div class="popup__timer__wrapper popup__permissions--addtags">
                    <label for="">{{default_trans($organizationId.'/permission.ui_elements_messages.minutes', __('default/permission.ui_elements_messages.minutes'))}}<span class="astrick">*</span></label>
                    <input id="timeout-minute" name="minute" value={{$data->minute}} class="custom-input timeout-input" type="number"  />
                </div>
                <div class="popup__timer__wrapper popup__permissions--addtags">
                    <label for="">{{default_trans($organizationId.'/permission.ui_elements_messages.seconds', __('default/permission.ui_elements_messages.seconds'))}}<span class="astrick">*</span></label>
                    <input  id="timeout-second" name="second" value={{$data->second}} class="custom-input timeout-input" type="number"  />
                </div>
            </div>
            <p class="warning-text warning-text-timer" ></p>
            <div class="buttons__all margin-top-2">
                <button type="button" class="custom-button custom-button-green" id="cancel">{{default_trans($organizationId.'/permission.ui_elements_messages.cancel', __('default/permission.ui_elements_messages.cancel'))}}</button>
                <button type="button" id="update-timeout-button" class="custom-button custom-button-blue">{{default_trans($organizationId.'/permission.ui_elements_messages.submit', __('default/permission.ui_elements_messages.submit'))}}</button>
            </div>
        </div>
    </div>
</div>
