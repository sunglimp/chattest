<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\User;

class UpdateApiToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:apitoken';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is used for updating user api tokens';

    /**
     * Create a new command instance.
     *
     * @return void
     */

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
           User::where('status', User::IS_ACTIVE)->get()->each(function ($user) {
            $user->api_token = get_user_api_token();
            $user->save();

           });
    }
}
