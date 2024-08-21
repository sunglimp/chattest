<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableChatChannels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chat_channels', function (Blueprint $table) {
            if (Schema::hasColumn('chat_channels', 'root_parent_id')) {
                $table->renameColumn('root_parent_id', 'root_channel_id');
            }
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
            if (Schema::hasColumn('chat_channels', 'root_channel_id')) {
                $table->renameColumn('root_channel_id', 'root_parent_id');
            }
        });
    }
}
