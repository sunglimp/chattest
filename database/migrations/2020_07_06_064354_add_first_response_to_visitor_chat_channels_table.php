<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFirstResponseToVisitorChatChannelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chat_channels', function (Blueprint $table) {
           $table->unsignedInteger('first_response_to_visitor')->after('accepted_at')->nullable();
           $table->unsignedInteger('waiting_time_for_visitor')->after('first_response_to_visitor')->default(0);
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
            $table->dropColumn('first_response_to_visitor');
            $table->dropColumn('waiting_time_for_visitor');
        });
    }
}
