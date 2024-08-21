<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterChatMessagesHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chat_messages_history', function (Blueprint $table) {
            $table->unsignedInteger('organization_id')->after('id')->nullable()->index();
            $table->unsignedInteger('group_id')->after('user_id')->nullable()->index();
            $table->mediumText('message_text')->after('internal_agent_id')->nullable();
            $table->foreign('organization_id')->references('id')->on('organizations');
            $table->foreign('group_id')->references('id')->on('groups');
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
            $table->dropColumn('organization_id');
            $table->dropColumn('group_id');
            $table->dropColumn('message_text');
        });
    }
}
