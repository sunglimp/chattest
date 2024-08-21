<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Redis;

class Group extends Model
{

    protected $dateFormat = 'U';
    public $timestamps    = true;
    
    protected $fillable   = ['name', 'organization_id', 'created_by'];

    public static function boot()
    {
        parent::boot();
        
        /**
         * Filter Online Agent
         *
         * Filter all the groups where atleast one user is online.
         */
        Collection::macro('filterOnlineAgent', function ($excludeUserId) {
            return $this->filter(function ($group) use ($excludeUserId) {
                $members = Redis::smembers('group_' . $group->id);
                $memberCount = count($members);
                return (($memberCount == 1) && !in_array($excludeUserId, $members)) or $memberCount > 1;
            });
        });
    }
    
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function members()
    {
        return $this->hasMany(UserGroup::class);
    }

    public function getFromCache()
    {
        dd($this->id);
    }
    
    /**
     * Get group while edit user.
     *
     * @param integer $organizationId
     * @return Collection
     */
    public static function getGroup($organizationId, $userId)
    {

        $groups = Group::select('groups.id', 'groups.name', DB::raw('IF(user_groups.id IS NOT NULL,1,0) as selected'))
        ->leftJoin('user_groups', function ($query) use ($userId) {
                            $query->on('user_groups.group_id', '=', 'groups.id')
                            ->where('user_groups.user_id', '=', $userId);
        })
                        ->where('organization_id', $organizationId)
                        ->where('name', '!=', 'Default')
                        ->distinct('groups.id')
                        ->get();

        
        return $groups;
    }
    
    public static function getOrganizationIdByGroup($groupId)
    {
        $id = Group::where('id', $groupId)->first();
        $organizationId = $id->organization_id;
        return $organizationId;
    }
    
    /**
     * 
     */
    public static function isGroupUsed($group_id)
    {
        return Group::has('members')->where('id', $group_id)->get()->toArray();
    }
    
    /**
     * Function to get the user online status based on organization groups
     * 
     * @param type $organizationId
     * @param type $id
     * @return boolean
     */
    
    public static function getOnlineStatus($organizationId, $id)
    {
        $status = false;
        $groups = Group::where('organization_id', $organizationId);
                if ($id) {
                    $groups->where('id', $id);
                }
                $groupIds = $groups->pluck('id');

                foreach ($groupIds as $groupId) {
                    if (Redis::smembers('group_' . $groupId)) {
                        $status = true;
                        break;
                    }
                }      
       return $status; 
    }
}
