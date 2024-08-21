<?php

use Illuminate\Database\Seeder;
use  App\Models\Organization;
use App\Models\PermissionSetting;

class OrganizationSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //

        $orgganizationData= Organization::all();
        $banUser=json_encode(["days"=>1]);
        $message=json_encode(["message"=>"No Agent Online."]);

        foreach ($orgganizationData as $key =>$value)
        {
            PermissionSetting::firstOrCreate(
                ['organization_id' => $value->id,'permission_id'=>config('constants.PERMISSION.BAN-USER')],
                ['settings' => $banUser]);
            PermissionSetting::firstOrCreate(
                ['organization_id' => $value->id,'permission_id'=>config('constants.PERMISSION.OFFLINE-FORM')],
                ['settings' => $message]);

        }

//        DB::table('permissions')->insert($data);

    }
}
