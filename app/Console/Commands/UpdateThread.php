<?php

namespace App\Console\Commands;

use App\Models\ {
    ChatChannel,
    ChatMessage,
    ChatMessagesHistory
};
use Illuminate\Console\Command;

class UpdateThread extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'onetime:thread';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        
        ChatMessagesHistory::where('message_type','!=', 'BOT')
                ->where('message_type', '!=', 'transfer')
                ->whereNull('thread')->orderBy('created_at', 'asc')->each(function($history){
                   
                   $threadKey = 'thread_' . $history->chat_channel_id;
                   if($history->recipient == ChatMessage::RECIPIENT_AGENT)
                    {
                        $thread = str_random(50);
                        cache([$threadKey => $thread], 120);
                    }
                    elseif($history->recipient == ChatMessage::RECIPIENT_VISITOR)
                    {
                        $thread = cache($threadKey);
                        
                        if(empty($thread))
                        {
                            $thread = str_random(50);
                            cache([$threadKey => $thread], 120);
                        }
                    }
                    $history->thread = $thread;
                    $history->save();
        });
    }
}
