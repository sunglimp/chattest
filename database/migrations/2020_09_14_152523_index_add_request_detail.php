<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IndexAddRequestDetail extends Migration
{
    public function up()
    {
        Schema::table('offline_requester_details', function (Blueprint $table) {
            $table->index('group_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('offline_requester_details', function (Blueprint $table) {
            $table->dropIndex(['group_id']);
            $table->dropIndex(['created_at']);
        });
    }
}
