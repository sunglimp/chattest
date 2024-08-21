<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableSummaries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::table('summaries', function (Blueprint $table) {
            $table->integer('count_chat')->default(0)->change();
            $table->integer('count_chat_transferred')->default(0)->change();
            $table->integer('count_chat_resolved')->default(0)->change();
            $table->integer('count_chat_missed')->default(0)->change();
            $table->integer('count_email_sent')->default(0)->change();
            $table->integer('count_chat_terminated_by_agent')->default(0)->change();
            $table->integer('count_chat_terminated_by_visitor')->default(0)->change();
            $table->integer('count_queued_visitor')->default(0)->change();
            $table->integer('count_entered_chat')->default(0)->change();
            $table->integer('count_queued_left')->default(0)->change();
            $table->integer('avg_chat')->default(0)->change();
            $table->integer('avg_session')->default(0)->change();
            $table->integer('avg_interaction')->default(0)->change();
            $table->decimal('avg_first_response_time')->default(0)->change();
            $table->decimal('avg_response_time')->default(0)->change();
            $table->decimal('avg_feedback')->default(0)->change();
            $table->integer('avg_online_duration')->default(0)->change();
            $table->integer('online_duration')->default(0)->comment("Online duration of agent");
         });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //Down't want to revert must be there in the first release
    }
}
