<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrganizationsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('company_name', 255);
            $table->string('contact_name', 255);
            $table->string('mobile_number', 15);
            $table->string('email', 80);
            $table->string('website', 100)->nullable();
            $table->string('logo', 100);
            $table->integer('seat_alloted');
            $table->string('surbo_unique_key')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->string('timezone', 100)->nullable();
            $table->unsignedInteger('created_at');
            $table->unsignedInteger('updated_at')->nullable();
            $table->unsignedInteger('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('organizations');
    }
}
