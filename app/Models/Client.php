<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    
    protected $dateFormat = 'U';
    public $timestamps = true;
    protected $fillable = ['raw_info','organization_id','identifier'];
    const BANNED_STATUS = 1;
    const UNBAN_STATUS = 0;


    
    /*public static function displayName($clientId)
    {
        return json_decode(Client::find($clientId)->raw_info, true)['name'];
    }
    */

    public static function details($clientId)
    {
        
        $client = Client::find($clientId);
        $aInfo = json_decode($client->raw_info, true);
        return ['raw_info' => $aInfo, 'name' => $client->identifier ?? 'Guest', 'has_history' => ($client->updated_at > $client->created_at ? 1 : 0) ];
    }
}
