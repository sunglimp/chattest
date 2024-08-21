<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableChatChannelsAddColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chat_channels', function (Blueprint $table) {
            $table->integer('queued_at')->after('status')->nullable()->index();
            $table->integer('transferred_at')->after('accepted_at')->nullable()->index();
            $table->integer('terminated_at')->after('accepted_at')->nullable()->index();
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
            $table->dropColumn('queued_at');
            $table->dropColumn('transferred_at');
            $table->dropColumn('terminated_at');
        });
    }
}
