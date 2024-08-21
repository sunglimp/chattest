<?php

use Illuminate\Database\Seeder;
use App\Models\Client;

class ClientIdentifierUpdateTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data= Client::whereNull('organization_id')->select('raw_info->name as name','id')->get();
        
        foreach($data as $client){
            $update_name = str_replace('"', '', $client->name);
            $name = Client::find($client->id);
            $name->identifier = $update_name;
            $name->save();         
        }
    }
}
