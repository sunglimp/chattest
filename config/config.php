<?php
return [
    
    'IMP_CHAT_NOTIFIER'               => '120', //in seconds
    'AUTO_CHAT_TRANSFER'              => '60', //in seconds
    'CHAT_FEEDBACK'                   => 'NPS', //default feedback type
    'GROUP_DEFAULT'                   => 'Default',
    'ATTACHMENT_UPLOAD'               => '5', //memory limit in MB
    'ATTACHMENT_DOWNLOAD'             => '10', //memory limit in MB
    'ADMIN_ROLE_IDS'                  => [1, 2], //
    'SPACE_NOT_ALLOWED_CONFIG'        => '/^\S*$/u',
    'ORG_KEY_LENGTH'                  => 30,
    'TAG_MAX_LENGTH'                  => 20,
    'CANNED_RESPONSE_SHORTCUT_MAX' => 20,
    'MESSAGE_LIMIT'                   => 100,
    'attachment_folder'              => env('ATTACHMENT_FOLDER', '___ORG_ID__'.DIRECTORY_SEPARATOR.'email_attachments'),
    'SENT_ITEMS_PAGE_LENGTH'         => 10,
    'CANNED_RESPONSE_SHORTCUT_LENGTH' => 20,
    'PER_PAGE_SIZE_MISSED_CHAT' => 15,
    'SUPERADMIN_RESTRICTED_PERMISSIONS' => [14],
    'EMAIL_DETAIL_PAGE_LENGTH'          =>1,
    'CLIENT_LIMIT'=> 10,
    'SUMMARY_EXECUTE_TIME'  => env('SUMMARY_EXECUTE_TIME', 5), //This value should change according to the CRON TIME for dashboard summary {CRON summary:create}
    'FILE_CONVERSION'                 =>  [
                                            'FACTOR' => 0.000001,
                                            'UNIT' => 'MB'
                                          ] ,
    'chat_attachment_folder'        =>  env('CHAT_ATTACHMENT_FOLDER', '__ORG_ID__'.DIRECTORY_SEPARATOR.'chat_attachments'),
    'EMAIL_MAX_LENGTH'              =>  15,
    'ATTACHMENT_EXTENSIONS'         => ['jpeg','jpg','xls','pdf','doc','docx','mp4','xlsx',
                                        'mpga','mpeg','wav','ogx','oga','ogv','ogg','png','gif',
                                        'bmp','txt','webm','mpeg4','qt','mov','avi','mpegps',
                                        'wmv','flv','ppt','pptx','pdf','psd','zip','rar','m4a',
                                        '3gp', '3gpp', 'mpg', 'mkv', 'm4v', 'mp3'],
    'COLOR_CODES' => [
        'A'=>'#e83e8c',
        'B'=>'#0070bd',
        'C'=>'#21b573',
        'D'=>'#fd7e14',
        'E'=>'#fab03b',
        'F'=>'#1c819e',
        'G'=>'#34a7b2',
        'H'=>'#ffa45c',
        'I'=>'#665c84',
        'J'=>'#714288',
        'K'=>'#843b62',
        'L'=>'#69779b',
        'M'=>'#db2d43',
        'N'=>'#20716a',
        'O'=>'#587850',
        'P'=>'#26baee',
        'Q'=>'#7874f2',
        'R'=>'#cb9b42',
        'S'=>'#005792',
        'T'=>'#f08181',
        'U'=>'#616f39',
        'V'=>'#12e6c8',
        'W'=>'#fadbac',
        'X'=>'#8b104e',
        'Y'=>'#f4a9c7',
        'Z'=>'#3c415e'
    ],
    'FILE_CLASS' => [
        'fas fa-image fileicon' => ['jpeg','jpg', 'bmp', 'png','gif'],
        'far fa-file-excel fileicon' => ['xls', 'xlsx'],
        'fas fa-file-pdf fileicon' => ['pdf'],
        'fas fa-video fileicon' => ['webm','mpeg4','qt','mov','avi', 'mp4', 'mpeg4', 'mpegps'],
        'fas fa-file-archive fileicon' => ['zip', 'rar'],
        'far fa-file fileicon' => ['doc', 'docx']
    ],
    'APP_URL' => env('APP_URL'),
    'DASHBOARD_REPORT_NAME' => 'Surbo Chat Dashboard Report.xlsx',
    'MB_FILE_SIZE_UNIT' => 'mb',
    'KB_FILE_SIZE_UNIT' => 'kb',
    
    'SUMMARY_EMAIL_RECIEVERS' => env('SUMMARY_EMAIL_RECIEVERS'),
    'NOTIFICATION_EVENTS' => [
        'new_chat'                      => 'New Chats',
        'internal_chat_transfer'        => 'Internal Group Transfer',
        'external_chat_transfer'        => 'External Group Transfer',
        'new_internal_comment'          => 'Internal Comment',
        'automatic_chat_transfer'       => 'Automatic Chat Transfer',
    ],
    'CHAT_DOWNLOAD' => [
        'agent_wise_chat_download'      => 'Allow Agent Wise Chat Download'
    ],
    'NOTIFICATION_EVENT_KEYS' => [
        'new_chat'                      => 'new_chat',
        'internal_chat_transfer'        => 'internal_chat_transfer',
        'external_chat_transfer'        => 'external_chat_transfer',
        'new_internal_comment'          => 'new_internal_comment',
        'automatic_chat_transfer'       => 'automatic_chat_transfer',
    ],
    'user_log_in' => 1,
    'languages' => [
                    'en'=>'English',
                    'hi' => 'Hindi',
                    'ar' => 'Arabic',
                    'ja' => 'Japanese',
                    'zh' => 'Mandarin Chinese',
                    'fr' => 'French',
                    'ru' => 'Russian'
                    ],
    'default_language' => 'en',
      'feature' => [
        'dashboard' => 'Dashboard',
        'archive' => 'Archive',
        'permission_list' => 'Permission List',
        'user_list' => 'User List',
        'canned_response' => 'Canned Response',
        'banned_user' => 'Banned User',
        'user_logging' => 'User Logging'
    ],
    'export_location' => [
        'agent_chat_downloads' => env('AGENT_WISE_CHAT_DOWNLOAD_FOLDER', 'agent_chat_downloads'),
        'offline_query_downloads' => env('OFFLINE_QUERY_DOWNLOAD_FOLDER', 'offline_query_downloads'),
    ],
    'source_type_expire' => 86400,
    'queue_subtract_days' => 3,
    'attachment_root_folder' => env('ATTACHMENT_ROOT_FOLDER')
];
