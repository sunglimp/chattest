<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MissedChatActions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('missed_chat_actions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('chat_channel_id')->index();
            $table->string('template_id');
            $table->tinyInteger('status')->default(0)->comment('1- WA Pushed, 2- Rejected');
            $table->unsignedInteger('created_at');
            $table->unsignedInteger('updated_at')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('missed_chat_actions');
    }
}
