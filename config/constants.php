<?php

return [
    /**
     * Fields inside 'lead_form_required_fields' array will be displayed as selected
     * in SuperAdmin/Admin and hence are reflected on add lead page.
     *
     * Boolean value against every field tells the system if the same field is mandatory.
     */
    'user'       => [
        'role' => [
            'super_admin' => 1,
            'admin'       => 2,
            'manager'     => 3,
            'team_lead'   => 4,
            'associate'   => 5
        ]
    ],
    'PERMISSION' => [
        "ROLE"                => 1,
        'CANNED-RESPONSE'     => 2,
        'DASHBOARD-ACCESS'    => 3,
        'GROUP-CREATION'      => 4,
        'SUPERVISE-TIP-OFF'   => 5,
        'CHAT-HISTORY'        => 6,
        'CHAT-NOTIFIER'       => 7,
        'CHAT-TRANSFER'       => 8,
        'AUTO-CHAT-TRANSFER'  => 9,
        'CHAT-TAGS'           => 10,
        'CHAT-FEEDBACK'       => 11,
        'SEND-ATTACHMENT'     => 12,
        'DOWNLOAD-ATTACHMENT' => 13,
        'EMAIL'               => 14,
        'TIMEOUT'             => 15,
        'INTERNAL-COMMENTS'   => 16,
        'DOWNLOAD-REPORT'     => 17,
        'CHAT'                => 18,
        'BAN-USER'            => 19,
        'OFFLINE-FORM'        => 20,
        'TMS-KEY'             => 21,
        'CLASSIFIED-CHAT'     => 22,
        'AUDIO-NOTIFICATION'  => 23,
        'LOGIN-HISTORY'       => 24,
        'SNEAK'               => 25,
        'CHAT-DOWNLOAD'       => 26,
        'SESSION_TIMEOUT'     => 27,
        'ARCHIVE_CHAT'        => 28,
        'IDENTIFIER-MASKING'  => 29,
        'MISSED-CHAT'         => 30,
        'CUSTOMER-INFORMATION'=> 31,
    ],
     // custom permission ids given for fixed sidebar modules
    
    'FIXED_SIDEBAR_CUSTOM_PERMISSION_IDS' => [
        'ORGANIZATION_LIST' => 201,
        'PERMISSION_LIST' => 202,
        'USER_LIST' => 203,
        'CUSTOMIZE_FIELDS' => 204,
        'LEAD_ENQUIRE' => 205
        
    ],
    'STATUS_SUCCESS'         => 200,
    'STATUS_FAIL'            => 422,
    'STATUS_BAN_USER'        => 251,
    'STATUS_NO_AGENT_ONLINE' => 252,
    'STATUS_ONLINE'            => 1,
    'STATUS_OFFLINE'           => 0,
    'ROLE_PERMISSION_ID'       => 1,
    'GROUP_DEFAULT'            => 'Default',
    'ADMIN_ROLE_IDS'           => [1, 2],
    'SPACE_NOT_ALLOWED_CONFIG' => '/^\S*$/u',
    'CHAT_STATUS'            => [
        'UNPICKED'              => 1,
        'PICKED'                => 2,
        'TRANSFERRED'           => 3,
        'TERMINATED_BY_AGENT'   => 4,
        'TERMINATED_BY_VISITOR' => 5,
    ],
    'EMAIL_STATUS'           => [
        'DELIVERED' => 'delivered'
    ],
    'ORG_KEY_LENGTH'         => 30,
    'DASHBOARD_DEFAULT_DAYS' => 7,
    'OFFLINE_MESSAGE'        => "No Agents are online!.",
    'TICKET_APPLICATION' => [
            'LQS' =>  1,
            'TMS' => 3,
            'LMS' => 2
        ],
    'BANNED_CLIENTS' => [
        'TEXT_SEARCH' => 0,
        'AGENT_SEARCH' => 1
    ],
    'LAST_ACTIVITY_SESSION_TIME' => 180,
    'USER_STATUS' => [
        'ACTIVE' => 1,
        'INACTIVE' => 0
    ],
    'ORGANIZTION_STATUS' => [
        'ACTIVE' => 1,
        'INACTIVE' => 0
    ],
    'AUTO_CHAT_TRANSFER_LIMIT' => 2,
    'ORGANIZATION_VALIDITY_REMINDER_DAYS'=> [1,3,7],
    'ORGANIZATION_VALIDITY_MAIL_ID' => explode(',', env('ORGANIZATION_VALIDITY_MAIL_ID', 'Product.surbo@vfirst.com')),
    'DAILY_SUMMARY_REPORT_SUBJECT' => env('DAILY_SUMMARY_REPORT_SUBJECT'),
    'ARCHIVE_TYPE' => [
        'HIERARCHICAL_ARCHIVE'=> 1,
        'COMPLETE_ARCHIVE' => 2
    ],
    'DEVICE_TYPE' => [
        'Android' => 1,
        'IOS'     => 2,
        'Web'     => 3
    ],
    'MAIL_ENCRYPTION'=>'tls',
    'MAIL_SERVICE_PROVIDER'=>[
        1=>'Octane',
        2=>'Amazon AWS SES'
    ],
    'OFFLINE_QUERIES_TYPE' => [
        'ORGANIZATION'=> 1,
        'GROUP' => 2
    ],
    'MISSED_CHAT_ACTION' => [
        'CONTACT' => 0, // Contact Customer
        'WA_PUSHED' => 1, // Customer Contacted
        'REJECTED' => 2, // Chat Rejected
    ],
    'WA_PUSH_SERVICE' => "surbo",
    'CHAT_NOTIFICATION_STATUS' => [
      'ENABLE' => 1,
      'DISABLE' => 0,
    ],
    'CHAT_NOTIFICATION_EVENTS' => [
      'NEW_CHAT' => 'new_chat',
      'TRANSFER' => 'transfer',
      'INTERNAL_COMMENT' => 'internal_comment',
      'NEW_MESSAGE' => 'new_message',
      'SESSION_TIMEOUT' => 'session_timeout',
    ],
    'CUSTOMER_CHAT_LABEL' => [
        'WHATSAPP' => [
            'NUMBER' => 1,
            'NAME' => 2,
            'NUMBER_NAME' =>3
        ]
        ],
    'DOWNLOAD_TRACKS' => [
        'IN_PROCESS'=> 0,
        'SUCCESS' => 1, //Mail sent successfully
        'FAILURE' => 2, //Mail not sent
    ],

    'PERMISSION_IDS_SIDEBAR' => [2,3,5,14,20,21,22,24,30],
    'ADMIN_PERMISSION_IDS_SIDEBAR' => [2,3,19,20,24,30],   
    'SIDEBAR_PERMISSION_MAPPING_WITH_LANGUAGE' => [
        2 =>'canned_response',
        3 =>'dashboard',
        5 =>'supervise_tipoff',
        14 =>'sent_emails',
        19 =>'banned_users',
        20 =>'offline_queries',
        21 =>'ticket_enquire',
        22 =>'classified_chat',
        24 =>'user_logging',
        30 =>'missed_chat',
        ],
];
