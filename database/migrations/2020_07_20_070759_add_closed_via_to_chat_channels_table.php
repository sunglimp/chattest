<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddClosedViaToChatChannelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chat_channels', function (Blueprint $table) {
            $table->unsignedTinyInteger('closed_via')->after('transferred_at')->default(0)->comment = "1 - AGENT_TIMEOUT, 2 - VISITOR_TIMEOUT, 3 - VISITOR_LEFT, 4 - AGENT_FORCE_LOGOUT, 5 - AGENT_CLOSE";
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chat_channels', function (Blueprint $table) {
            $table->dropColumn('closed_via');
        });
    }
}
