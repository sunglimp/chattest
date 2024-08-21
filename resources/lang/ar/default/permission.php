<?php

return array(
    'ui_elements_messages' =>
    array(
        'action'                               => 'Action',
        'permission'                           => 'PERMISSION',
        'permissions'                          => 'Permissions',
        'admin'                                =>'Admin',
        'manager'                              =>'Manager',
        'team-lead'                            =>'Team Lead',
        'associate'                             =>'Associate',
        'roles'                                => 'Roles',
        'canned-response'                      => 'Canned Responses',
        'dashboard-access'                     => 'Dashboard Access',
        'group-creation'                       => 'Group Creations',
        'supervise-tip-off'                    => 'Supervise & Tip Off',
        'chat-history'                         => 'Chat History',
        'chat-notifier'                        => 'Chat Notifier',
        'chat-transfer'                        => 'Chat Transfer',
        'auto-chat-transfer'                   => 'Auto Chat Transfer',
        'chat-tags'                            => 'Chat Tags',
        'chat-feedback'                        => 'Chat Feedback',
        'send-attachments'                     => 'Send Attachments',
        'email'                                => 'Email',
        'timeout'                              => 'Timeout',
        'internal-comments'                    => 'Internal Comments',
        'download-report'                      => 'Download Report',
        'chat'                                 => 'Chat',
        'ban-user'                             => 'Ban User',
        'offline-form'                         => 'Offline Form',
        'surbo_ace_integration'                => 'Surbo ACE Integration',
        'classified_chat'                      => 'Classified Chat',
        'audio_notification'                   => 'Audio Notification',
        'login_history'                        => 'Login History',
        'sneak_user'                           => 'Sneak User',
        'save'                                 => 'Save',
        'group'                                => 'Group',
        'groups'                               => 'Groups',
        'add_group'                            => 'Add Group',
        'enter_group'                          => 'Enter Group',
        'cancel'                               => 'Cancel',
        'chat_notifier'                        => 'Chat Notifier',
        'hours'                                => 'Hours',
        'minutes'                              => 'Minutes',
        'seconds'                              => 'Seconds',
        'submit'                               => 'Submit',
        'please_select_time_for_notify_Chat'   => 'Please Select time for notify Chat',
        'auto_chat_transfer'                   => 'Auto Chat Transfer',
        'please_select_time_for_transfer_chat' => 'Please Select time for Transfer Chat',
        'tag'                                  => 'Tag',
        'add_tag'                              => 'Add Tag',
        'enter_tag'                            => 'Enter Tag',
        'upload_attachment_size'               => 'Upload Attachment Size',
        'mb'                                   => 'MB',
        'email_configuration'                  => 'Email Configuration',
        'username'                             => 'Username',
        'password'                             => 'Password',
        'port'                                 => 'Port',
        'host'                                 => 'Host',
        'chat_timeout'                         => 'Chat Timeout',
        'please_select_time_for_timeout'       => ' Please Select time for timeout',
        'ban_client_(days)'                    => 'Ban Client (days)',
        'days'                                 => 'days',
        'enter_days'                           => 'Enter Days',
        'message'                              => 'Message',
        'optional'                             => 'Optional',
        'mandatory'                            => 'Mandatory',
        'tag_creation'                         => 'Tag Creation',
        'chat_download'                        => 'Chat Download',
        'qc'                                   => 'QC',
        'wa_push'                              => 'Wa Push',
        'all'                                  => 'All',
        'group_wise'                           => 'Group Wise',
        'offline_querries'                      => 'Offline Queries',
        'tms'                                  => 'Tms',
        'api'                                  => 'API',
        'free_api'                             => 'Free API',
        'template_id'                          => 'Template Id',
        'missed_chat_template_id'              => 'Template Id',
        'missed_chat_api'                      => 'API',
        'missed_chat_bot_id'                   => 'Bot Id',
        'missed_chat_token'                    => 'Token',
        'free_template_id'                     => 'Free Template Text',
        'in_session_push'                      => 'In-Session Free Messaging',
        'out_session_push'                     => 'Out-Session Push',
        'bot_id'                               => 'Bot Id',
        'token'                                => 'Token',
        'add_bot_transcript'                   => 'Add Bot Transcript',
        'subject'                              => 'Subject',
        'session_timeout'                      => 'Session Timeout',
        'select_time_session_timeout'          => 'Please select time for session timeout',
        'max_transfer_limit_note' => 'Max transfer limit 2 - 20',
        'limit_for_transfer_chat' => 'Max Limit for Auto Chat Transfer',
        'time_for_auto_transfer_chat' => 'Time for Auto Chat Transfer',
        'archive_chat' => 'Archive Chat',
        'hierarchical_archive' => 'Hierarchical Archive',
        'complete_archive' => 'Complete Archive',
        'encryption'=>'Encryption',
        'select_provider'=>'Select Provider',
        'thank_you_message' => 'Thank You Message',
        'missed_chat'       => 'Missed Chat',
        'from_email' => 'From Email',
        'customer_information' => 'Customer Information',
        'wa_number' => 'Whatsapp Number',
        'wa_name' => 'Whatsapp Name',
        'wa_number_and_name' => 'Whatsapp Number and Name',
        'send_email_on_qc' => 'Send Email on Query Capture',
    ),
    'success_messages'     =>
    array(
        'notification_setting_updated' => 'Notification setting updated successfully',
        'msg_success_updated'          => 'Successfully updated',
        'setting_updated'              => 'Setting Updated',
        'tag_add_success'              => 'Tags added successfully',
        'tag_delete_success'           => 'Tag deleted successfully',
    ),
    'fail_messages'        =>
    array(
        'api_key_failed'        => 'TMS API Key is not valid',
        'update_tms_key_failed' => 'Update ticket field key failed',
        'group_already_used'    => 'Group already exist',
        'something_wrong'       => 'Something wrong',
        'tag_add_fail'          => 'Tags adding failed',
        'tag_delete_fail'       => 'Tag deletion failed',
    ),
    'validation_messages'  =>
    array(
        'name_required'               => ':attribute is required.',
        'name_unique'                 => ':attribute should be unique.',
        'notificationEvents_required' => 'Notification event require',
        'classified_token_required'   => 'Token is required.',
        'classified_token_regex'      => 'Token is not valid.',
        'tms_key_required'            => 'TMS key require',
        'ban_days_required'           => 'The ban days field is required.',
        'ban_days_integer'            => 'The ban days must be an integer.',
        'ban_days_min'                => 'The ban days must be at least 1.',
        'ban_days_max'                => 'The ban days may not be greater than 999.',
        'size_required'               => 'The size field is required.',
        'size_integer'                => 'The size must be an integer.',
        'size_min'                    => 'The size must be at least 1.',
        'size_max'                    => 'The size may not be greater than 20.',
        'port_digit_between'          => 'The port must be between 1 and 6 digits.',
        'username_required'           => 'The username field is required.',
        'password_required'           => 'The password field is required.',
        'host_required'               => 'The host field is required.',
        'port_required'               => 'The port field is required.',
        'hour_required'               => 'The hour field is required',
        'minute_required'             => 'The minute field is required.',
        'second_required'             => 'The second field is required.',
        'hour_max'                    => 'The hour may not be greater than :attribute',
        'minute_max'                  => 'The minute may not be greater than :attribute',
        'second_max'                  => 'The second may not be greater than :attribute',
        'hour_min'                    => 'The hour must be at least 0.',
        'minute_min'                  => 'The minute must be at least :attribute',
        'second_min'                  => 'The minute must be at least 1.',
        'notification_required'       => 'Audio notification is required',
        'tag_name_required'           => 'This field is required',
        'tag_spaces_disallowed'       => 'Spaces not allowed in tags',
        'tag_name_unique'             => 'Tag name must be unique',
        'tag_max_length'              => 'Only 20 characters allowed',
        'settings_updated'            => 'Settings Updated',
        'email_required'              => 'Email is required',
        'email_format'                => 'Email format is wrong',
        'message_required'            => 'Message is required',
        'subject_required'            => 'Subject is required',
        'email_body_required'         => 'Email Body is required',
        'api_required'                => 'Api is required',
        'free_api_required'           => 'Free Api is required',
        'free_template_id_required'   => 'Free Template text is required',
        'session_push_required'       => 'Push type is required',
        'template_id_required'        => 'Template id required',
        'bot_id_required'             => 'Bot id required',
        'transfer_limit_required'             => 'The transfer limit field is required.',
        'transfer_limit_max'                  => 'The transfer limit must be equal or less than :attribute',
        'transfer_limit_min'                  => 'The transfer limit must be equal or greater than :attribute',
        'transfer_limit_integer' => 'The Transfer limit must be integer',
        'encryption_in'               => 'The Encryption value is invalid',
        'provider_type_required'      => 'The provider is required',
        'encryption_required_unless'  => 'The encryption is required unless provider is Octane.',
        'customer_information_label_required' => 'Please select atleast one option.',
        'send_email_on_qc_required' => 'Send Email on Query Capture required',
        ),
);
