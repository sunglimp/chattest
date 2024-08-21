<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmailContent extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_contents', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('agent_id');
            $table->unsignedInteger('chat_channel_id');
            $table->unsignedInteger('organization_id');
            $table->string('subject');
            
            $table->longText('body');
            $table->string('content_type')->default('html');
            
            $table->unsignedInteger('created_at')->nullable();
            $table->unsignedInteger('updated_at')->nullable();
            
            $table->foreign('agent_id')
            ->references('id')->on('users')
            ->onDelete('cascade')->onUpdate('cascade');
            
            $table->foreign('chat_channel_id')
            ->references('id')->on('chat_channels')
            ->onDelete('cascade')->onUpdate('cascade');
            
            $table->foreign('organization_id')
            ->references('id')->on('organizations')
            ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('email_contents');
    }
}
