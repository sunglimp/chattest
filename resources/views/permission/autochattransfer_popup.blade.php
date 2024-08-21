@php
$languageClass = '';
if (Auth::user()->role_id !=1 && Auth::user()->language =="ar"){
    $languageClass = 'arabic';
}
@endphp

<div class="popup popup__container" id="auto-chat-transfer__popup">
    <div class="popup__wrapper">
        <a class="close-btn {{ $languageClass }}" id="close-btn-tags"><i class="fas fa-times"></i></a>
        <div class="popup__wrapper__heading">{{default_trans($organizationId.'/permission.ui_elements_messages.auto_chat_transfer', __('default/permission.ui_elements_messages.auto_chat_transfer'))}}</div>
        <div class="popup__content">
            
            <form>
            <div class="popup__full">
                <div class="popup__left__content">
                    <div class="popup__timer__heading">
                        {{default_trans($organizationId.'/permission.ui_elements_messages.time_for_auto_transfer_chat', __('default/permission.ui_elements_messages.time_for_auto_transfer_chat'))}}
                    </div>
                    <div class="popup__timer__heading">  
                        {{default_trans($organizationId.'/permission.ui_elements_messages.limit_for_transfer_chat', __('default/permission.ui_elements_messages.limit_for_transfer_chat'))}}
                    </div>
                </div>
                <div class="popup__right__content">
                    <div class="popup__full">
                        <div class="popup__timer__wrapper popup__permissions--addtags">
                            <label for="">{{default_trans($organizationId.'/permission.ui_elements_messages.hours', __('default/permission.ui_elements_messages.hours'))}}<span class="astrick">*</span></label>
                            <input id="autotransfer-hour" name="hour"  value={{$data->hour}} class="custom-input autotransfer-input" type="number" min=0 max=23 />
                        </div>
                        <div class="popup__timer__wrapper popup__permissions--addtags">
                            <label for="">{{default_trans($organizationId.'/permission.ui_elements_messages.minutes', __('default/permission.ui_elements_messages.minutes'))}}<span class="astrick">*</span></label>
                            <input id="autotransfer-minute" name="minute" value={{$data->minute}} class="custom-input autotransfer-input" type="number" min=0 max=60 />
                        </div>
                        <div class="popup__timer__wrapper popup__permissions--addtags">
                            <label for="">{{default_trans($organizationId.'/permission.ui_elements_messages.seconds', __('default/permission.ui_elements_messages.seconds'))}}<span class="astrick">*</span></label>
                            <input  id="autotransfer-second" name="second" value={{$data->second}} class="custom-input autotransfer-input" type="number" min=0 max=60 />
                        </div>                        
                    </div>
                    <p class="warning-text warning-text-timer" style="width: 75%; margin-top: 51px;"></p>
                    <div class="popup__full">
                        <div class="popup__timer__content mar-l-0 popup__permissions--addtags">
                            <div class="mar-t-7">
                                <input id="autotransfer-limit" name="transfer_limit"  value="{{$data->transfer_limit ?? 2}}" class="custom-input autotransfer-input" type="number" min=2 max=20 style="width: 50px;"/>
                                <span style="font-size: 10px;">( {{default_trans($organizationId.'/permission.ui_elements_messages.max_transfer_limit_note', __('default/permission.ui_elements_messages.max_transfer_limit_note'))}} )</span>
                            </div>
                        </div> 
                    </div>
                    <p class="warning-text warning-text-timer"  style="width: 75%; padding-top: 5px;"></p>
                    
                </div>
            </div>   
            
            <div class="buttons__all margin-top-2">
                <button type="button"  class="custom-button custom-button-green" id="cancel">{{default_trans($organizationId.'/permission.ui_elements_messages.cancel', __('default/permission.ui_elements_messages.cancel'))}}</button>
                <button type="button" id="update-autochat-button" class="custom-button custom-button-blue">{{default_trans($organizationId.'/permission.ui_elements_messages.submit', __('default/permission.ui_elements_messages.submit'))}}</button>
            </div>
            </form>
        </div>
    </div>
</div>
