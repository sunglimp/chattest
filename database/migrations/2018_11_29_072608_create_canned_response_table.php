<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCannedResponseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('canned_responses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('shortcut', 45);
            $table->text('response');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('organization_id')->nullable();
            $table->tinyInteger('is_admin_response');
            $table->unsignedInteger('created_at');
            $table->unsignedInteger('updated_at')->nullable();
            $table->unsignedInteger('deleted_at')->nullable();
            
            $table->foreign('organization_id')
            ->references('id')->on('organizations')
            ->onDelete('cascade')->onUpdate('cascade');
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('canned_responses');
    }
}
