<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IndexAddOfflineForm extends Migration
{
    
    public function up()
    {
        Schema::table('offline_forms', function (Blueprint $table) {
            $table->unsignedInteger('offline_requester_detail_id')->index()->nullable()->default(null)->change();
        });
    }

    public function down()
    {
        Schema::table('offline_forms', function (Blueprint $table) {
            $table->integer('offline_requester_detail_id')->nullable()->default(null)->change();
            $table->dropIndex(['offline_requester_detail_id']);
        });
    }
}
