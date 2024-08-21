<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableOfflineForms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('offline_forms', function (Blueprint $table) {
        $table->Integer('offline_requester_detail_id')->after('identifier')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('offline_forms', function (Blueprint $table) {
            $table->dropColumn(['offline_requester_detail_id']);
        });
    }
}
