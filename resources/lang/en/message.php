<?php

return [
    //Organization
    'msg_company_name_required' => 'Company name is required.',
    'msg_name_required' => 'Name is required.',
    'msg_name_regex'=> 'Incorrect Name.',
    'msg_company_name_exist' => 'Company name already exist.',
    'msg_company_name_regex'=> 'Incorrect Company Name.',
    'msg_user_name_alpha' => 'Incorrect Name.',
    'msg_organization_required' => 'The organization is required.',
    'msg_name_required' => 'The contact name is required.',
    'msg_organization_exist' => 'This organization name is already exist',
    'org_list_success' => 'Organization list fetched successfully',
    'org_list_fail' => 'Organization list fetching failed',
    'msg_language_required' => 'Language is required.',
    'validity_update_msg'=> 'Account expired. Please extend the account validity',

    //Mobile
    'msg_mobile_required' => 'Mobile no. is required.',
    'msg_mobile_numeric' => 'Mobile no. must be numeric.',
    'msg_mobile_max' => 'Mobile no. must be min 10 numbers and less than 15 numbers.',
    'msg_mobile_min' => 'Mobile no. must be min 10 numbers and less than 15 numbers.',
    'msg_login' => 'Login Successfully',
    'msg_logout' => 'Logout Successfully',
    'msg_device_headers' => 'Device-ID, deviceType, deviceToken field is required in header.',
    //Email
    'msg_email_required' => 'Email ID is required.',
    'msg_email_format' => 'Incorrect Email ID.',
    'msg_email_exist' => 'Email ID already exists.',
    'msg_success_updated' => 'Sucessfully updated',
    'msg_password_policy' => 'Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters.',

    //Canned Response
    'shortcut_required' => 'This field is required',
    'response_required' => 'This field is required',
    'canned_response_sucess' => 'Canned Response added successfully',
    'canned_response_fail' => 'Canned Response failed',
    'canned_response_id_required' => 'Canned Response Id required',
    'canned_response_delete_sucess' => 'Canned Response deleted successfully',
    'canned_response_delete_fail' => 'Canned response deletion failed',
    'unique_combination' => 'Duplicate combination of shortcut and response is not allowed',
    'canned_response_fetch_fail' => 'Canned Response not fetched',
    'canned_response_fetch_sucess' => 'Canned Response fetched successfully',
    'canned_response_edit_sucess' => 'Canned Response edited successfully',
    'canned_response_edit_fail' => 'Canned Response edit failed',
    'canned_response_title' => 'Canned Response',
    'shortcut_max_length' => 'Only 20 characters allowed',

    //Tags
    'tag_name_required' => 'This field is required',
    'tag_add_success' => 'Tags added successfully',
    'tag_add_fail' => 'Tags adding failed',
    'tag_incorrect' => 'No spaces allowed in tags',
     'tag_fetch_fail' => 'Tags fetching failed',
     'tag_fetch_success' => 'Tags fetched successfully',
     'tag_spaces_disallowed' => 'Spaces not allowed in tags',
     'tag_name_unique' => 'Tag name must be unique',
     'tag_id_required' => 'Tag Id required',
     'tag_delete_success' => 'Tag deleted successfully',
     'tag_delete_fail' => 'Tag deletion failed.',
     'tag_duplicate' => 'Duplicate Tags are not allowed',
    'tag_link_success' => 'Tag is linked with corresponding chat successfully',
    'tag_link_fail' => 'Tag and chat linking failed',
    'tag_link_exist' => 'This tag is already linked with particular chat',
    'tag_max_length' => 'Only 20 characters allowed',
    'tag_unlink_success' => 'Tag unlinked successfully',
    'tag_unlink_fail'   => 'Tag unlinked fail',

    //Unique Key
    'org_key_required' => 'Organization Key Required',
    'key_fetched' => 'Organization Key fetched successfully',
    'key_fetch_failed' => 'Organization Key fetching failed',
    'org_id_required' => 'Organization Id Required',
    'key_update_success' => 'Organization Key updated successfully',
    'key_update_failed' => 'Organization Key updated failed',
    'org_key_unique' => 'This key has already been taken',

    'no_reportee_found' => 'No reporting manager found',
    'reportee_found' => 'User cannot be deleted due to active reportees (:name :count) in the system',
    'report_to_required' => 'The report manager field is required',
    'api_token_required' => 'API token is required',
    'something_went_wrong' => 'Something Went Wrong. Please try again',
    'msg_seat_alloted_required'=> 'This field is required.',
    'msg_seat_alloted_integer'=>'Only numeric characters are allowed.',
    'msg_timezone_required'=> 'Timezone is required',
    'msg_website_url'=> 'Invalid input',
    'msg_add_success' => 'Messages added successfully',
    //User
    'msg_user_name_required' => 'Name is required.',
    'msg_concurrent_chat_required'=> 'This field is required.',
    'msg_concurrent_chat_integer'=>'Only numeric characters are allowed.',
    'msg_role_id_required' => 'This field is required.',
    //Chat
    'chat_pick_success'=> 'Chat picked up successfully',
    'chat_pick_fail' => 'Chat picking failed',
    'chat_pick_params' => 'Please check whether agent id and chat id are passed',
    'chat_close_success'=> 'Chat terminated successfully',
    'chat_close_fail' => 'Chat closing failed',
    'chat_transfer_success' => 'Chat is transferred successfully',
    'chat_transfer_agent_offline' => 'Selected agent is offline.',
    'chat_close_params' => 'Please check whether agent id and chat id are passed',
    'offline_fail_active_chats' => 'You have active chats available. In case of force log out, active chats will be lost. Do you really want to proceed?',
    'internal_chat_delete_success' => 'Internal Comments mapping with chat removed',
    'internal_chat_delete_fail' => 'Internal Comments removal with chats failed',
    'invalid_chat_channel' => "Invalid Chat Channel",
    'chat_transfer_max' => 'Only '.config('chat.chat_transfer_size').' characters are allowed',
    'agent_no_chat_permission' => 'The Agent has no permission for the chat.',
    //email
    'email_senders_fetched' => 'Email Senders fetched successfully',
    'email_senders_fetch_fail' => 'Email Senders not fetched',
    'email_data_fetched' => 'Email Data fetched successfully',
    'email_data_fetch_fail' => 'Email Data not fetched',
    'file_size_exceeded' => 'File size exceeded',
    'sent_items_title' => 'Sent Items',
    'email_subject_required' => 'Email Subject is required',
    'email_body_required' => 'Email Body is required',
    'email_file_size_exceeded' => 'File Size exceeded',
    'msg_group_name_required' => ':attribute  is required.',
    'msg_group_name_unique' => ':attribute should be unique.',

    'access_not_allowed' => 'Access not allowed',
    'copyright' => '<i>Copyright</i> &COPY;'.date('Y').' <i>ValueFirst Digital Media Pvt. Ltd.</i> All rights reserved.',

    'incorrect_file_format' => 'This file format is not allowed',
    'message_not_added'  => 'Message not added successfully',
    'parameters_check'   => 'Please check required paarmeters are given',
    'organization_banned' => 'You are banned for this organization',
    'no_agent_online' => 'Thank you for contacting us. We are closed today. We have noted your contact details. Our agent will get in touch with you on the next working day. We thank you again for your interest in :Organization.',

    //TMS
    'update_ticket_success' => 'Ticket fields updated successfully',
    'update_ticket_fail'    => 'Ticket fields update failed',
    'update_tms_key_failed' =>  'Update ticket field key failed',
    'api_key_failed'        =>  'TMS API Key is not valid',
    'msg_tms_key_required'=> "TMS API Key is required.",
    'msg_tms_first_name_format'=> "First name format is invalid.",
    'msg_tms_last_name_format'=> "Last name format is invalid.",
    'msg_tms_mobile_format'=> "Mobile format is invalid.",
    'msg_tms_size_format'=>"Mobile must be at least 10 characters.",

    'msg_classified_chat_required'=>"Token is required.",
    'msg_classified_chat_regex'=>"Token is not valid.",

    'ticket_details_failed' => 'Failed in fetching ticket details',
    'ticket_details_success' => 'Ticket details fetched successfully',
    'lead_details_failed' => 'Failed in fetching lead details',
    'lead_details_success' => 'Lead details fetched successfully',

    //Banned clients
    'client_banned_failed' => 'Client ban failed',
    'banned_list_success' => 'Banned clients list fetched successfully',
    'banned_list_fail'     => 'No client has been banned for this organization',
    'banned_client_detail_success' => 'Banned client chats fetched successfully',
    'banned_client_detail_fail' => 'Banned client chats fetching failed',
    'unban_client_success' => 'Client has been unbanned successfully',
    'unban_client_fail' => 'Client has not been unbanned',
    'client_banned_success' => 'Client has been banned successfully',

    //Notification
    'notification_required' => 'Audio notification is required',

    'sneak_success' => 'Sneak user has been successfully done',
    'user_already_login' => 'Sneak in will not be possible since either user is logged in already or inactive',
    'can_sneak_in' => 'Sneak user is possible',

    'bot_image' => 'Bot',
    'msg_sucessfully_created' => 'Successfully Updated.',
    
    //Sidebar
    'side_bar_data' => 'Sidebar Data'
];
