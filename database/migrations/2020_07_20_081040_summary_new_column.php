<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SummaryNewColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('summaries', function (Blueprint $table) {
            $table->integer('count_queued_left')->nullable()->default(0)->comment('Count of chat timeouts in queue')->change();
            $table->integer('count_insession_timeout')->after('count_queued_left')->nullable()->default(0)->comment('Count of chat timeouts in awaiting list');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('summaries', function (Blueprint $table) {
            $table->dropColumn('count_insession_timeout');

        });
    }
}
