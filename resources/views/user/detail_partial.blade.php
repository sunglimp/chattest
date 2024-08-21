<div class="popup__content">
    <div class="popup__small__content--wrap">
        <img src={{$user_detail->image}} class="picture" />
    </div>
    <div class="popup__small__content--wrap">
        <label>{{default_trans($organizationId.'/user_list.ui_elements_messages.add_full_name', __('default/user_list.ui_elements_messages.add_full_name'))}}</label>
        <span>{{$user_detail->name}}</span>
    </div>
    <div class="popup__small__content--wrap">
        <label>{{default_trans($organizationId.'/user_list.ui_elements_messages.email_id', __('default/user_list.ui_elements_messages.email_id'))}}</label>
        <span>{{$user_detail->email}}</span>
    </div>
    <div class="popup__small__content--wrap">
        <label>{{default_trans($organizationId.'/user_list.ui_elements_messages.mobile', __('default/user_list.ui_elements_messages.mobile'))}}</label>
        <span>{{$user_detail->mobile_number}}</span>
    </div>
    <div class="popup__small__content--wrap">
        <label>{{default_trans($organizationId.'/user_list.ui_elements_messages.add_gender', __('default/user_list.ui_elements_messages.add_gender'))}}</label>
        <span>{{$user_detail->gender}}</span>
    </div>
    <div class="popup__small__content--wrap">
        <label>{{default_trans($organizationId.'/user_list.ui_elements_messages.concurrent_chats', __('default/user_list.ui_elements_messages.concurrent_chats'))}}</label>
        <span>{{$user_detail->no_of_chats}}</span>
    </div>
    <div class="popup__small__content--wrap">
        <label>{{default_trans($organizationId.'/user_list.ui_elements_messages.role', __('default/user_list.ui_elements_messages.role'))}}</label>
        <span>{{$user_detail->role->name}}</span>
    </div>
    <div class="popup__small__content--wrap">
        <label>{{default_trans($organizationId.'/user_list.ui_elements_messages.reporting_manager', __('default/user_list.ui_elements_messages.reporting_manager'))}}</label>
        {{--@if($user_detail->report != null)--}}
        <span>{{isset($user_detail->parent->name)?$user_detail->parent->name:"N/A" }}</span>
        {{--@endif--}}
    </div>
    <div class="popup__small__content--wrap">
        <label>{{default_trans($organizationId.'/user_list.ui_elements_messages.time_zone', __('default/user_list.ui_elements_messages.time_zone'))}}</label>
        <span>{{$user_detail->timezone}}</span>
    </div>
    <div class="popup__small__content--wrap">
        <label>{{default_trans($organizationId.'/user_list.ui_elements_messages.group', __('default/user_list.ui_elements_messages.group'))}}</label>
        <span>{{$user_groups}}</span>
    </div>

</div>
