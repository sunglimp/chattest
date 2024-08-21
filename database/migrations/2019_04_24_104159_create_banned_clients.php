<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBannedClients extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('banned_clients', function (Blueprint $table) {
           $table->unsignedInteger('banned_at');
           $table->unsignedInteger('ban_expired_at');
           $table->unsignedInteger('banned_by');
           $table->unsignedInteger('client_id');
           
           $table->foreign('client_id')
           ->references('id')->on('clients')
           ->onDelete('cascade')->onUpdate('cascade');
           
           $table->foreign('banned_by')
           ->references('id')->on('users')
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
        Schema::dropIfExists('banned_clients');
    }
}
