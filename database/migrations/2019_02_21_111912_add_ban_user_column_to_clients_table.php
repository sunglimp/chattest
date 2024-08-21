<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBanUserColumnToClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('identifier', 255)->after('id');
            $table->unsignedInteger('organization_id')->after('id')->nullable()->index();
            
            Schema::enableForeignKeyConstraints();
            $table->foreign('organization_id')->references('id')->on('organizations');
            $table->dropColumn(['email', 'mobile']);
            Schema::disableForeignKeyConstraints();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('email', 100)->after('id');
            $table->string('mobile', 50)->after('email');
            $table->dropForeign('clients_organization_id_foreign');
        });
    }
}
