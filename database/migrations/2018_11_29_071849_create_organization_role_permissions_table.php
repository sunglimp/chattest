<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrganizationRolePermissionsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organization_role_permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('organization_id')->nullable();
            $table->unsignedInteger('role_id')->nullable();
            $table->unsignedInteger('permission_id')->nullable();
            $table->unsignedInteger('created_by');
            $table->foreign('organization_id')
                ->references('id')->on('organizations')
                ->onDelete('cascade')->onUpdate('cascade');

            $table->foreign('role_id')
                ->references('id')->on('roles')
                ->onDelete('set null');

            $table->foreign('permission_id')
                ->references('id')->on('permissions')
                ->onDelete('set null');

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
        Schema::dropIfExists('organization_role_permissions');
    }

}
