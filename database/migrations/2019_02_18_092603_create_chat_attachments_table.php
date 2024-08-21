<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_attachments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('hash_name');
            $table->string('original_name');
            $table->unsignedInteger('size');
            $table->string('path');
            $table->unsignedInteger('chat_message_id');
            $table->foreign('chat_message_id')->references('id')->on('chat_messages');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chat_attachments');
    }
}
