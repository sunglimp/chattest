<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Doctrine\DBAL\Query\QueryBuilder;

class Tag extends Model
{
    use SoftDeletes;
    
    protected $dateFormat = 'U';
    public $timestamps = true;
    
    protected $fillable = ['name', 'user_id', 'organization_id', 'is_admin_tag'];
    protected $dates = ['deleted_at'];
    protected $appends = ['tag_id'];
    
    /**
     * FUnction to delete tags.
     *
     * @param integer $tagId
     * @throws \Exception
     */
    public static function deleteTags($tagId)
    {
        try {
            $tag = Tag::find($tagId);
            if (!empty($tag)) {
                self::unlinkTagChats($tagId);
                return Tag::find($tagId)->delete();
            } else {
                return false;
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
    
    /**
     * Function to link chat with tags.
     *
     * @param Tag $isTagAdded
     * @param array $requestParams
     */
    public static function linkChatWithTags($isTagAdded, $requestParams)
    {
        try {
            $tagId = $isTagAdded->id ?? null;
            $channelId = $requestParams['channelId'] ?? null;
            $tagName = $isTagAdded->name ?? '';
            
            $isLinked = self::checkChatTagLink($tagId, $channelId);
            if (empty($isLinked)) {
                return ChatTags::create([
                    'tag_name' => $tagName,
                    'tag_id' => $tagId,
                    'chat_channel_id' => $channelId
                ]);
            } else {
                return false;
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
    
    /**
     * Function to link tags with chat.
     *
     * @param array $requestParams
     * @throws Exception
     */
    public static function linkTags($requestParams)
    {
        try {
            $tagId = $requestParams['tagId'];
            $tags = Tag::find($tagId);
            if (!empty($tags)) {
                return self::linkChatWithTags($tags, $requestParams);
            } else {
                return false;
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
    
    /**
     * Function to add tags by admin.
     *
     * @throws \Exception
     */
    public static function addAdminTags($requestParams)
    {
        try {
            $userDetails = Auth::user();
            
            $addedTag = DB::transaction(function () use ($userDetails, $requestParams) {
                $addedTag = self::addTags($requestParams, $userDetails);
                $duplicateAgentTags = Tag::where('name', $requestParams['name'])->where('is_admin_tag', 0)->where('organization_id', $requestParams['organizationId'])->get();
                $duplicateAgentTags->map(function ($tags) use ($addedTag) {
                    ChatTags::where('tag_id', $tags->id)->update(['tag_id'=> $addedTag->id]);
                    self::deleteTags($tags->id);
                });
                return $addedTag;
            });
            return $addedTag;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }
    
    /**
     * Function to add tags.
     *
     * @throws \Exception
     */
    private static function addTags($requestParams, $userDetails)
    {
        try {
            $loggedInUserRole = $userDetails->role_id ?? 0;
            $organizationId = $requestParams['organizationId'];
            $isAdmin = in_array($loggedInUserRole, config('constants.ADMIN_ROLE_IDS'));
            
            $isTagAdded = Tag::create([
                'name' => trim($requestParams['name']) ?? '',
                'user_id' => $userDetails->id ?? 0,
                'organization_id' => $organizationId,
                'is_admin_tag' => $isAdmin
            ]);
            return $isTagAdded;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
    
    /**
     * Function to add agent tags.
     *
     * @param array $requestParams
     * @throws \Exception
     */
    public static function addAgentTags($requestParams)
    {
        try {
            
            $userId = $requestParams['userId'] ?? 0;
            $addedTag = DB::transaction(function () use ($userId, $requestParams) {
                $userDetails = User::find($userId);
                $isTagAdded = self::addTags($requestParams, $userDetails);
                self::linkChatWithTags($isTagAdded, $requestParams);
                return $isTagAdded;
            });
            return $addedTag;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }
    
    /**
     * Function to get agent tags.
     *
     * @param string $userToken
     * @throws \Exception
     */
    public static function getAgentTags($userId, $chatId)
    {
        try {
            $userDetails = User::find($userId);
            $userId = $userDetails->id ?? null;
            $organizationId = $userDetails->organization_id;
            $query= Tag::select('tags.name', DB::raw('IF(chat_tags.id IS NULL, false, true) as selected'), 'tags.id', DB::raw("IF(tags.user_id = $userId,1,0) as can_delete"))
            ->leftJoin('chat_tags', function ($query) use ($chatId) {
                $query->on('chat_tags.tag_id', '=', 'tags.id')
                       ->where('chat_tags.chat_channel_id', $chatId);
            });
            self::getTags($query, $userId, $organizationId);
            $tags =  $query->distinct('tags.id')->get();
            return $tags;
        } catch (\Exception $exception) {
            echo $exception->getMessage();
            die;
            throw $exception;
        }
    }
    
    /**
     * Function to get agent all chat tags.
     *
     * @param string $userToken
     * @throws \Exception
     */
    public static function getAgentChatTags($userIds)
    {
        try {
            $query= ChatChannel::select('chat_tags.tag_name','chat_tags.tag_id')
                    ->whereIn('agent_id',$userIds)
            ->Join('chat_tags', function ($query) {
                $query->on('chat_tags.chat_channel_id', '=', 'chat_channels.id');
            });
            $tags =  $query->distinct('chat_tags.tag_id')->get();
            return $tags;
        } catch (\Exception $exception) {
            echo $exception->getMessage();
            die;
            throw $exception;
        }
    }
    
    /**
     * Function to get Tags.
     *
     * @param QueryBuilder $query
     * @param User $userDetails
     * @throws \Exception
     */
    private static function getTags(&$query, $userId, $organizationId)
    {
        try {
            $query->join('users', 'users.id', '=', 'tags.user_id')
            ->where(function ($nestedQuery) use ($organizationId) {
                $nestedQuery->where('is_admin_tag', '=', 1)
                ->where('tags.organization_id', '=', $organizationId);
            })
            ->orWhere(function ($nestedQuery) use ($userId, $organizationId) {
                $nestedQuery->where('tags.user_id', '=', $userId)
                ->where('tags.organization_id', '=', $organizationId);
            });
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
    
    /**
     * Function to get admin tags.
     *
     * @throws \Exception
     */
    public static function getAdminTags($organizationId, $loggedInUserId)
    {
        try {
            $query = Tag::select('tags.name', 'tags.id', DB::raw("IF(tags.user_id = $loggedInUserId,1,0) as can_delete"));
            self::getTags($query, $loggedInUserId, $organizationId);
            $tags =  $query->get();
            return $tags;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
    
    /**
     * Function to get tag attribute.
     *
     * @return encryptedTagId.
     */
    public function getTagIdAttribute()
    {
        return encrypt($this->id);
    }
    
    /**
     * Function to check whether particular chat and tag is linked.
     *
     * @param integer $tagId
     * @param integer $channelId
     * @throws \Exception
     */
    private static function checkChatTagLink($tagId, $channelId)
    {
        try {
            return ChatTags::where('tag_id', $tagId)->where('chat_channel_id', $channelId)->first();
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
    
    /**
     * Function to unlink chat tags.
     *
     * @param integer $tagId
     * @throws \Exception
     */
    public static function unlinkTagChats($tagId, $chatId = 0)
    {
        try {
            if ($chatId != 0) {
                 return ChatTags::where('tag_id', $tagId)->where('chat_channel_id', $chatId)->delete();
            }
            return ChatTags::where('tag_id', $tagId)->delete();
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
}
