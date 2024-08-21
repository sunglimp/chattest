<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSummariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('summaries', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('organization_id')->index('summary_organization_id');
            $table->unsignedInteger('agent_id')->nullable()->index('summary_agent_id');
            $table->integer('count_chat')->comment("Number of chats on daily basis");
            $table->integer('count_chat_transferred')->comment("Number of chats transferred on daily basis");
            $table->integer('count_chat_resolved')->comment("Number of chats resolved on daily basis");
            $table->integer('count_chat_missed')->comment("Number of chats missed on daily basis");
            $table->integer('count_email_sent')->comment("Number of email sent on daily basis");
            $table->integer('count_chat_terminated_by_agent')->comment("Number of chats terminated by agent on daily basis");
            $table->integer('count_chat_terminated_by_visitor')->comment("Number of chats terminated by visitor on daily basis");
            $table->integer('count_queued_visitor')->comment("Number of chats queued on daily basis");
            $table->integer('count_entered_chat')->comment("Number of chats entered on daily basis");
            $table->integer('count_queued_left')->comment("Number of chats left the queue on daily basis");
            $table->integer('avg_chat')->comment("Average number of chats on daily basis");
            $table->integer('avg_session')->comment("Average number of chats session on daily basis");
            $table->integer('avg_interaction')->comment("Average number of chats interaction on daily basis");
            $table->decimal('avg_first_response_time')->comment("Average number of chat first response time on daily basis");
            $table->decimal('avg_response_time')->comment("Average number of chats response time on daily basis");
            $table->decimal('avg_feedback')->comment("Average number of chats feedback on daily basis");
            $table->integer('avg_online_duration')->comment("Average number of agent online duration on daily basis");
            
            
            $table
                    ->foreign('organization_id')
                    ->references('id')
                    ->on('organizations');
            
            $table
                    ->foreign('agent_id')
                    ->references('id')
                    ->on('users');
            
            $table->date('summary_date')->index();
            $table->unique(['organization_id', 'agent_id', 'summary_date']);
            
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('summaries');
    }
}
