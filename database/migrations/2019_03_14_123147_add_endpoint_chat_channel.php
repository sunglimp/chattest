<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEndpointChatChannel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('chat_channels', function($table) {
            $table->string('end_point')->nullable()->after('channel_name');
            $table->string('token')->nullable()->after('channel_name');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('chat_channels', function($table) {
            $table->dropColumn('end_point');
            $table->dropColumn('token');
        });
    }
}
