<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmailRecipientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   
        Schema::create('email_recipients', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email_address');
            $table->string('recipient_type');
            $table->unsignedInteger('email_content_id');
            
            $table->foreign('email_content_id')
            ->references('id')->on('email_contents')
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
        Schema::dropIfExists('email_recipients');
    }
}
