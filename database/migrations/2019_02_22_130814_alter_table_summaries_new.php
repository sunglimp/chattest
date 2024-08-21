<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableSummariesNew extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('summaries', function (Blueprint $table) {
            $table->decimal('avg_session')->comment("Average number of chats session on daily basis")->change();
            $table->decimal('avg_interaction')->comment("Average number of chats interaction on daily basis")->change();
            $table->decimal('avg_first_response_time')->comment("Average number of chat first response time on daily basis")->change();
            $table->decimal('avg_response_time')->comment("Average number of chats response time on daily basis")->change();
            $table->decimal('avg_feedback')->comment("Average number of chats feedback on daily basis")->change();
            $table->decimal('avg_online_duration')->comment("Average number of agent online duration on daily basis")->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('summaries', function (Blueprint $table) {
            //
        });
    }
}
