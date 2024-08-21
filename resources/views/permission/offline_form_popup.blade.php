@php
$wa_checked = $qc_checked = $email_checked = $bot_transcript_checked = $in_session_push_checked = $out_session_push_checked = $send_email_on_qc_checked = '' ;
$wa_display = $email_display = 'display:none';
$view_disabled = 'viewDisabled';
$free_view_disabled = 'viewDisabled';
$qc = $data->qc ?? '';
$session_push = $data->whatsapp->sessionPush ?? '0';
$wa = $data->whatsapp ?? [];
$email = $data->email ?? [];
if($qc=="true"){ $qc_checked = 'checked="checked"'; $view_disabled = ''; }
if(intval($session_push) === 1 || intval($session_push) === 3){ $in_session_push_checked = 'checked="checked"'; $free_view_disabled = ''; }
if(intval($session_push) === 2 || intval($session_push) === 3){ $out_session_push_checked = 'checked="checked"'; }
if(!empty($wa)) { $wa_checked = 'checked="checked"'; $wa_display = 'display: block'; }
if(!empty($email)) {
    $email_checked = 'checked="checked"'; $email_display = 'display: block';
    if ($data->email->botTranscript=='true') { $bot_transcript_checked = 'checked="checked"'; }
    if (isset($data->email->sendEmailOnQC) && $data->email->sendEmailOnQC=='true') { $send_email_on_qc_checked = 'checked="checked"'; }
}
$emailIds = $data->email->emailId ?? [];
$disabled = '';
$readonly = '';
$hide_credential = 0;
if (Gate::allows('not-superadmin')) {
    $disabled = 'disabled';
    $readonly = 'readonly';
    $hide_credential = 1;
}
@endphp

<div class="popup popup__container" id="offline_form__popup">
    <div class="popup__wrapper" style="max-height: 600px; overflow-y: auto;">
        <a class="close-btn close-btn-tags"><i class="fas fa-times"></i></a>
        <div class="popup__wrapper__heading">{{default_trans($organizationId.'/permission.ui_elements_messages.offline-form', __('default/permission.ui_elements_messages.offline-form'))}}</div>

        <div class="popup__content justify-center">
            <form id="offline_popup_form_element">
                <div class="canned__add--box message-class">
                    <label class="mar-b-5" for="">{{default_trans($organizationId.'/permission.ui_elements_messages.message', __('default/permission.ui_elements_messages.message'))}}</label>
                    <textarea id="message" name="message" class="custom-input" cols="10" rows="3" id="response"
                        autocomplete="off">{{$data->message ??''}} </textarea>
                   <p class="warning-text" style="width:100%;margin-top:.2rem;"></p>
                </div>
                <div class="canned__add--box message-class">
                    <label class="mar-b-5" for="">{{default_trans($organizationId.'/permission.ui_elements_messages.thank_you_message', __('default/permission.ui_elements_messages.thank_you_message'))}}</label>
                    <textarea id="thank_you_message" name="thank_you_message" class="custom-input" cols="10" rows="3"
                        autocomplete="off">{{$data->thank_you_message ??''}} </textarea>
                   <p class="warning-text" style="width:100%;margin-top:.2rem;"></p>
                </div>
                <div class="canned__add--radio-container">
                    <label class="mar-b-5 f-size-14" for="">{{default_trans($organizationId.'/permission.ui_elements_messages.offline_querries', __('default/permission.ui_elements_messages.offline_querries'))}}</label>
                    <div class="stv-radio-tabs-wrapper" style="width: 100%; margin: 10px 0px;">
                        <input type="radio" class="stv-radio-tab" name="offline_query_type" id="all" value=1 checked/>
                               <label for="all">
                            <!--        Internal-->
                            {{default_trans($organizationId.'/permission.ui_elements_messages.all', __('default/permission.ui_elements_messages.all'))}}

                        </label>
                        <input type="radio" class="stv-radio-tab" name="offline_query_type" value=2 id="group_wise" @if(isset($data->offline_query_type)) @if($data->offline_query_type == 2) checked @endif @endif/>
                        <label for="group_wise">
                            <!--        External-->
                            {{default_trans($organizationId.'/permission.ui_elements_messages.group_wise', __('default/permission.ui_elements_messages.group_wise'))}}
                        </label>
                    </div>
                </div>
                <div class="canned__add--radio-container">
                    <table class="table">
                        <tr>
                            <td>
                                <span>{{default_trans($organizationId.'/permission.ui_elements_messages.qc', __('default/permission.ui_elements_messages.qc'))}}</span>
                                <label class="switch admin-switch " id="">
                                    <input type="checkbox" autocomplete="off" name="qc_slider" value="true" {!!$qc_checked!!} {{ $disabled }}>
                                    <span class="slider round"></span>
                                </label>
                            </td>
                            <td>
                                <span>{{default_trans($organizationId.'/permission.ui_elements_messages.wa_push', __('default/permission.ui_elements_messages.wa_push'))}}</span>
                                <label class="switch admin-switch {!!$view_disabled!!} " id="">
                                    <input type="checkbox" autocomplete="off" name="wa_push_slider" {!!$wa_checked!!} {{ $disabled }}>
                                    <span class="slider round"></span>
                                </label>
                            </td>
                            <td>
                                <span>{{default_trans($organizationId.'/permission.ui_elements_messages.email', __('default/permission.ui_elements_messages.email'))}}</span>

                                <label class="switch admin-switch {!!$view_disabled!!} " id="">
                                    <input type="checkbox" autocomplete="off" name="email_slider" {!!$email_checked!!} {{ $disabled }}>
                                    <span class="slider round"></span>
                                </label>
                            </td>
                            <td>
                                <span>{{default_trans($organizationId.'/permission.ui_elements_messages.tms', __('default/permission.ui_elements_messages.tms'))}}</span>
                                <label class="switch admin-switch viewDisabled" id="">
                                    <input type="checkbox" autocomplete="off" name="tms_slider" {{ $disabled }}>
                                    <span class="slider round"></span>
                                </label>
                            </td>
                        </tr>
                    </table>
                    <div class="wa_push" style="{!!$wa_display!!}">
                        <div class="canned__add--radio-container">
                            <table class="table">
                                <tr>
                                    <td  style="background: #dcdcdc;">
                                        <span>{{default_trans($organizationId.'/permission.ui_elements_messages.in_session_push', __('default/permission.ui_elements_messages.in_session_push'))}}</span>
                                        <label class="switch admin-switch " id="in_session_push">
                                            <input type="checkbox" autocomplete="off" name="session_push" {!!$in_session_push_checked!!} {{ $disabled }}>
                                            <span class="slider round"></span>
                                        </label>
                                    </td>
                                    <td  style="background: #dcdcdc;">
                                        <span>{{default_trans($organizationId.'/permission.ui_elements_messages.out_session_push', __('default/permission.ui_elements_messages.out_session_push'))}}</span>
                                        <label class="switch admin-switch" id="out_session_push">
                                            <input type="checkbox" autocomplete="off" {!!$out_session_push_checked!!} {{ $disabled }}>
                                            <span class="slider round"></span>
                                        </label>
                                    </td>
                                </tr>
                            </table>
                            <p id="in_session_error" class="warning-text" style="width:100%;margin-top:.2rem;"></p>
                        </div>
                        <div class="popup__content message-class">
                            <label>{{default_trans($organizationId.'/permission.ui_elements_messages.api', __('default/permission.ui_elements_messages.api'))}} </label>
                            <input type="text" name="api" class="custom-input" autocomplete="off" value="{{ isset($data->whatsapp->api) ? hide_service_credential($data->whatsapp->api, $hide_credential) : ''}}" {{ $readonly }}>
                            <p class="warning-text" style="width:100%;margin-top:.2rem;"></p>
                        </div>
                        <div class="popup__content message-class free-in-session-push {!!$free_view_disabled!!}">
                            <label>{{default_trans($organizationId.'/permission.ui_elements_messages.free_api', __('default/permission.ui_elements_messages.free_api'))}} </label>
                            <input type="text" name="free_api" class="custom-input" autocomplete="off" value="{{ isset($data->whatsapp->freeApi) ? hide_service_credential($data->whatsapp->freeApi, $hide_credential) : ''}}" {{ $readonly }}>
                            <p class="warning-text" style="width:100%;margin-top:.2rem;"></p>
                        </div>
                        <div class="popup__content message-class">
                            <label>{{default_trans($organizationId.'/permission.ui_elements_messages.template_id', __('default/permission.ui_elements_messages.template_id'))}}</label>
                            <input type="text" name="template_id" class="custom-input" autocomplete="off" value="{{ isset($data->whatsapp->templateId) ? $data->whatsapp->templateId : ''}}">
                            <p class="warning-text" style="width:100%;margin-top:.2rem;"></p>
                        </div>
                        <div class="popup__content message-class  free-in-session-push {!!$free_view_disabled!!}">
                            <label>{{default_trans($organizationId.'/permission.ui_elements_messages.free_template_id', __('default/permission.ui_elements_messages.free_template_id'))}}</label>
                            <textarea name="free_template_id" class="custom-input" autocomplete="off" value="" style=" width: 70%; " rows="5">{{ isset($data->whatsapp->freeTemplateId) ? $data->whatsapp->freeTemplateId : ''}}</textarea>
                            <p class="warning-text" style="width:100%;margin-top:.2rem;"></p>
                        </div>
                        <div class="popup__content message-class">
                            <label>{{default_trans($organizationId.'/permission.ui_elements_messages.bot_id', __('default/permission.ui_elements_messages.bot_id'))}}</label>
                            <input type="text" name="bot_id" class="custom-input" autocomplete="off" value="{{ isset($data->whatsapp->botId) ? hide_service_credential($data->whatsapp->botId, $hide_credential) : ''}}" {{ $readonly }}>
                            <p class="warning-text" style="width:100%;margin-top:.2rem;"></p>
                        </div>
                        <div class="popup__content message-class">
                            <label>{{default_trans($organizationId.'/permission.ui_elements_messages.token', __('default/permission.ui_elements_messages.token'))}} </label>
                            <input type="text" name="token" class="custom-input" autocomplete="off" value="{{ isset($data->whatsapp->token) ? hide_service_credential($data->whatsapp->token, $hide_credential) : ''}}" {{ $readonly }}>
                            <p class="warning-text" style="width:100%;margin-top:.2rem;"></p>
                        </div>
                    </div>
                    <div class="email" style="{!! $email_display !!}">
                        <div class="popup__content--wrap message-class">
                            <label>
                                <input type="checkbox" name="send_email_on_qc" autocomplete="off" value="true" {!! $send_email_on_qc_checked !!} {{ $disabled }}>
                                {{default_trans($organizationId.'/permission.ui_elements_messages.send_email_on_qc', __('default/permission.ui_elements_messages.send_email_on_qc'))}}
                                <p class="warning-text" style="width:100%;margin-top:.2rem;padding:0px;"></p>
                            </label>
                        </div>
                        <div class="popup__content--wrap width60 f-left message-class">
                            <label class="mar-b-5">{{default_trans($organizationId.'/permission.ui_elements_messages.email', __('default/permission.ui_elements_messages.email'))}}</label>
                            <input type="text" name="email_id" class="custom-input" autocomplete="off" value="{{implode(',',$emailIds)}}" {{ $readonly }}>
                            <p class="warning-text" style="width:100%;margin-top:.2rem;padding:0px;"></p>
                        </div>
                        <div class="popup__content--wrap width40 f-left">
                            <label>
                                <input type="checkbox" name="bot_transcript" autocomplete="off" value="true" {!! $bot_transcript_checked !!} {{ $disabled }}>
                                {{default_trans($organizationId.'/permission.ui_elements_messages.add_bot_transcript', __('default/permission.ui_elements_messages.add_bot_transcript'))}}
                            </label>
                        </div>
                        <div class="popup__content--wrap message-class">
                            <label class="mar-b-5">{{default_trans($organizationId.'/permission.ui_elements_messages.subject', __('default/permission.ui_elements_messages.subject'))}} </label>
                            <input type="text" name="subject" class="custom-input" autocomplete="off" value="{{$data->email->subject ?? ''}}" {{ $readonly }}>
                            <p class="warning-text" style="width:100%;margin-top:.2rem;padding:0px;"></p>
                        </div>
                        <div class="popup__content--wrap message-class">
                            <textarea rows="6" id="message" name="email_body" class="custom-input" cols="10" rows="3"
                                id="response" autocomplete="off" {{ $readonly }}>{{$data->email->emailBody ?? ''}}</textarea>
                            <p class="warning-text" style="width:100%;margin-top:.2rem;padding:0px;"></p>
                        </div>
                    </div>
                </div>
                <div class="buttons__all">
                    <button type=button class="custom-button custom-button-green" id="cancel">{{default_trans($organizationId.'/permission.ui_elements_messages.cancel', __('default/permission.ui_elements_messages.cancel'))}}</button>
                    <button type=submit class="custom-button custom-button-blue" id="offline_form_button">{{default_trans($organizationId.'/permission.ui_elements_messages.submit', __('default/permission.ui_elements_messages.submit'))}}</button>
                </div>
            </form>
        </div>
    </div>
</div>