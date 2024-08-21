<span class="icon icon--accepted popup-btn test" id="edit" data-id={{$id}}>
    <span class="icon-hover-text">{{default_trans($organizationId.'/user_list.ui_elements_messages.edit', __('default/user_list.ui_elements_messages.edit'))}}</span>
                                    <i class="fas fa-pencil-alt"></i>
                                </span>
<span class="icon icon--warning" id="view" data-id={{$id}}>
    <span class="icon-hover-text">{{default_trans($organizationId.'/user_list.ui_elements_messages.view', __('default/user_list.ui_elements_messages.view'))}}</span>
                                    <i class="fas fa-eye"></i>
                                </span>
<span class="icon icon--rejected" id="delete" data-id={{$id}}>
    <span class="icon-hover-text">
    {{default_trans($organizationId.'/user_list.ui_elements_messages.delete', __('default/user_list.ui_elements_messages.delete'))}}
    </span>
    <i class="fas fa-times"></i>
</span>

<span class="icon icon--warning" id="user-permission" data-id={{$id}}>
    <span class="icon-hover-text">
    {{default_trans($organizationId.'/user_list.ui_elements_messages.permission', __('default/user_list.ui_elements_messages.permission'))}}
    </span>
<i class="fa fa-list-alt"></i>
</span>

@if($is_sneak_in == true)
<span class="icon icon--warning" id="log-in" data-id={{$id}}>
   <span class="icon-hover-text">
   {{default_trans($organizationId.'/user_list.ui_elements_messages.log_in', __('default/user_list.ui_elements_messages.log_in'))}}        
</span>
       <i class="fa fa-user-alt"></i>
</span>
@endif

@if($is_login==1)
<span class="icon icon--rejected clear-login" data-id={{$id}}>
    <span class="icon-hover-text">
    {{default_trans($organizationId.'/user_list.ui_elements_messages.clear_session', __('default/user_list.ui_elements_messages.clear_session'))}}
    </span>
    <i class="fa fa-broom"></i>
</span>
@endif
