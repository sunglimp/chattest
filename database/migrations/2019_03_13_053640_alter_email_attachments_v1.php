<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterEmailAttachmentsV1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('email_attachments', function (Blueprint $table) {
            if (!Schema::hasColumn('email_attachments', 'attachment_unit')) {
                 $table->string('attachment_unit', 45)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('email_attachments', function (Blueprint $table) {
            if (Schema::hasColumn('email_attachments', 'attachment_unit')) {
                $table->dropColumn('attachment_unit');
            }
        });
    }
}
