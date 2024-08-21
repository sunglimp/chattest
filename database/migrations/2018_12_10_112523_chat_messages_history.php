<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChatMessagesHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('chat_messages_history', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('chat_channel_id');
            $table->unsignedInteger('client_id');
            $table->unsignedInteger('user_id');
            $table->json('message');
            $table->integer('read_at')->nullable();
            $table->string('recipient')->comment('can be a visitor, an agent or supervisior');
            $table->string('message_type')->default('public')->comment('possible values are public, internal');
            $table->integer('created_at')->unsigned();
            $table->integer('updated_at')->unsigned();
            $table->integer('deleted_at')->unsigned()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chat_messages_history');
    }
}
