<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PermissionsTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $created_at = Carbon::now()->timestamp;
        $data = [
            [
                'id'=>config('constants.PERMISSION.ROLE'),
                'name' => 'Roles', 'slug' => 'roles',
                'created_at' => $created_at,
                'disabled'=> 1
            ],

            [
                'id'=>config('constants.PERMISSION.CANNED-RESPONSE'),
                'name' => 'Canned Responses', 'slug' => 'canned-response',
                'created_at' => $created_at,
                'disabled'=> 1
            ],
            [
                'id'=>config('constants.PERMISSION.DASHBOARD-ACCESS'),
                'name' => 'Dashboard Access', 'slug' => 'dashboard-access',
                'created_at' => $created_at,
                'disabled'=> 1
            ],
            [
                'id'=>config('constants.PERMISSION.GROUP-CREATION'),

                'name' => 'Group Creations', 'slug' => 'group-creation',
                'created_at' => $created_at,
                'disabled'=> 0
            ],
            [
                'id'=>config('constants.PERMISSION.SUPERVISE-TIP-OFF'),
                'name' => 'Supervise & Tip Off', 'slug' => 'supervise-tip-off',
                'created_at' => $created_at,
                'disabled'=> 1
            ],
            [
                'id'=>config('constants.PERMISSION.CHAT-HISTORY'),
                'name' => 'Chat History', 'slug' => 'chat-history',
                'created_at' => $created_at,
                'disabled'=> 1
            ],
            [
                'id'=>config('constants.PERMISSION.CHAT-NOTIFIER'),
                'name' => 'Chat Notifier', 'slug' => 'chat-notifier',
                'created_at' => $created_at,
                'disabled'=> 0
            ],
            [
                'id'=>config('constants.PERMISSION.CHAT-TRANSFER'),
                'name' => 'Chat Transfer', 'slug' => 'chat-transfer',
                'created_at' => $created_at,
                'disabled'=> 1
            ],
            [
                'id'=>config('constants.PERMISSION.AUTO-CHAT-TRANSFER'),
                'name' => 'Auto Chat Transfer', 'slug' => 'auto-chat-transfer',
                'created_at' => $created_at,
                'disabled'=> 0
            ],
            [
                'id'=>config('constants.PERMISSION.CHAT-TAGS'),
                'name' => 'Chat Tags', 'slug' => 'chat-tags',
                'created_at' => $created_at,
                'disabled'=> 0
            ],
            [
                'id'=>config('constants.PERMISSION.CHAT-FEEDBACK'),
                'name' => 'Chat Feedback', 'slug' => 'chat-feedback',
                'created_at' => $created_at,
                'disabled'=> 1
            ],
            [
                'id'=>config('constants.PERMISSION.SEND-ATTACHMENT'),
                'name' => 'Send Attachments', 'slug' => 'send-attachments',
                'created_at' => $created_at,
                'disabled'=> 0
            ],
            [
                'id'=>config('constants.PERMISSION.TIMEOUT'),
                'name' => 'Timeout', 'slug' => 'timeout',
                'created_at' => $created_at,
                'disabled'=> 0
            ],
            [
                'id'=>config('constants.PERMISSION.INTERNAL-COMMENTS'),
                'name' => 'Internal Comments', 'slug' => 'internal-comments',
                'created_at' => $created_at,
                'disabled'=> 1

            ],[
                'id'=>config('constants.PERMISSION.DOWNLOAD-REPORT'),
                'name' => 'Download Report', 'slug' => 'download-report',
                'created_at' => $created_at,
                'disabled'=> 1
            ],
            [
                'id'=>config('constants.PERMISSION.EMAIL'),
                'name' => 'Email', 'slug' => 'email',
                'created_at' => $created_at,
                'disabled'=> 0
            ],
            [
                'id'=>config('constants.PERMISSION.CHAT'),
                'name' => 'Chat', 'slug' => 'chat',
                'created_at' => $created_at,
                'disabled'=> 1
            ],

            [
                'id'=>config('constants.PERMISSION.BAN-USER'),
                'name' => 'Ban User', 'slug' => 'ban-user',
                'created_at' => $created_at,
                'disabled'=> 0
            ],
            [
                'id'=>config('constants.PERMISSION.OFFLINE-FORM'),
                'name' => 'Offline Form', 'slug' => 'offline-form',
                'created_at' => $created_at,
                'disabled'=> 0
            ],
            [
                'id'=>config('constants.PERMISSION.TMS-KEY'),
                'name' => 'Surbo ACE Integration', 'slug' => 'surbo_ace_integration',
                'created_at' => $created_at,
                'disabled'=> 0
            ],
            [
                'id'=>config('constants.PERMISSION.CLASSIFIED-CHAT'),
                'name' => 'Classified Chat', 'slug' => 'classified_chat',
                'created_at' => $created_at,
                'disabled'=> 0
            ],
            [
                'id'=>config('constants.PERMISSION.AUDIO-NOTIFICATION'),
                'name' => 'Audio Notification', 'slug' => 'audio_notification',
                'created_at' => $created_at,
                'disabled'=> 0
            ],
            [
                'id'=>config('constants.PERMISSION.LOGIN_HISTORY'),
                'name' => 'Login History', 'slug' => 'login_history',
                'created_at' => $created_at,
                'disabled'=> 1
            ],[
                 'id'=>config('constants.PERMISSION.SNEAK'),
                 'name' => 'Sneak User', 'slug' => 'sneak_user',
                 'created_at' => $created_at,
                 'disabled'=> 1
             ],
            [
                 'id'=>config('constants.PERMISSION.CHAT-DOWNLOAD'),
                 'name' => 'Chat Download', 'slug' => 'chat_download',
                 'created_at' => $created_at,
                 'disabled'=> 0
             ],
            [
                 'id'=>config('constants.PERMISSION.SESSION_TIMEOUT'),
                 'name' => 'Session Timeout', 'slug' => 'session_timeout',
                 'created_at' => $created_at,
                 'disabled'=> 0
             ],
            [
                 'id'=>config('constants.PERMISSION.ARCHIVE_CHAT'),
                 'name' => 'Archive Chat', 'slug' => 'archive_chat',
                 'created_at' => $created_at,
                 'disabled'=> 0
             ],
             [
                 'id'=>config('constants.PERMISSION.IDENTIFIER-MASKING'),
                 'name' => 'Identifier Masking', 'slug' => 'identifier_masking',
                 'created_at' => $created_at,
                 'disabled'=> 1
             ],
            [
                 'id'=>config('constants.PERMISSION.MISSED_CHAT'),
                 'name' => 'Missed Chat', 'slug' => 'missed_chat',
                 'created_at' => $created_at,
                 'disabled'=> 0
             ],
             [
                 'id'=>config('constants.PERMISSION.CUSTOMER-INFORMATION'),
                 'name' => 'Customer Information', 'slug' => 'customer_information',
                 'created_at' => $created_at,
                 'disabled'=> 0
             ],
        ];
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('permissions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        DB::table('permissions')->insert($data);
    }
}
