<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMysqlMaskIdentifierFunction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("DROP FUNCTION IF EXISTS mask_identifier");
        DB::unprepared("CREATE FUNCTION `mask_identifier`(identifier VARCHAR(150)) RETURNS VARCHAR(150) DETERMINISTIC
                        BEGIN
                           DECLARE identifierMasked VARCHAR(150);
                           DECLARE identifierLength VARCHAR(150);
                           DECLARE identifierSearch VARCHAR(150);
                        SET
                           identifierLength = LENGTH(identifier);
                        IF (identifierLength = 1)
                        THEN
                           SET
                              identifierMasked = '*';
                        ELSEIF identifierLength = 2
                        THEN
                           SET
                              identifierMasked = CONCAT(LEFT (identifier, 1), '*');
                        ELSEIF identifierLength = 3
                        THEN
                           SET
                              identifierMasked = CONCAT(LEFT(identifier, 1), '*', RIGHT(identifier, 1));
                        ELSEIF identifierLength = 4
                        THEN
                           SET
                              identifierMasked = CONCAT(LEFT(identifier, 1), '**', RIGHT(identifier, 1));
                        ELSEIF identifierLength >= 5
                        THEN
                           SET
                              identifierSearch = SUBSTRING(identifier, 3, LENGTH(SUBSTRING(identifier, 3)) - 2);
                           SET
                              identifierMasked = CONCAT(LEFT(identifier, 2), REPLACE(identifierSearch, identifierSearch, REPEAT('*', LENGTH(identifierSearch))), RIGHT(identifier, 2));
                        END
                        IF;
                        RETURN (identifierMasked);
                        END");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared("DROP FUNCTION IF EXISTS mask_identifier");
    }
}
