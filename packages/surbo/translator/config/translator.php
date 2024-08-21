<?php

return [
     /*
    |--------------------------------------------------------------------------
    | Routes group config
    |--------------------------------------------------------------------------
    |
    | The default group settings for the elFinder routes.
    |
    */
    'route'          => [
        'prefix'     => 'translator',
        'middleware' => ['web','auth'],
    ],
    'languages' => [
        'english' => 'en',
        'hindi'   => 'hi',
        'arabic'  => 'ar',
        'japanese' => 'ja',
        'chinese'  => 'ch',
        'french'   => 'fr',
        'russian'  => 'ru',
        'chinese'  => 'zh'
    ],
    'features' => [
        'dashboard'     => 'Dashboard',
        'permission'    => 'Permission List',
        'user_list' => 'User List',
        'canned_response' => 'Canned Response',
        'banned_users' => 'Banned Users',
        'chat'=> 'Chat',
        'sidebar'=>'Sidebar',
        'user_logging' => 'User Logging',
        'archive'=> 'Archive',
        'ticket_enquire'=>'Ticket Enquire',
        'lead_enquire'=>'Lead Enquire',
        'classified'=>'Classified',
        'sent_emails'=>'Sent Emails',
        'supervise_tipoff'=> 'Supervise & Tipoff',
        'offline_queries' => 'Offline Queries',
        'missed_chat' => 'Missed Chat'
    ],
    'type'=>[
        'ui_elements_messages'=> 'UI Elements',
        'validation_messages'=>'Validations',
        'success_messages'=>'Success Message',
        'fail_messages'=>'Fail Messages'
    ]
];
