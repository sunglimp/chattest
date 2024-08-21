<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\BaseController;
use App\Http\Requests\APIRequest\Tag\AddTagRequest;
use App\Models\Tag;
use App\Http\Resources\TagCollection;
use App\Http\Requests\APIRequest\Tag\LinkTagRequest;
use App\Models\ChatTags;
use App\Models\Permission;
use App\User;
use App\Models\ChatChannel;
use Auth;
use App\Http\Resources\ChatTagCollection;
use Illuminate\Http\Request;

class TagController extends BaseController
{

    /**
     * Function to get tags.
     *
     * @return \Illuminate\Http\JsonResponse
     */


    /**
     * @api {get} /tags/agents/{userId}/chat/{chatId}  Get agent chat tags
     * @apiVersion 1.0.0
     * @apiName GetTags
     * @apiGroup Tags
     *
     * @apiSuccess {Object[]} data Response data payload
     * @apiSuccess {Integer} data.id Tag Id
     * @apiSuccess {Integer} data.canDelete You can delete or not
     * @apiSuccess {String} data.tag Tag Name
     *
     * @apiHeader {String} Authorization API token
     * @apiHeader {String} Content-Type Content type of the payload
     *
     * @apiHeaderExample {json} Content-Type:
     *     {
     *         "Content-Type" : "application/json"
     *     }
     * @apiHeaderExample {json} Authorization:
     *     {
     *         "Authorization" : "Bearer  l39I1Pyerw0DhDUTviio"
     *     }
     *
     * @apiSuccessExample Tags Available:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": [
     *               {
     *                   "tag": "g",
     *                   "selected": 0,
     *                   "id": 20,
     *                   "canDelete": 0

     *               },
     *               {
     *                   "tag": "ABC",
     *                   "selected": 0,
     *                   "id": 20,
     *                   "canDelete": 0

     *               },
     *
     *           ]
     *
     *       }
     *
     * @apiError ChatNotFound chatId not found
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "TagNotFound"
     *     }
     */


    public function index($agentId, $chatId)
    {

        try {
            $tags = Tag::getAgentTags($agentId, $chatId);
            return new TagCollection($tags);
        } catch (\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }

    /**
     * Function to add tags.
     *
     * @return \Illuminate\Http\JsonResponse
     */



      /**
     * @api {post} /tags/add Add New Tag
     * @apiVersion 1.0.0
     * @apiName Save Tags
     * @apiGroup Tags
     *
     * @apiParam {Integer} userId User id
     * @apiParam {String} name Tag Name.
     * @apiParam {Integer} channelId chat channel Id
     * @apiParam {Integer} organizationId OrgId
     *
     * @apiHeader {String} Authorization API token
     * @apiHeader {String} Content-Type Content type of the payload
     *
     * @apiHeaderExample {json} Content-Type:
     *     {
     *         "Content-Type" : "application/json"
     *     }
     * @apiHeaderExample {json} Authorization:
     *     {
     *         "Authorization" : "Bearer  l39I1Pyerw0DhDUTviio"
     *     }
     * @apiSuccessExample Tag Added:
     *     HTTP/1.1 200 OK
     *     {
     *          "message":"Tags added successfully",
     *          "status":true,
     *          "data":{
     *             "tagId":123,
     *          }
     *     }
     *
     * @apiError Tag Name already Taken
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "Name Already Taken"
     *     }
     *
     */




    public function store(AddTagRequest $request)
    {
        try {
            $requestParams = $request->validated();
            $userId = Auth::user()->id;
            $user = User::find($userId);
            $isAccessAllowed = $this->checkTagPermissions($user);
            if ($isAccessAllowed === true) {
                $isAdded = Tag::addAgentTags($requestParams);
                if ($isAdded instanceof Tag) {
                    $tagId = array(
                        'tagId' => $isAdded->id
                    );
                    return $this->successResponse(__('message.tag_add_success'), $tagId);
                } else {
                    return $this->failResponse(__('message.tag_add_fail'));
                }
            } else {
                return $this->failResponse(__('message.access_not_allowed'));
            }
        } catch (\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }

    /**
     *
     * @return \Illuminate\Http\JsonResponse
     */


     /**
     * @api {post} /tags/link Link Tag
     * @apiVersion 1.0.0
     * @apiName Link Tags
     * @apiGroup Tags
     *
     * @apiParam {Integer} tagId Tag Id
     * @apiParam {Integer} channelId Chat Channel Id.
     *
     * @apiHeader {String} Authorization API token
     * @apiHeader {String} Content-Type Content type of the payload
     *
     * @apiHeaderExample {json} Content-Type:
     *     {
     *         "Content-Type" : "application/json"
     *     }
     * @apiHeaderExample {json} Authorization:
     *     {
     *         "Authorization" : "Bearer  l39I1Pyerw0DhDUTviio"
     *     }
     * @apiSuccessExample Agent Available:
     *     HTTP/1.1 200 OK
     *     {
     *          "message":"Tag linked with chat successfully",
     *          "status":true,
     *          "data":[]
     *     }
     * @apiError GroupNotFound Group Id associated with the Access Token Not found
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "message": "This tag is already linked with particular chat",
     *       "status":false,
     *       "data":[]
     *     }
     *
     */

    public function link(LinkTagRequest $request)
    {
        try {
            $requestParams = $request->validated();
            $chatChannelId = $requestParams['channelId'];
            $chatchannel = ChatChannel::find($chatChannelId);
            $isAccessAllowed = $this->checkTagPermissions($chatchannel->agent);
            if ($isAccessAllowed === true) {
                $isLinked = Tag::linkTags($requestParams);
                if ($isLinked instanceof ChatTags) {
                    return $this->successResponse(__('message.tag_link_success'));
                } elseif ($isLinked == false) {
                    return $this->failResponse(__('message.tag_link_exist'));
                } else {
                    return $this->failResponse(__('message.tag_link_fail'));
                }
            } else {
                return $this->failResponse(__('message.access_not_allowed'));
            }
        } catch (\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }


    /**
     * Function to delete tags.
     *
     * @param integer $tagId
     * @return \Illuminate\Http\JsonResponse
     */

    /**
     * @api {delete} /tags/{tagId}  Delete Tags
     * @apiVersion 1.0.0
     * @apiName Delete Tags
     * @apiGroup Tags
     *
     * @apiSuccess {Boolean} status Status of the request.
     * @apiSuccess {Object[]} data Response data payload
     *
     * @apiHeader {String} AAuthorization API token
     * @apiHeader {String} Content-Type Content type of the payload
     *
     * @apiHeaderExample {json} Content-Type:
     *     {
     *         "Content-Type" : "application/json"
     *     }
     * @apiHeaderExample {json} Authorization:
     *     {
     *         "Authorization" : "Bearer  l39I1Pyerw0DhDUTviio"
     *     }
     *
     * @apiSuccessExample Tags Available:
     *     HTTP/1.1 200 OK
     *     {
     *
     *         "message" : "Tag deleted successfully",
     *         "status"  :  "true"
     *          "data": []
     *
     *       }
     *
     * @apiError TagDeletionFailed Tag deletion failed
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "Tag deletion failed"
     *     }
     */


    public function delete($tagId)
    {
        try {
            $isDeleted = Tag::deleteTags($tagId);
            Tag::unlinkTagChats($tagId);
            if ($isDeleted == true) {
                return $this->successResponse(__('message.tag_delete_success'));
            } else {
                return $this->failResponse(__('message.tag_delete_fail'));
            }
        } catch (\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }


    /**
     * Function to unlink tags.
     *
     * @param integer $tagId
     * @param integer $chatId
     */

    /**
     * @api {delete} /tags/{tagId}/unlink/chat/{chatId}  Unlink Tags
     * @apiVersion 1.0.0
     * @apiName Unlink Tags
     * @apiGroup Tags
     *
     * @apiSuccess {Boolean} status Status of the request.
     * @apiSuccess {Object[]} data Response data payload
     *
     * @apiHeader {String} Authorization API token
     * @apiHeader {String} Content-Type Content type of the payload
     *
     * @apiHeaderExample {json} Content-Type:
     *     {
     *         "Content-Type" : "application/json"
     *     }
     * @apiHeaderExample {json} Authorization:
     *     {
     *         "Authorization" : "Bearer  l39I1Pyerw0DhDUTviio"
     *     }
     *
     * @apiSuccessExample Tags Available:
     *     HTTP/1.1 200 OK
     *     {
     *          {
     *
     *              "message" : "Tag unlinked successfully",
     *              "status"  :  "true"
     *               "data": []
     *          }
     *
     *       }
     *
     * @apiError TagUnlinkFailed Tag unlinked fail
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "Tag unlinked fail"
     *     }
     */


    public function unlink($tagId, $chatId)
    {
        try {
            $chatchannel = ChatChannel::find($chatId);
            $isAccessAllowed = $this->checkTagPermissions($chatchannel->agent);
            if ($isAccessAllowed === true) {
                $isUnlinked = Tag::unlinkTagChats($tagId, $chatId);
                if ($isUnlinked == true) {
                    return $this->successResponse(__('message.tag_unlink_success'));
                } else {
                    return $this->failResponse(__('message.tag_unlink_fail'));
                }
            } else {
                return $this->failResponse(__('message.access_not_allowed'));
            }
        } catch (\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }

    /**
     * Function to check email send permission allowed.
     *
     * @param ChatChannel $chatChannel
     * @throws \Exception
     */
    private function checkTagPermissions($user)
    {
        try {
            $permissionId = config('constants.PERMISSION.CHAT-TAGS');
            $permission = Permission::find($permissionId);
            return $user->can('check', $permission);
        } catch(\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Function to get tags.
     *
     * @return \Illuminate\Http\JsonResponse
     */


    /**
     * @api {get} /tags/get_chat_tags  Get Tags
     * @apiVersion 1.0.0
     * @apiName GetChatTags
     * @apiGroup Tags
     *
     * @apiSuccess {Object[]} data Response data payload
     * @apiSuccess {Integer} data.tag_id Tag Id
     * @apiSuccess {String} data.tag_name Tag Name
     *
     * @apiHeader {String} Authorization API token
     * @apiHeader {String} Content-Type Content type of the payload
     *
     * @apiHeaderExample {json} Content-Type:
     *     {
     *         "Content-Type" : "application/json"
     *     }
     * @apiHeaderExample {json} Authorization:
     *     {
     *         "Authorization" : "Bearer  l39I1Pyerw0DhDUTviio"
     *     }
     *
     * @apiSuccessExample Tags Available:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": [
     *               {
     *                   "tag_name": "g",
     *                   "tag_id": 20,

     *               },
     *               {
     *                   "tag_name": "ABC",
     *                   "tag_id": 20,

     *               },
     *
     *           ]
     *
     *       }
     *
     * @apiError ChatNotFound chatId not found
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "TagNotFound"
     *     }
     */


    public function chatTags(Request $request)
    {
        try {
             if(isset($request->reportee) && !empty($request->reportee))
        {
            if ($request->reportee=='team')
            {
                if(Auth()->user()->role_id==2)
                {
                   $userIds= User::where('organization_id',Auth::user()->organization_id)
                        ->where('id','!=',Auth::user()->id)
                        ->pluck('id');

                }
                else{
                    $userIds=get_direct_reportees(Auth::user()->id,true);
                    array_push($userIds,Auth::user()->id);
                }

            }
            else
            {
                $userIds= [$request->reportee];
            }

        }
        else
        {
            if(Auth()->user()->role_id==2)
            {
                $userIds=  User::where('organization_id',Auth::user()->organization_id)
                    ->where('id','!=',Auth::user()->id)
                    ->pluck('id');

            }
            else
            {

                $userIds=get_direct_reportees(Auth::user()->id,true);
                array_push($userIds,Auth::user()->id);
            }

        }
            $tags = Tag::getAgentChatTags($userIds);
            return new ChatTagCollection($tags);
        } catch (\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }

}
