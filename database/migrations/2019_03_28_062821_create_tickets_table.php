<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('ticket_id')->nullable();
            $table->unsignedInteger('chat_id');
            $table->unsignedInteger('application_id');
            $table->unsignedInteger('organization_id');
            $table->json('ticket_data');
            
            $table->foreign('organization_id')
            ->references('id')->on('organizations')
            ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('chat_id')
            ->references('id')->on('chat_channels')
            ->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedInteger('created_at');
            $table->unsignedInteger('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tickets');
    }
}
