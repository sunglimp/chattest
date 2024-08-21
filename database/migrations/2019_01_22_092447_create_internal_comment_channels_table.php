<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInternalCommentChannelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('internal_comment_channels', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('chat_channel_id');
            
            $table->unsignedInteger('internal_agent_id');
            
            $table->foreign('internal_agent_id')
            ->references('id')->on('users')
            ->onDelete('cascade')->onUpdate('cascade');
            
            $table->foreign('chat_channel_id')
            ->references('id')->on('chat_channels')
            ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('internal_comment_channels');
    }
}
