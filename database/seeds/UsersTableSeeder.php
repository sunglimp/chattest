<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Faker\Generator as Faker;

class UsersTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $created_at = Carbon::now()->timestamp;

        \DB::table('users')->insert([
            //super Admin
                [
                'organization_id' => null,
                'name' => 'Super Admin',
                'password' => Hash::make('Admin@123'),
                'mobile_number' => '9090909090',
                'email' => 'superadmin@vfirst.com',
                'gender' => 'male',
                'image' => '',
                'role_id' => 1,
                'remember_token' => '0HCzHxubEBwTdyvaxUKWYiOxj8YWste41ld51A5lXx0VomE97igI6sajwpbp',
                'created_at' => $created_at,
                'updated_at' => null,
                'deleted_at' => null,
                'timezone'=>'Asia/Kolkata',
                'language' => 'en'    
                ]
        ]);
    }
}
