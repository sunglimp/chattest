
<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class RolesTableSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        //
        $created_at = Carbon::now()->timestamp;
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('roles')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        DB::table('roles')->insert([
                ['id' => config('constants.user.role.super_admin'),
                'name' => 'Super Admin',
                'slug' => 'super-admin',
                'created_at' => $created_at,
            ],
                [
                'id' => config('constants.user.role.admin'),
                'name' => 'Admin',
                'slug' => 'admin',
                'created_at' => $created_at,
            ],
                [
                'id' => config('constants.user.role.manager'),
                'name' => 'Manager',
                'slug' => 'manager',
                'created_at' => $created_at,
            ],
                [
                'id' => config('constants.user.role.team_lead'),
                'name' => 'Team Lead',
                'slug' => 'team-lead',
                'created_at' => $created_at,
            ],
                [
                'id' => config('constants.user.role.associate'),
                'name' => 'Associate',
                'slug' => 'associate',
                'created_at' => $created_at,
            ]
        ]);
    }

}
