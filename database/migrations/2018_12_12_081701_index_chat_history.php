<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IndexChatHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chat_messages_history', function (Blueprint $table) {
            $table->unsignedInteger('chat_channel_id')->nullable()->change();
            $table->unsignedInteger('client_id')->nullable()->change();
            $table->unsignedInteger('user_id')->nullable()->change();
            $table->foreign('chat_channel_id')->references('id')->on('chat_channels')->onUpdate('set null')->onDelete('set null');
            $table->foreign('client_id')->references('id')->on('clients')->onUpdate('set null')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('set null')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chat_messages_history', function (Blueprint $table) {
            $table->dropForeign('chat_messages_history_chat_channel_id_foreign');
            $table->dropForeign('chat_messages_history_client_id_foreign');
            $table->dropForeign('chat_messages_history_user_id_foreign');
        });
    }
}
