<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterChatChannelsAddAgentAssignedAt extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chat_channels', function (Blueprint $table) {
            $table->string('status')->comment('1 - UNPICKED, 2 -PICKED, 3 - TRANSFERRED, 4 - TERMINATED_BY_AGENT, 5 - TERMINATED_BY_VISITOR')->change();
            $table->integer('agent_assigned_at')->after('queued_at')->nullable();
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
            if (Schema::hasColumn('chat_channels', 'agent_assigned_at')) {
                $table->dropColumn('agent_assigned_at');
            }
        });
    }
}
