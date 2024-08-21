@php
$languageClass = '';
if (Auth::user()->language === 'ar') {
    $languageClass = 'arabic';
}
@endphp

<div class="popup popup__container" id="chatdownload__popup" >
    <div class="popup__wrapper">
        <a class="close-btn close-btn-tags" ><i class="fas fa-times"></i></a>
        <div class="popup__wrapper__heading">{{default_trans($organizationId.'/permission.ui_elements_messages.chat_download', __('default/permission.ui_elements_messages.chat_download'))}}</div>
        
        <div class="popup__content flex__start">
        <form>
            @foreach(config('config.CHAT_DOWNLOAD') as $key=>$events)
               @php
			if(!empty($data->agent_wise_chat_download) && $data->agent_wise_chat_download == "true") {
               		$checked = 'checked';
               } else {
               		$checked = '';
               }
                @endphp
            <label class="checkbox__container {{ $languageClass }}">
                <input type="checkbox" class="chatdownnload" {{$checked}} id="chatdownload" name="chatdownload"  value={{$key}}>
                <span class="checkmark"></span>
                <span class="checkbox__title">{{$events}}</span> 
            </label>
            @endforeach
            <p class="warning-text warning-text-timer" style="width:100%"></p>
        <div class="buttons__all">
            <button type="button" class="custom-button custom-button-green" id="cancel">{{default_trans($organizationId.'/permission.ui_elements_messages.cancel', __('default/permission.ui_elements_messages.cancel'))}}</button>
            <button type="button" class="custom-button custom-button-blue" id="submit-chatdownload-setting-btn">{{default_trans($organizationId.'/permission.ui_elements_messages.submit', __('default/permission.ui_elements_messages.submit'))}}</button>
        </div>
        </form>
        </div>
    </div>
</div>
