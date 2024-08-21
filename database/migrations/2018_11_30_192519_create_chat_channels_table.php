<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatChannelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_channels', function (Blueprint $table) {
            $table->increments('id');
            $table->string('channel_name');
            $table->unsignedInteger('agent_id')->nullable();
            $table->unsignedInteger('group_id');
            $table->unsignedInteger('parent_id')->nullable();
            $table->json('subscriber_info');
            $table->tinyInteger('status')->default(1)->comment = "1 - UNPICKED, 2 - PICKED, 3 - TRANSFERRED, 4 - TERMINATED";
            $table->integer('accepted_at')->nullable();
            $table->integer('created_at');
            $table->integer('updated_at');
            $table->integer('deleted_at')->nullable();
            
            $table->foreign('agent_id')
                    ->references('id')->on('users');
            
            $table->foreign('group_id')
                    ->references('id')->on('groups');
            
            $table->foreign('parent_id')
                    ->references('id')->on('chat_channels');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chat_channels');
    }
}
