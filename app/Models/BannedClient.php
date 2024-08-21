<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Doctrine\DBAL\Query\QueryBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Redis;

class BannedClient extends Model
{
    protected $dateFormat = 'U';
    public $timestamps = false;
    
    protected $fillable = ['banned_at', 'ban_expired_at', 'client_id', 'banned_by'];
    
    /**
     * Function to ban client.
     *
     * @param integer $expires
     * @param integer $agentId
     * @param integer $clientId
     * @throws \Exception
     */
    public static function ban($expires, $agentId, $clientId, $bannedAt)
    {
        try {
            $bannedClient = new self();
            $bannedClient->banned_at = $bannedAt;
            $bannedClient->ban_expired_at = $expires;
            $bannedClient->banned_by = $agentId;
            $bannedClient->client_id = $clientId;
            return $bannedClient->save();
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
    
   /**
    * Function to get banned client list.
    *
    * @param array $requestParams
    * @param integer $organizationId
    * @throws \Exception
    * @return Collection banned clients
    */
    public static function list($requestParams, $organizationId)
    {
        try {
            $query = BannedClient::select(DB::raw('DISTINCT(identifier)'), 'banned_at', 'banned_clients.client_id', 'raw_info','source_type')
                        ->join('clients', 'clients.id', '=', 'banned_clients.client_id')
                        ->join('chat_channels', 'chat_channels.client_id', '=', 'banned_clients.client_id')
                        ->where('clients.organization_id', $organizationId);
            if ($requestParams['keyword'] != "") {
                self::searchBannedClientByKeyword($requestParams, $query);
            }
            if (!empty($requestParams['start_date']) && $requestParams['end_date']) {
                self::searchBannedListByDate($requestParams, $query);
            }
            $bannedClients = $query->groupBy('identifier')->paginate();
            return $bannedClients;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
    
    /**
     * Function to create query for banned list searching by keyword.
     *
     * @param array $requestParams
     * @param QueryBuilder $query
     * @throws \Exception
     */
    private static function searchBannedClientByKeyword($requestParams, $query)
    {
        try {
            if (!empty($requestParams['search'])) {
                if ($requestParams['keyword'] == config('constants.BANNED_CLIENTS.TEXT_SEARCH')) {
                    $searchText = strtolower($requestParams['search']);
                    $query->join('chat_messages_history', 'chat_messages_history.client_id', '=', 'clients.id')
                    ->where(function ($query) use ($searchText) {
                        $query->where(DB::raw('lower(JSON_EXTRACT(message, "$.text"))'), 'like', "%".$searchText."%")
                        ->orWhere(DB::raw('lower(clients.identifier)'), 'like', "%".$searchText."%");
                    });
                } elseif ($requestParams['keyword'] == config('constants.BANNED_CLIENTS.AGENT_SEARCH')) {
                    $query->join('users', 'users.id', '=', 'banned_clients.banned_by')
                    ->where('name', 'like', "%".$requestParams['search']."%");
                }
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
    
    /**
     * Function to create query for banned list searching by keyword.
     *
     * @param array $requestParams
     * @param QueryBuilder $query
     * @throws \Exception
     */
    private static function searchBannedListByDate($requestParams, $query)
    {
        try {
            $startDate = date('Y-m-d', strtotime($requestParams['start_date']));
            $endDate   = date('Y-m-d', strtotime($requestParams['end_date']));
            $query->whereBetween(DB::raw('DATE(FROM_UNIXTIME(banned_at))'), [$startDate, $endDate]);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
    
    /**
     * Fucntion to unban client.
     *
     * @param integer $clientId
     * @throws \Exception
     */
    public static function unBanClient($clientId)
    {
        try {
            $bannedClient = Client::where('id', $clientId)->select('identifier', 'organization_id')->first();

            Redis::del("ban_user_".$bannedClient->organization_id."_".strtolower($bannedClient->identifier));

                self::where('client_id', $clientId)->delete();
                return true;
        } catch(\Exception $exception) {
            throw $exception;
        }
    }
    
    /**
     * Function to unban on daily basis.
     *
     * @throws \Exception
     */
    public static function unbanDaily()
    {
        try {
            
            self::whereRaw('DATE(from_unixtime(ban_expired_at)) <= UTC_DATE()')->get()->each(function ($client) {
                self::unBanClient($client->client_id);
            });
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
}
