<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateDailySummarySp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("DROP procedure IF EXISTS usp_get_daily_summary");
        DB::unprepared("CREATE PROCEDURE `usp_get_daily_summary`()
        BEGIN 
        SELECT 
            tmp1.org_id,
            tmp1.company_name,
            tmp1.seat_alloted,
            tmp1.agents as  user_id_count,
            tmp1.agents_online,
            tmp1.today_chat,
            tmp2.month_chat,
            tmp3.lifetime_chat
        FROM
            (SELECT 
                organizations.id AS org_id,
                    company_name,
                    seat_alloted,
                    COUNT(DISTINCT (users.id)) AS agents,
                    COUNT(DISTINCT (user_logins.user_id)) AS agents_online,
                    COUNT(ch.id) AS today_chat
            FROM
                organizations
            LEFT JOIN users ON (users.organization_id = organizations.id
                AND users.role_id NOT IN(".config('constants.user.role.super_admin').",".config('constants.user.role.admin')."))
            LEFT JOIN user_logins ON (user_logins.user_id = users.id
                AND DATE(FROM_UNIXTIME(user_logins.created_at)) = CURDATE() - INTERVAL 1 DAY)
            LEFT JOIN chat_channels ch ON (ch.agent_id = users.id
                AND DATE(FROM_UNIXTIME(ch.created_at)) = CURDATE() - INTERVAL 1 DAY)
            GROUP BY organizations.id) tmp1
                INNER JOIN
            (SELECT 
                organizations.id AS org_id, COUNT(ch.id) AS month_chat
            FROM
                organizations
            LEFT JOIN users ON (users.organization_id = organizations.id
                AND users.role_id NOT IN(".config('constants.user.role.super_admin').",".config('constants.user.role.admin')."))
            LEFT JOIN chat_channels ch ON (ch.agent_id = users.id
                AND MONTH(DATE(FROM_UNIXTIME(ch.created_at))) = MONTH(CURDATE()))
            GROUP BY organizations.id) tmp2 ON tmp1.org_id = tmp2.org_id
                INNER JOIN
            (SELECT 
                organizations.id AS org_id, COUNT(ch.id) AS lifetime_chat
            FROM
                organizations
            LEFT JOIN users ON (users.organization_id = organizations.id
                AND users.role_id NOT IN(".config('constants.user.role.super_admin').",".config('constants.user.role.admin')."))
            LEFT JOIN chat_channels ch ON (ch.agent_id = users.id)
            GROUP BY organizations.id) tmp3 ON tmp2.org_id = tmp3.org_id;
        END");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared("DROP procedure IF EXISTS usp_get_daily_summary");
    }
}
