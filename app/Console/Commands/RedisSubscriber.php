<?php
/**
 * This is the class for the Subscribe Redis channels 
 * Trigger the command to start listening to the channel.
 * Subscribe method begins a long-running process
 * 
 * We'll want to run the command using a process manager 
 * like supervisor or pm2, much the same as the docs describe 
 * running queue listeners.  
 */
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use App\Jobs\ExpireChatClose;
use Illuminate\Support\Facades\Log;

class RedisSubscriber extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'redis:subscriber';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for Redis key expire subscriber';

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
     * This function is Listen Key Events
     * Example_ keyevent @*: expired </b> listens for expired messages
     * @return mixed
     */
    public function handle()
    {
        Log::debug("Start : RedisSubscriber...............");
        $redis = Redis::connection('publisher'); //Create a new instance
        $publisherDb = config('database.redis.publisher.database');
        $redis->psubscribe(['__keyevent@'.$publisherDb.'__:expired'], function ($key) {
            Log::debug('RedisSubscriber::Chat expire for channel id '.$key);
            $redisKeyDetails = explode(config('chat.chat_timeout_key_prefix'), $key);
            $channelId = $redisKeyDetails[1] ?? '';
            if($channelId) {
                ExpireChatClose::dispatch($channelId)
                    ->onQueue(config('chat.queues.expire_chat_close'));
            }
        });
        Log::debug("END : RedisSubscriber...............");
    }
}
