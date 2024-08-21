<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatTags extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_tags', function (Blueprint $table) {
            $table->increments('id');
            $table->string('tag_name');
            $table->unsignedInteger('tag_id');
            $table->unsignedInteger('chat_channel_id');
            $table->foreign('tag_id')
            ->references('id')->on('tags')
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
        Schema::dropIfExists('chat_tags');
    }
}
