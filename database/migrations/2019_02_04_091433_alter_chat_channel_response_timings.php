<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterChatChannelResponseTimings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chat_channel_response_timings', function (Blueprint $table) {
            $table->unsignedInteger('agent_first_responded_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chat_channel_response_timings', function (Blueprint $table) {
            $table->dropColumn('agent_first_responded_at');
        });
    }
}
