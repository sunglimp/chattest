<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfflineRequesterDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offline_requester_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('group_id');
            $table->json('client_info');
            $table->json('transcript');
            $table->unsignedInteger('organization_id');
            $table->integer('agent_id')->nullable();
            $table->string('source_type');
            $table->tinyInteger('status')->default(1)->comment = "1 - UNPICKED, 2 - PICKED, 3 - REJECTED";
            $table->integer('created_at');
            $table->integer('updated_at');
            $table->integer('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {      
            Schema::dropIfExists('offline_requester_details');
    }
}
