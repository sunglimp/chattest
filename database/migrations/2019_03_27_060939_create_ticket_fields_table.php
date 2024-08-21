<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ticket_fields', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('application_id');
            $table->string('application_name');
            $table->unsignedInteger('organization_id');
            $table->json('fields_data');
            
            $table->foreign('organization_id')
            ->references('id')->on('organizations')
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
        Schema::dropIfExists('ticket_fields');
    }
}
