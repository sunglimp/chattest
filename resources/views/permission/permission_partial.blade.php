
@php
$isSettingEnabled = 0;
$languageClass = '';
if (Auth::user()->language === 'ar') {
    $languageClass = 'arabic';
}
@endphp

<div class="content__wrapper margin-top-2">
    <div class="margin-top-2">
        <table class="table table-sorting {{ $languageClass }}" id="myTable1">
            <thead>
                <tr>
                    <th style="width: 25%">{{default_trans($organizationId.'/permission.ui_elements_messages.permissions', __('default/permission.ui_elements_messages.permissions'))}}</th>
                    @foreach($user_roles as $user)
                    <th style="width: 20%">{{default_trans($organizationId.'/permission.ui_elements_messages.'.$user->slug,__('default/permission.ui_elements_messages.'.$user->slug))}}</th>
                    @endforeach
                    <th style="width: 15%">{{default_trans($organizationId.'/permission.ui_elements_messages.action', __('default/permission.ui_elements_messages.action'))}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($permission_list as $permission)
                @php $isSettingEnabled = 0; @endphp
                <tr>
                    <td>{{default_trans($organizationId.'/permission.ui_elements_messages.'.$permission->slug,__('default/permission.ui_elements_messages.'.$permission->slug))}}</td>
                    @foreach($user_roles as $user)
                    @php $checked = ''; @endphp
                    @if($permission->id == 1)
                    @php $class = "master-switch";
                    $id = $user->slug @endphp
                    @else
                    @php $class = $user->slug."-switch ";
                    $id = '';
                    @endphp
                    @endif
                    @for($i=0;$i< count($organization_permission);$i++)
                    @if( $organization_permission[$i]['role_id'] == $user->id && $organization_permission[$i]['permission_id'] == $permission->id)
                    @php $isSettingEnabled = 1 @endphp
                    @php $checked = 'checked';
                    @endphp
                    @endif
                    @endfor
                    @if(
                        ($permission->id == config('constants.PERMISSION.SUPERVISE-TIP-OFF') && $user->id == config('constants.user.role.associate')) ||
                        (($permission->id == config('constants.PERMISSION.SNEAK') && !in_array($user->id, config('constants.ADMIN_ROLE_IDS')))) ||
                        (($permission->id == config('constants.PERMISSION.SESSION_TIMEOUT') && !in_array($user->id, config('constants.ADMIN_ROLE_IDS'))))
                    )
                    @php $class = $user->slug."-switch disabled"; $checked = ''; @endphp
                    @endif

                    @if ($permission->id == config('constants.PERMISSION.SESSION_TIMEOUT') && config('constants.user.role.admin') == Auth()->user()->role_id)
                    @php $isSettingEnabled = 1; @endphp
                    @endif

                    @if(($permission->id == config('constants.PERMISSION.OFFLINE-FORM')))
                    @php $class = $user->slug."-switch disabled"; $checked = 'checked'; $isSettingEnabled = 1; @endphp
                    @endif

                    <!-- Customer information Enabled by Default -->
                    {{-- @if(($permission->id == config('constants.PERMISSION.CUSTOMER-INFORMATION')))
                    @php $class = $user->slug."-switch disabled"; $checked = 'checked'; $isSettingEnabled = 1; @endphp
                    @endif --}}
                    <!-- Customer information Enabled by Default -->

                    @if(isset($user_counts[$user->id])&& $user->id != config('constants.user.role.admin') && $user_counts[$user->id] > 0 && $permission->id == config('constants.PERMISSION.ROLE'))
                    @php $class = $user->slug."-switch disabled";
                    $checked = 'checked';
                    @endphp
                    @endif
                    <td>
                        <label class="switch {{$class}}"  id="{{$id}}">
                            <input type="checkbox" autocomplete="off" name="permission[{{$user->id}}][{{$permission->id}}]" {{$checked}} value="1" onchange="enableDisableSetting(this)">
                            <span class="slider round"></span>
                        </label>
                    </td>
                    @endforeach
                    <td id="setting-action">
                        <!-- if permission is not assigned and disabled attr -->
                        <span class="icon icon--warning popup-btn @if($isSettingEnabled == 0 || $permission->disabled == 1) disabled @endif" id="{{$permission->slug}}" data-value="{{$permission->id}}" data-disabled="{{$permission->disabled}}">
                            <i class="fas fa-cog"></i>
                            <input type="hidden" value={{$permission->id}}>
                        </span>
                    </td>
                </tr>

                @endforeach
            </tbody>
        </table>
        <div class="buttons__all justify-end">
            <a type="button" class="custom-button custom-button-blue" id="add-organization-permission">{{default_trans($organizationId.'/permission.ui_elements_messages.save', __('default/permission.ui_elements_messages.save'))}}</a>
        </div>

        <div id="add-group-popup"></div>
        <div id="upload-attachment-popup"></div>
        <div id="chat-feedback-popup"></div>
        <div id="email-popup"></div>
        <div id="ban-user-popup"></div>
        <div id="tms-key-popup"></div>
        <div id="notification-popup"></div>
        <div id="chatdownload-popup"></div>
        <div id="session-timeout-popup"></div>
        <div id="archive-chat-popup"></div>
        <div id="missed-chat-popup"></div>
        <div id="customer-information-popup"></div>

        <div class="popup popup__container" id="timer__popup">
            <div class="popup__wrapper">
                <a class="close-btn {{ $languageClass }}" id="close-btn-tags"><i class="fas fa-times"></i></a>
                <div class="popup__wrapper__heading">Chat Notifier</div>
                <div class="popup__content">
                    <div class="popup__timer__heading">
                        <span>Set your time</span>
                        Pick a time of the day, when you would like to recieve reminders
                    </div>
                    <div class="popup__timer__content">
                        <div class="popup__timer__wrapper">
                            <label for="">Hours<span class="astrick">*</span></label>
                            <input class="custom-input" type="number" min=0 max=23 />
                        </div>
                        <div class="popup__timer__wrapper">
                            <label for="">Minutes<span class="astrick">*</span></label>
                            <input class="custom-input" type="number" min=0 max=60 />
                        </div>
                        <div class="popup__timer__wrapper">
                            <label for="">Seconds<span class="astrick">*</span></label>
                            <input class="custom-input" type="number" min=0 max=60 />
                        </div>
                    </div>
                    <div class="buttons__all">
                        <button class="custom-button custom-button-green" id="cancel">Cancel</button>
                        <button class="custom-button custom-button-blue">Submit</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
