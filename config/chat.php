<?php

return [
    'agent_max_client_count' => env('CHAT_AGENT_MAX_CLIENT_COUNT', 5),
    'load_message_count' => env('CHAT_LOAD_MESSAGE_COUNT', 5),
    'queues' => [
        'auto_transfer' => 'auto-transfer',
        'response_time_update' => 'response-time-update',
        'summarize' => 'summarize',
        'chat_download_agent_wise' => 'chat-download-agent-wise',
        'expire_chat_close' => 'expire-chat-close',
        'chat_notification' => 'chat-notification'
    ],
    'chat_transfer_size' => 300,
    'chat_timeout_key_prefix' => 'chat_timeout_close_',
    'org_chat_timeout_key_prefix' => 'org_chat_timeout_',
    'chat_default_expire_time' => 5, //min
];


