@php
$languageClass = '';
if (Auth::user()->role_id !=1 && Auth::user()->language =="ar"){
    $languageClass = 'arabic';
}
@endphp

<div class="popup popup__container" id="chat_notifier__popup">
    <div class="popup__wrapper">
        <a class="close-btn {{ $languageClass }}" id="close-btn-tags"><i class="fas fa-times"></i></a>
        <div class="popup__wrapper__heading">{{default_trans($organizationId.'/permission.ui_elements_messages.chat_notifier', __('default/permission.ui_elements_messages.chat_notifier'))}}</div>
        <div class="popup__content">
            <div class="popup__timer__heading">
                {{default_trans($organizationId.'/permission.ui_elements_messages.please_select_time_for_notify_Chat', __('default/permission.ui_elements_messages.please_select_time_for_notify_Chat'))}}
            </div>
            <form>
            <div class="popup__timer__content">
                <div class="popup__timer__wrapper popup__permissions--addtags">
                    <label for="">{{default_trans($organizationId.'/permission.ui_elements_messages.hours', __('default/permission.ui_elements_messages.hours'))}}<span class="astrick">*</span></label>
                    <input name="hour" id="notifier-hour" value={{$data->hour}} class="custom-input notifier-input" type="number" min=0 max=23 />
                </div>
                <div class="popup__timer__wrapper popup__permissions--addtags">
                    <label for="">{{default_trans($organizationId.'/permission.ui_elements_messages.minutes', __('default/permission.ui_elements_messages.minutes'))}}<span class="astrick">*</span></label>
                    <input name="minute" id="notifier-minute" value={{$data->minute}} class="custom-input notifier-input" type="number" min=0 max=60 />
                </div>
                <div class="popup__timer__wrapper popup__permissions--addtags">
                    <label for="">{{default_trans($organizationId.'/permission.ui_elements_messages.seconds', __('default/permission.ui_elements_messages.seconds'))}}<span class="astrick">*</span></label>
                    <input name="second" id="notifier-second" value={{$data->second}} class="custom-input notifier-input" type="number" min=0 max=60 />
                </div>
            </div>
            <p class="warning-text warning-text-timer" ></p>
            <div class="buttons__all margin-top-2">
                <button type="button"  class="custom-button custom-button-green" id="cancel">{{default_trans($organizationId.'/permission.ui_elements_messages.cancel', __('default/permission.ui_elements_messages.cancel'))}}</button>
                <button type="button" id="update-chatnotifier-button" class="custom-button custom-button-blue">{{default_trans($organizationId.'/permission.ui_elements_messages.submit', __('default/permission.ui_elements_messages.submit'))}}</button>
            </div>
            </form>
        </div>
    </div>
</div>
