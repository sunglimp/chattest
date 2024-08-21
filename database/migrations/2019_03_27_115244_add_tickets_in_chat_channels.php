<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTicketsInChatChannels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chat_channels', function (Blueprint $table) {
            //
            $table->string('ticket_type')->after('status')->nullable();
            $table->tinyInteger('ticket_status')->after('status')->nullable()->comment('1- Pending , 0- Reject , 2- Success');

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
            //
            $table->dropColumn('ticket_type');
            $table->dropColumn('ticket_status');
        });
    }
}
