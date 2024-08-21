<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSummariesTableMakeNullableColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('summaries', function (Blueprint $table) {
            $table->decimal('avg_chat')->nullable(false)->change();
            $table->decimal('avg_session')->nullable(false)->change();
            $table->decimal('avg_interaction')->nullable(false)->change();
            $table->decimal('avg_first_response_time')->nullable(false)->change();
            $table->decimal('avg_response_time')->nullable(false)->change();
            $table->decimal('avg_feedback')->nullable(false)->change();
            $table->decimal('avg_online_duration')->nullable(false)->change();
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
            $table->decimal('avg_chat')->nullable()->change();
            $table->decimal('avg_session')->nullable()->change();
            $table->decimal('avg_interaction')->nullable()->change();
            $table->decimal('avg_first_response_time')->nullable()->change();
            $table->decimal('avg_response_time')->nullable()->change();
            $table->decimal('avg_feedback')->nullable()->change();
            $table->decimal('avg_online_duration')->nullable()->change();
        });
    }
}
