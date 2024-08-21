<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Horizon Redis Connection
    |--------------------------------------------------------------------------
    |
    | This is the name of the Redis connection where Horizon will store the
    | meta information required for it to function. It includes the list
    | of supervisors, failed jobs, job metrics, and other information.
    |
    */

    'use' => 'default',


    /*
    |--------------------------------------------------------------------------
    | Horizon Redis Prefix
    |--------------------------------------------------------------------------
    |
    | This prefix will be used when storing all Horizon data in Redis. You
    | may modify the prefix when you are running multiple installations
    | of Horizon on the same server so that they don't have problems.
    |
    */

    'prefix' => env('HORIZON_PREFIX', 'horizon:'),

    /*
    |--------------------------------------------------------------------------
    | Horizon Route Middleware
    |--------------------------------------------------------------------------
    |
    | These middleware will get attached onto each Horizon route, giving you
    | the chance to add your own middleware to this list or change any of
    | the existing middleware. Or, you can simply stick with this list.
    |
    */

    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Queue Wait Time Thresholds
    |--------------------------------------------------------------------------
    |
    | This option allows you to configure when the LongWaitDetected event
    | will be fired. Every connection / queue combination may have its
    | own, unique threshold (in seconds) before this event is fired.
    |
    */

    'waits' => [
        'redis:default' => 60,
        'redis:mlprocess' => env('MLPROCESS_RETRY_WAIT', 120)
    ],

    /*
    |--------------------------------------------------------------------------
    | Job Trimming Times
    |--------------------------------------------------------------------------
    |
    | Here you can configure for how long (in minutes) you desire Horizon to
    | persist the recent and failed jobs. Typically, recent jobs are kept
    | for one hour while all failed jobs are stored for an entire week.
    |
    */

    'trim' => [
        'recent' => 60,
        'failed' => 10080,
    ],

    /*
    |--------------------------------------------------------------------------
    | Fast Termination
    |--------------------------------------------------------------------------
    |
    | When this option is enabled, Horizon's "terminate" command will not
    | wait on all of the workers to terminate unless the --wait option
    | is provided. Fast termination can shorten deployment delay by
    | allowing a new instance of Horizon to start while the last
    | instance will continue to terminate each of its workers.
    |
    */

    'fast_termination' => false,

    /*
    |--------------------------------------------------------------------------
    | Queue Worker Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define the queue worker settings used by your application
    | in all environments. These supervisors and settings handle all your
    | queued jobs and will be provisioned by Horizon during deployment.
    |
    */

    'environments' => [
        'production' => [
            'highest' => [
                'connection' => 'redis',
                'queue' => ['response-time-update','auto-transfer', 'expire-chat-close'],
                'balance' => 'simple',
                'processes' => 4,
                'tries' => 2,
            ],
            'higher' => [
                'connection' => 'redis',
                'queue' => ['default', 'summarize', 'chat-notification'],
                'balance' => 'simple',
                'processes' => 3,
                'tries' => 1,
            ],
            'high' => [
                'connection' => 'redis',
                'queue' => ['chat_archived_listener'],
                'balance' => 'simple',
                'processes' => 2,
                'tries' => 1,
            ],
            'low' => [
                'connection' => 'redis',
                'queue' => ['mlprocess','chat-download-agent-wise', 'offline-query-download'],
                'balance' => 'simple',
                'processes' => 2,
                'tries' => 3,
            ],
        ],

        'uat' => [
            'supervisor-1' => [
                'connection' => 'redis',
                'queue' => ['response-time-update', 'default', 'summarize', 'expire-chat-close'],
                'balance' => 'simple',
                'processes' => 3,
                'tries' => 1,
            ],
            'supervisor-2' => [
                'connection' => 'redis',
                'queue' => ['chat_archived_listener', 'auto-transfer', 'chat-notification'],
                'balance' => 'simple',
                'processes' => 2,
                'tries' => 1,
            ],
            'supervisor-3' => [
                'connection' => 'redis',
                'queue' => ['mlprocess','chat-download-agent-wise', 'offline-query-download'],
                'balance' => 'simple',
                'processes' => 1,
                'tries' => 3,
            ],
        ],

        'qa' => [
            'supervisor-1' => [
                'connection' => 'redis',
                'queue' => ['response-time-update', 'default', 'summarize', 'expire-chat-close', 'chat-notification'],
                'balance' => 'simple',
                'processes' => 3,
                'tries' => 1,
            ],
            'supervisor-2' => [
                'connection' => 'redis',
                'queue' => ['chat_archived_listener', 'auto-transfer'],
                'balance' => 'simple',
                'processes' => 3,
                'tries' => 1,
            ],

            'supervisor-3' => [
                'connection' => 'redis',
                'queue' => ['mlprocess','chat-download-agent-wise', 'offline-query-download'],
                'balance' => 'simple',
                'processes' => 1,
                'tries' => 3,
            ],

        ],

        'local' => [
            'supervisor-1' => [
                'connection' => 'redis',
                'queue' => ['response-time-update', 'default', 'chat_archived_listener','auto-transfer', 'summarize','mlprocess','chat-download-agent-wise', 'expire-chat-close', 'offline-query-download', 'chat-notification'],
                'balance' => 'simple',
                'processes' => 3,
                'tries' => 1,
            ],
        ],
    ],
];
