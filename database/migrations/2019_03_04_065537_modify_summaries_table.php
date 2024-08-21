<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifySummariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('summaries', function (Blueprint $table) {
//            $table->string('name', 50)->nullable()->change();


//            $table->unsignedInteger('organization_id')->change();
//            $table->unsignedInteger('agent_id')->nullable()->change();
            $table->integer('count_chat')->nullable()->change();
            $table->integer('count_chat_transferred')->nullable()->change();
            $table->integer('count_chat_resolved')->nullable()->change();
            $table->integer('count_chat_missed')->nullable()->change();
            $table->integer('count_email_sent')->nullable()->change();
            $table->integer('count_chat_terminated_by_agent')->nullable()->change();
            $table->integer('count_chat_terminated_by_visitor')->nullable()->change();
            $table->integer('count_queued_visitor')->nullable()->change();
            $table->integer('count_entered_chat')->nullable()->change();
            $table->integer('count_queued_left')->nullable()->change();
            $table->decimal('avg_chat')->nullable()->change();
            $table->decimal('avg_session')->nullable()->change();
            $table->decimal('avg_interaction')->nullable()->change();
            $table->decimal('avg_first_response_time')->nullable()->change();
            $table->decimal('avg_response_time')->nullable()->change();
            $table->decimal('avg_feedback')->nullable()->change();
            $table->decimal('avg_online_duration')->nullable()->change();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
