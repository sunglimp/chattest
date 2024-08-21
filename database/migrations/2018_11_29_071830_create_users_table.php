<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('mobile_number', 22)->nullable();
            $table->unsignedInteger('organization_id')->nullable();
            $table->unsignedInteger('report_to')->nullable();
            $table->unsignedInteger('role_id');
            $table->string('gender', 11)->default('male');
            $table->string('image', 200)->nullable();
            $table->smallInteger('no_of_chats')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->string('timezone', 100);

            $table->foreign('organization_id')->references('id')->on('organizations')
                ->onUpdate('set null')->onDelete('set null');

            $table->foreign('report_to')->references('id')->on('users')
                ->onUpdate('set null')->onDelete('set null');

            $table->foreign('role_id')
                ->references('id')->on('roles')
                ->onDelete('cascade');

            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
