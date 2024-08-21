
<div class="popup popup__container" id="session_timeout__popup">
    <div class="popup__wrapper">
        <a class="close-btn" id="close-btn-tags"><i class="fas fa-times"></i></a>
        <div class="popup__wrapper__heading">{{default_trans($organizationId.'/permission.ui_elements_messages.session_timeout', __('default/permission.ui_elements_messages.session_timeout'))}}</div>
        <div class="popup__content">
            <div class="popup__timer__heading">
                {{default_trans($organizationId.'/permission.ui_elements_messages.select_time_session_timeout', __('default/permission.ui_elements_messages.select_time_session_timeout'))}}
            </div>
            <div class="popup__timer__content">
                @if(Gate::allows('superadmin'))
                <div class="popup__timer__wrapper popup__permissions--addtags">
                    <label for="">Max Hrs<span class="astrick">*</span></label>
                    <input id="max-hours" name="max_hours" value="{{isset($data->max_hours) ? $data->max_hours :(config('constants.LAST_ACTIVITY_SESSION_TIME')/60)}}" class="custom-input timeout-input" type="number"  />
                </div>
                @else
                <input id="max-hours" name="max_hours" value="{{isset($data->max_hours) ? $data->max_hours :(config('constants.LAST_ACTIVITY_SESSION_TIME')/60)}}" class="custom-input timeout-input" type="hidden"/>
                @endif
                <div class="popup__timer__wrapper popup__permissions--addtags">
                    <label for="">{{default_trans($organizationId.'/permission.ui_elements_messages.hours', __('default/permission.ui_elements_messages.hours'))}}<span class="astrick">*</span></label>
                    <input id="timeout-hour" name="hour" value="{{isset($data->hour) ? $data->hour : $data['max_hour']}}" class="custom-input timeout-input" type="number"  />
                </div>
                <div class="popup__timer__wrapper popup__permissions--addtags">
                    <label for="">{{default_trans($organizationId.'/permission.ui_elements_messages.minutes', __('default/permission.ui_elements_messages.minutes'))}}<span class="astrick">*</span></label>
                    <input id="timeout-minute" name="minute" value="{{isset($data->minute) ? $data->minute : $data['max_minute']}}" class="custom-input timeout-input" type="number"  />
                </div>
                <div class="popup__timer__wrapper popup__permissions--addtags">
                    <label for="">{{default_trans($organizationId.'/permission.ui_elements_messages.seconds', __('default/permission.ui_elements_messages.seconds'))}}<span class="astrick">*</span></label>
                    <input  id="timeout-second" name="second" value="{{isset($data->second) ? $data->second : 0}}" class="custom-input timeout-input" type="number"  />
                </div>
            </div>
            <p class="warning-text warning-text-timer" ></p>
            <div class="buttons__all margin-top-2">
                <button type="button" class="custom-button custom-button-green" id="cancel">{{default_trans($organizationId.'/permission.ui_elements_messages.cancel', __('default/permission.ui_elements_messages.cancel'))}}</button>
                <button type="button" id="update-session-timeout-button" class="custom-button custom-button-blue">{{default_trans($organizationId.'/permission.ui_elements_messages.submit', __('default/permission.ui_elements_messages.submit'))}}</button>
            </div>
        </div>
    </div>
</div>
