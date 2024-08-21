<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddResponseWithinChatHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chat_messages_history', function (Blueprint $table) {
            $table->unsignedSmallInteger('response_within')->after('message_type')->default(0)->comment('show agent response delay in seconds');
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
            $table->dropColumn('response_within');
        });
    }
}
