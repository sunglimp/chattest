@php
$disabled = '';
$hide_credential = 0;
if (Gate::allows('not-superadmin')) {
    $disabled = 'disabled';
    $hide_credential = 1;
}
@endphp
<div class="popup popup__container" id="email__popup" >
    <div class="popup__wrapper">
        <a class="close-btn close-btn-tags" ><i class="fas fa-times"></i></a>
        <div class="popup__wrapper__heading">{{default_trans($organizationId.'/permission.ui_elements_messages.email_configuration', __('default/permission.ui_elements_messages.email_configuration'))}}</div>

        <div class="popup__content justify-center">
        <form>
        <div class="popup__permissions--addtags" style="width:80%; flex-wrap:wrap" >
            <!-- <label class="width50 f-size-13">Service Provider</label> -->
            <select onchange="handleGroupServieType()" class="custom-input permissions-input" style="flex: 1; background: white;" id="provider_type" name="provider_type" {{ $disabled }}>
                <option selected disabled="">{{default_trans($organizationId.'/permission.ui_elements_messages.select_provider', __('default/permission.ui_elements_messages.select_provider'))}}</option>
                @foreach(config('constants.MAIL_SERVICE_PROVIDER') as $key=>$provider)
                    @if (isset($data->provider_type) &&  $data->provider_type == $key)
                    <option  selected value="{{ $key }}" >{{ $provider }}</option>
                    @else
                    <option  value="{{ $key }}" >{{ $provider }}</option>
                    @endif
                @endforeach
            </select>
            <p class="warning-text" style="width:100%;margin-top:.2rem;"></p>
        </div>
        <div class="popup__permissions--addtags" style="width:80%; flex-wrap:wrap" >
            <input type="text" placeholder="{{default_trans($organizationId.'/permission.ui_elements_messages.username', __('default/permission.ui_elements_messages.username'))}}" class="custom-input permissions-input" name="username" id="username"  value='@php if(isset($data->username)) echo hide_service_credential($data->username, $hide_credential);  @endphp' style="width:100%" {{ $disabled }}>
            <p class="warning-text" style="width:100%;margin-top:.2rem;"></p>
        </div>
            <div class="popup__permissions--addtags" style="width:80%; flex-wrap:wrap" >
            <input type="text" placeholder="{{default_trans($organizationId.'/permission.ui_elements_messages.password', __('default/permission.ui_elements_messages.password'))}}" class="custom-input permissions-input" name="password" id="password"  value='@php if(isset($data->password)) echo hide_service_credential($data->password, $hide_credential);  @endphp' style="width:100%" {{ $disabled }}>
            <p class="warning-text" style="width:100%;margin-top:.2rem;"></p>
        </div><div class="popup__permissions--addtags" style="width:80%; flex-wrap:wrap" >
            <input type="text" placeholder="{{default_trans($organizationId.'/permission.ui_elements_messages.host', __('default/permission.ui_elements_messages.host'))}}" class="custom-input permissions-input" name="host" id="host"  value='@php if(isset($data->host)) echo hide_service_credential($data->host, $hide_credential);  @endphp' style="width:100%" {{ $disabled }}>
            <p class="warning-text" style="width:100%;margin-top:.2rem;"></p>
        </div><div class="popup__permissions--addtags" style="width:80%; flex-wrap:wrap" >
            <input type="text" placeholder="{{default_trans($organizationId.'/permission.ui_elements_messages.port', __('default/permission.ui_elements_messages.port'))}}" class="custom-input permissions-input" name="port" id="port"  value='@php if(isset($data->port)) echo hide_service_credential($data->port, $hide_credential);  @endphp' style="width:100%" {{ $disabled }}>
            <p class="warning-text" style="width:100%;margin-top:.2rem;"></p>
        </div>
            <div class="popup__permissions--addtags" style="width:80%; flex-wrap:wrap" >
            <input type="text" placeholder="{{default_trans($organizationId.'/permission.ui_elements_messages.encryption', __('default/permission.ui_elements_messages.encryption'))}}" class="custom-input permissions-input" name="encryption" id="encryption"  value='@php if(isset($data->encryption)) echo hide_service_credential($data->encryption, $hide_credential);  @endphp'  style="width:100%" {{ $disabled }} {{ $hide_credential ? 'readonly' :'' }}>
            <p class="warning-text" style="width:100%;margin-top:.2rem;"></p>
        </div>
        <div class="popup__permissions--addtags" style="width:80%; flex-wrap:wrap" >
            <input type="text" placeholder="{{default_trans($organizationId.'/permission.ui_elements_messages.from_email', __('default/permission.ui_elements_messages.from_email'))}}" class="custom-input permissions-input" name="from_email" id="from_email"  value='@php if(isset($data->from_email)) echo $data->from_email;  @endphp' style="width:100%" {{ $disabled }}>
            <p class="warning-text" style="width:100%;margin-top:.2rem;"></p>
        </div>
        <div class="buttons__all">
            @if(!$hide_credential)
            <button type=button class="custom-button custom-button-green" id="cancel">{{default_trans($organizationId.'/permission.ui_elements_messages.cancel', __('default/permission.ui_elements_messages.cancel'))}}</button>
            <button type=button class="custom-button custom-button-blue" id="email_button">{{default_trans($organizationId.'/permission.ui_elements_messages.submit', __('default/permission.ui_elements_messages.submit'))}}</button>
            @endif
        </div>
        </form>
        </div>


    </div>
</div>