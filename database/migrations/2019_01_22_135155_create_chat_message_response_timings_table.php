<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatMessageResponseTimingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_channel_response_timings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('chat_channel_id')->unique();
            $table->integer('visitor_responded_at')->nullable()->index();
            $table->integer('agent_responded_at')->nullable()->index();
            $table->foreign('chat_channel_id')->references('id')->on('chat_channels');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chat_channel_response_timings');
    }
}
