
<div class="popup__content">
    <div class="popup__content--wrap">
        <label>{{default_trans($organizationId.'/user_list.ui_elements_messages.add_full_name', __('default/user_list.ui_elements_messages.add_full_name'))}}<span class="astrick">*</span></label>
        <input type="text" name="name" class="custom-input" />
    </div>
    <div class="popup__content--wrap">
        <label>{{default_trans($organizationId.'/user_list.ui_elements_messages.add_gender', __('default/user_list.ui_elements_messages.add_gender'))}} <span class="astrick">*</span></label>
        <div class="stv-radio-tabs-wrapper">
            <input type="radio" class="stv-radio-tab" name="gender" value="male" id="male"
                   checked="checked" />
            <label for="male">{{default_trans($organizationId.'/user_list.ui_elements_messages.male_gender', __('default/user_list.ui_elements_messages.male_gender'))}} </label>
            <input type="radio" class="stv-radio-tab" name="gender" value="female" id="female" />
            <label for="female">{{default_trans($organizationId.'/user_list.ui_elements_messages.female_gender', __('default/user_list.ui_elements_messages.female_gender'))}}</label>
        </div>
    </div>

    <div class="popup__content--wrap">
        <label>{{default_trans($organizationId.'/user_list.ui_elements_messages.email', __('default/user_list.ui_elements_messages.email'))}}<span class="astrick">*</span></label>
        <input type="text" name="email" class="custom-input" />
    </div>
    <div class="popup__content--wrap">
        <label>{{default_trans($organizationId.'/user_list.ui_elements_messages.password', __('default/user_list.ui_elements_messages.password'))}}</label>
        <input type="password" name="password" class="custom-input" />
    </div>
    <div class="popup__content--wrap">
        <label>{{default_trans($organizationId.'/user_list.ui_elements_messages.mobile', __('default/user_list.ui_elements_messages.mobile'))}}<span class="astrick">*</span></label>
        <input type="text" name="mobile_number" class="custom-input" />
    </div>
    <div class="popup__content--wrap">
        <label>{{default_trans($organizationId.'/user_list.ui_elements_messages.role', __('default/user_list.ui_elements_messages.role'))}} <span class="astrick">*</span></label>
        <div class="select-custom no-shadow">
            <select name="role_id" id="select-role">
                <option disabled selected>{{default_trans($organizationId.'/user_list.ui_elements_messages.select_role', __('default/user_list.ui_elements_messages.select_role'))}}</option>
                @foreach ($user_roles as $type)
                    <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="popup__content--wrap">
        <label>{{default_trans($organizationId.'/user_list.ui_elements_messages.concurrent_chats', __('default/user_list.ui_elements_messages.concurrent_chats'))}}</label>
        <input type="text" name="no_of_chats" id="no_of_chats" class="custom-input" />
    </div>
    <div class="popup__content--wrap">
        <label>{{default_trans($organizationId.'/user_list.ui_elements_messages.reporting_manager', __('default/user_list.ui_elements_messages.reporting_manager'))}}</label>
        <div class="select-custom no-shadow" >
            <div id="add-report-to">
                <select name="report_to" id="report-to" class="report-to">
                    <option disabled selected> {{default_trans($organizationId.'/user_list.ui_elements_messages.select_manager', __('default/user_list.ui_elements_messages.select_manager'))}} </option>
                </select>
            </div>
        </div>
    </div>
    <div class="popup__content--wrap">
        <label>{{default_trans($organizationId.'/user_list.ui_elements_messages.time_zone', __('default/user_list.ui_elements_messages.time_zone'))}}</label>
        <div class="select-custom time_zone no-shadow">
            <select name="timezone" class="dropdown-search">
                <option disabled selected="" >{{default_trans($organizationId.'/user_list.ui_elements_messages.select_timezone', __('default/user_list.ui_elements_messages.select_timezone'))}}</option>
                @foreach($timezone_list as $k=>$v)

                @if($k == $organization_timezone)
                	<option selected
                    value="{{ $k }}"
                    >{{ $k.'('.$v.')' }}</option>
                  @else
                	<option
                    value="{{ $k }}"
                    >{{ $k.'('.$v.')' }}</option>
                    @endif
                @endforeach
            </select>
        </div>
    </div>
    <div class="popup__content--wrap">
        <label>{{default_trans($organizationId.'/user_list.ui_elements_messages.group', __('default/user_list.ui_elements_messages.group'))}}</label>
        <div class="select-custom">

            @php $setDisableClass = count($groups) ?"":"group-disabled";  @endphp
            <select   multiple="multiple" id="group" name="group[]" class= "{{$setDisableClass}} groupMultiple">
            @foreach($groups as $group)
                <option value="{{ $group['id'] }}">{{ $group['name']}}</option>
                @endforeach
            </select>
        </div>
        @php
        if (!count($groups))
        {
            echo "<input type='hidden' name= 'group[]' value = '{$default_group->id}'>";
        }
        @endphp

    </div>
    
    <div class="popup__content--wrap">
        <label>{{default_trans($organizationId.'/user_list.ui_elements_messages.lang', __('default/user_list.ui_elements_messages.lang'))}}</label>
        <div class="select-custom no-shadow">
            <select name="language" id="select-language">
                @foreach ($org_languages as $language=>$label)
                	 @if(count($org_languages) == 1)
                	 	<option selected value="{{ $language }}">{{ $label }}</option>
               		 @else
                    	<option value="{{ $language }}">{{ $label }}</option>
                    @endif
                @endforeach
            </select>
        </div>
    </div>
    <div class="popup__content--wrap">
        <label>{{default_trans($organizationId.'/user_list.ui_elements_messages.profile_pic', __('default/user_list.ui_elements_messages.profile_pic'))}}</label>
        <label for="input-file" class="file-uploader">
            <i class="far fa-file-image"></i>
            <span class="file-name">{{default_trans($organizationId.'/user_list.ui_elements_messages.drag_upload_img', __('default/user_list.ui_elements_messages.drag_upload_img'))}}</span>
            <span class="file-delete"><i class="fas fa-times"></i></span>
        </label>
        <input type="file" class="input-file" id="input-file" name="logo" />
    </div>
</div>
