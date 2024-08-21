<div class="popup__content">
    <div class="popup__content--wrap">
        <label>{{default_trans($organizationId.'/user_list.ui_elements_messages.add_full_name', __('default/user_list.ui_elements_messages.add_full_name'))}}<span class="astrick">*</span></label>
        <input type="text" class="custom-input" name="name" value="{{$user_detail->name}}" />
    </div>
    <div class="popup__content--wrap">
        <label>{{default_trans($organizationId.'/user_list.ui_elements_messages.add_gender', __('default/user_list.ui_elements_messages.add_gender'))}} <span class="astrick">*</span></label>
        <div class="stv-radio-tabs-wrapper">

            <input type="radio" class="stv-radio-tab" name="gender"  id="male_edit" value="male" @if($user_detail->gender == 'male') checked ="checked" @endif />
            <label for="male_edit">{{default_trans($organizationId.'/user_list.ui_elements_messages.male_gender', __('default/user_list.ui_elements_messages.male_gender'))}} </label>
            <input type="radio" class="stv-radio-tab" name="gender"  id="female_edit" value="female" @if($user_detail->gender == 'female') checked ="checked" @endif/>
            <label for="female_edit">{{default_trans($organizationId.'/user_list.ui_elements_messages.female_gender', __('default/user_list.ui_elements_messages.female_gender'))}}</label>
        </div>
    </div>
    <div class="popup__content--wrap">
        <label>{{default_trans($organizationId.'/user_list.ui_elements_messages.email', __('default/user_list.ui_elements_messages.email'))}} <span class="astrick">*</span></label>
        <input type="text" class="custom-input" name="email" value="{{$user_detail->email}}" />
    </div>
    <div class="popup__content--wrap">
        <label>{{default_trans($organizationId.'/user_list.ui_elements_messages.password', __('default/user_list.ui_elements_messages.password'))}}</label>
        <input type="password" class="custom-input" name="{{$user_detail->password}}"  disabled />
    </div>
    <div class="popup__content--wrap">
        <label>{{default_trans($organizationId.'/user_list.ui_elements_messages.mobile', __('default/user_list.ui_elements_messages.mobile'))}}<span class="astrick">*</span></label>
        <input type="text" class="custom-input" name="mobile_number" value="{{$user_detail->mobile_number}}" />
    </div>
    <div class="popup__content--wrap">
        <label>{{default_trans($organizationId.'/user_list.ui_elements_messages.role', __('default/user_list.ui_elements_messages.role'))}}  <span class="astrick">*</span></label>
        <div class="select-custom no-shadow">
            <select name="role_id" id="select-role">
                <option value="">{{default_trans($organizationId.'/user_list.ui_elements_messages.select_role', __('default/user_list.ui_elements_messages.select_role'))}}</option>
                @foreach ($user_roles as $type)
                    @php $role_class= $type['id']==$user_detail->role_id?"selected":"";

                    @endphp
                    <option {{$role_class}} value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="popup__content--wrap">
        <label>{{default_trans($organizationId.'/user_list.ui_elements_messages.concurrent_chats', __('default/user_list.ui_elements_messages.concurrent_chats'))}}</label>
        @php $setDisableAttr = ($user_detail->no_of_chats==0 ) ?"disabled":"";  @endphp
        <input type="text" class="custom-input" id="no_of_chats" name="no_of_chats" value="{{$user_detail->no_of_chats?$user_detail->no_of_chats:""}}" {{$setDisableAttr}} />
    </div>
    <div class="popup__content--wrap">
        <label>{{default_trans($organizationId.'/user_list.ui_elements_messages.reporting_manager', __('default/user_list.ui_elements_messages.reporting_manager'))}}</label>
        <div class="select-custom no-shadow" >
            <div id="add-report-to">
                @php $setDisableAttr = ($user_detail->role_id==2 )?"disabled":"";  @endphp
            @if(count($report_to))
                    <select name="report_to" id="report-to" class="report-to  {{$setDisableAttr}}">
                        @foreach($report_to as $k=>$v)
                            @php $select =($user_detail->report_to==$v->id )? "selected":"";
                            @endphp
                            <option {{$select }} value={{ $v->id}} >{{$v->name }}</option>
                        @endforeach
                    </select>
                @else
                    <select name="report_to" id="report-to" class="report-to   {{$setDisableAttr}}">
                        <option disabled selected>{{default_trans($organizationId.'/user_list.ui_elements_messages.select_manager', __('default/user_list.ui_elements_messages.select_manager'))}} </option>
                    </select>
                @endif
            </div>
        </div>
    </div>
    <div class="popup__content--wrap">
        <label>{{default_trans($organizationId.'/user_list.ui_elements_messages.time_zone', __('default/user_list.ui_elements_messages.time_zone'))}}</label>
        <div class="select-custom time_zone no-shadow">
            <select name="timezone" class="dropdown-search">
                <option disabled selected="" >Select Timezone</option>
                @foreach($timezone_list as $k=>$v){{default_trans($organizationId.'/user_list.ui_elements_messages.select_timezone', __('default/user_list.ui_elements_messages.select_timezone'))}}
                    @php  $selected= ($k==$user_detail->timezone) ?"selected":"";  @endphp
                    <option {{$selected}}
                            value="{{ $k }}"
                    >{{ $k.'('.$v.')' }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="popup__content--wrap">
        <label>{{default_trans($organizationId.'/user_list.ui_elements_messages.group', __('default/user_list.ui_elements_messages.group'))}}</label>
        <div class="select-custom">

            @php $setDisableClass = count($groups) ?"":"group-disabled";  @endphp
            @php $setDisableClass = ($user_detail->role_id==2) ?"disabled":"";  @endphp

            <select   multiple="multiple" id="group" name="group[]" class= "{{$setDisableClass}} groupMultiple">
                @foreach($groups as $group)
                    @if(isset($group['selected']) && $group['selected'] == 1)
                        <option selected value="{{ $group['id'] }}" >{{ $group['name'] }}</option>
                    @else
                        <option value="{{ $group['id'] }}">{{ $group['name'] }}</option>
                    @endif
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
                 @foreach ($user_languages as $language=>$selected_lang)
                    	@if ($selected_lang['value'] == true)
                    		<option selected value="{{$language}}" >{{$selected_lang['label']}}</option>
                    	@else
                    		<option value="{{$language}}" >{{$selected_lang['label']}}</option>
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
    <input type="hidden" name="user_id" id="user_id" value="{{$user_detail->id}}" />
    <input type="hidden" name="organization_id" id="organization_id" value="{{$user_detail->organization_id}}" />
</div>

