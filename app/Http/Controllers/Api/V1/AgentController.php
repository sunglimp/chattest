<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\BaseController;
use App\Models\PermissionSetting;
use Illuminate\Http\Request;
use App\Models\ChatChannel;
use App\Agent;
use Carbon\Carbon;
use App\Models\UserLogin;
use App\User;
use App\Events\UserOnline;
use App\Events\UserOffline;
use App\Models\OrganizationRolePermission;
use App\Models\Permission;
use App\Http\Resources\ChannelCollection;
use Response;
use DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redis;
use Auth;

class AgentController extends BaseController
{

    const CHANNEL_STATUS_UNPICKED = 1;
    const CHANNEL_STATUS_PICKED   = 2;


      /**
     * @api {get} /agents/{agentId}/channel Get Agent Channel
     * @apiVersion 1.0.0
     * @apiName Get Agent Channel
     * @apiGroup Agent
     *
     * @apiParam {Integer} agentId Agent id
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
     * @apiSuccessExample Success:
     *     {
     *       "data": [
     *           {
     *              "id": 15,
     *              "channel_name": "visitor-7057bc75-de8a-466d-807f-1f4d50e67b93",
     *              "group_id": 1,
     *              "client_id": 1,
     *              "parent_id": null,
     *              "agent_name": "Associate One",
     *              "role": "Associate",
     *              "client_display_name": "9090111159",
     *              "source_type": "whatsapp",
     *              "client_raw_info": {
     *                   "city": "Mzn",
     *                   "name": "SS Shri",
     *                   "email": "dummy.kumar@vfirst.com",
     *                   "mobile": "919873908694",
     *                   "browser": "Chrome 10.0",
     *                   "whatsapp": {
     *                       "city": "Mzn",
     *                       "name": "SS Shri",
     *                       "email": "dummy.kumar@vfirst.com",
     *                       "mobile": "919873908694",
     *                       "browser": "Chrome 10.0",
     *                       "identifier": "9090111159"
     *                   },
     *                   "identifier": "9090111159"
     *               },
     *              "unread_count": 6,
     *              "recent_message": {
     *                  "text": "hi"
     *              },
     *              "channel_agent_id": 5,
     *              "channel_type": "basic",
     *              "status": "2",
     *              "has_history": 1
     *           }
     *       ],
     *       "status": true
     *   }
     *
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "status":404
     *       "message": "Not Found"
     *     }
     */

    public function channel($agentId)
    {

        //@TODO IMPROVEMENT client display info can be injected without join (using seperate query)
        return (new ChannelCollection(ChatChannel::getChannels($agentId)))
                ->additional(['status' => true]);
    }


     /**
     * @api {put} /agents/{agentId}/online Make Agent Online
     * @apiVersion 1.0.0
     * @apiName Online Agent
     * @apiGroup Agent
     *
     * @apiParam {Integer} agentId Agent id
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
     * @apiSuccessExample Agent Online:
     *    HTTP/1.1 200 OK
     *    {
     *        "message": "Status updated successfully",
     *        "status": true,
     *        "data": {
     *            "user_id": "5",
     *            "in_time": 1589887181,
     *            "updated_at": "1589887181",
     *            "created_at": "1589887181",
     *            "id": 16
     *        }
     *    }
     *
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "status":404
     *       "error": "Not Found"
     *     }
     *
     */

    //Change Agent status to online
    public function online($agentId)
    {
        try {
            //@TODO Some event need to be triggered.
            $status = config('constants.STATUS_ONLINE');
            User::changeStatus($agentId, $status);

            $userLoginDetail = [
                'user_id'    => $agentId,
                'in_time'    => Carbon::now()->timestamp,
                'created_at' => Carbon::now()->timestamp
            ];
            $detail          = UserLogin::create($userLoginDetail);
            $user = User::find($agentId);
            if ($user->checkPermissionBySlug('chat')) {
                event(new UserOnline($agentId));
            }

            return $this->successResponse('Status updated successfully', $detail);
        } catch (\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }



     /**
     * @api {put} /agents/{agentId}/offline/{checkChatAvailable}/{ignorePickChats?} Make Agent Offline
     * @apiVersion 1.0.0
     * @apiName Offline Agent
     * @apiGroup Agent
     *
     * @apiParam {Integer} agentId Agent id
     * @apiParam {Integer} checkChatAvailable 1 0r 0
     * @apiParam {Integer} ignorePickChats 1 or 0
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
     * @apiSuccessExample Chat Active:
     *     HTTP/1.1 200 OK
     *    {
     *        "message": "You have active chats available. In case of force log out, active chats will be lost. Do you really want to proceed?",
     *        "status": false,
     *        "data": []
     *    }
     *
     * @apiSuccessExample Chat Force offline:
     *    HTTP/1.1 200 OK
     *    {
     *        "message": "Status updated successfully",
     *        "status": true,
     *        "data": []
     *    }
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "status":404
     *       "error": "Not Found"
     *     }
     *
     */
    //Change Agent status to offline
    public function offline($agentId, $checkChatAvailable, $makeChatTerminated = 0)
    {
        try {
            //@TODO Some event need to be triggered.
            if ($checkChatAvailable == 1) {
                $isChatActive = ChatChannel::checkChatAvailable($agentId);

                //if no active chats are there
                if ($isChatActive->isEmpty()) {
                    return $this->offlineChat($agentId);
                } else {
                    //if active chats are there
                    return $this->failResponse(__("message.offline_fail_active_chats"));
                }
            } elseif ($checkChatAvailable == 0) {
                //in case of force logout
                if ($makeChatTerminated) {
                    ChatChannel::makeChatTerminated($agentId);
                }
                return $this->offlineChat($agentId, $makeChatTerminated);
            } else {
                return $this->failResponse(__('message.something_went_wrong'));
            }
        } catch (\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }

    /**
     * @api {get} /agents/{agentId}/permissions Get Agent Permissions
     * @apiVersion 1.0.0
     * @apiName Get Agent Permissions
     * @apiGroup Agent
     *
     * @apiParam {Integer} agentId Agent id.
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
     * @apiSuccessExample Response-Success:
     *     HTTP/1.1 200 OK
     *     {
     *         "message": "Data send",
     *         "status": true,
     *         "data": {
     *             "roles": false,
     *             "canned-response": true,
     *             "dashboard-access": false,
     *             "group-creation": false,
     *             "supervise-tip-off": false,
     *             "chat-history": true,
     *             "chat-notifier": false,
     *             "chat-transfer": true,
     *             "auto-chat-transfer": false,
     *             "chat-tags": false,
     *             "chat-feedback": false,
     *             "send-attachments": true,
     *             "email": true,
     *             "timeout": false,
     *             "internal-comments": false,
     *             "download-report": false,
     *             "chat": true,
     *             "ban-user": true,
     *             "offline-form": false,
     *             "tms_key": false,
     *             "classified_chat": false,
     *             "audio_notification": false,
     *             "settings": {
     *                 "send-attachments": {
     *                     "size": 5
     *                 }
     *             },
     *             "lqs": false,
     *             "lms": false,
     *             "tms": false
     *         }
     *     }
     *
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "status":404
     *       "message": "Not Found"
     *     }
     */

    /**
     * @todo to put this logic in cache
     * @param Request $request
     * @return mixed
     */
    public function permissions($agentId)
    {

        $user = User::find($agentId);

        $organizationRolePermissions=[];

        $orgPermissionList =  OrganizationRolePermission::where('organization_id', $user->organization_id)
                ->where('role_id', $user->role_id)
                ->get(['permission_id'])
                ->toArray();
            $userPermissionList = Auth::User()->user_permission;
        foreach ($orgPermissionList as $value) {
            //Priotity for organization permission, in the case of true then user permission will check
            if ($userPermissionList) {
                if (in_array($value['permission_id'], $userPermissionList)) {
                    if (isset($userPermissionList[$value['permission_id']]) && $userPermissionList[$value['permission_id']] == true) {
                        $organizationRolePermissions[$value['permission_id']] = true;
                    }
                }
            }
        }
        $permissions = Permission::select('id', 'name', 'slug')->get();

        $userPermissions=[];
        foreach ($permissions as $permission) {
            $userPermissions[$permission->slug] = array_key_exists($permission->id, $organizationRolePermissions) ? true : false;
        }
        self::getSettingData($user, config('constants.PERMISSION.AUDIO-NOTIFICATION'), $userPermissions, 'notification_settings');
        self::getSettingData($user, config('constants.PERMISSION.SEND-ATTACHMENT'), $userPermissions, 'send-attachments');
        self::getSettingData($user, config('constants.PERMISSION.CHAT-TAGS'), $userPermissions, 'tag_settings');
        self::getSurboAceIntegrationData($user, config('constants.PERMISSION.TMS-KEY'), $userPermissions, 'surbo_ace_integration');
        self::getSettingData($user, config('constants.PERMISSION.ARCHIVE_CHAT'), $userPermissions, 'archive-chat');
        self::getSettingData($user, config('constants.PERMISSION.CHAT-DOWNLOAD'), $userPermissions, 'chat_download');

        return  $this->successResponse("Data send", $userPermissions);

    }

    private function offlineChat($agentId, $makeChatTerminated = 0)
    {
        $status = config('constants.STATUS_OFFLINE');
        User::changeStatus($agentId, $status);
        UserLogin::updateOfflineTime($agentId);
        event(new UserOffline($agentId, $makeChatTerminated));
        return $this->successResponse('Status updated successfully');
    }

    public function banUser($channelId)
    {
        try {
            $channel = ChatChannel::find($channelId);
            $clientId = $channel->client_id;

            $client = Client::find($clientId);
            $client->banned_by = Auth::user()->id;
            $client->banned_at = Carbon::now()->timestamp;
            $client->save();

            return $this->successResponse('Customer banned successfully');

        } catch (\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }

    /**
     * Function to get setting data depending on permission.
     *
     * @param User $user
     * @param integer $permissionId
     * @param array $userPermissions
     * @param string $key
     */
    private static function getSettingData($user, $permissionId, &$userPermissions, $key)
    {
        try {
            $permission = Permission::find($permissionId);
            if ($user->can('check', $permission)) {
                $permissionSettings = PermissionSetting::getPermissionSettingData($user->organization_id, $permissionId);
                if (!empty($permissionSettings)) {
                    $notificationSettings = $permissionSettings->settings;
                    $userPermissions['settings'][$key]  = json_decode($notificationSettings);
                }
            }
        } catch (\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }

    /**
     * Function to get setting surbo ace depending on permission.
     *
     * @param User $user
     * @param integer $permissionId
     * @param array $userPermissions
     * @param string $key
     */
    private static function getSurboAceIntegrationData($user, $permissionId, &$userPermissions, $key)
    {
        $permission = Permission::find($permissionId);
        $userPermissions["lqs"]  = false;
        $userPermissions["lms"]  = false;
        $userPermissions["tms"]  = false;
        //$surboAcePermission = ['permission'=> false,'application'=>[]];
        if ($user->can('check', $permission)) {
            $surboApplicationArray = \App\Models\TicketField::where('organization_id',$user->organization_id)->pluck('application_id')->toArray();
            if(in_array(config('constants.TICKET_APPLICATION.LQS'),$surboApplicationArray)){
               $userPermissions["lqs"]  = true;
            }
            if(in_array(config('constants.TICKET_APPLICATION.LMS'),$surboApplicationArray)){
              $userPermissions["lms"]  = true;
            }
            if(in_array(config('constants.TICKET_APPLICATION.TMS'),$surboApplicationArray)){
               $userPermissions["tms"]  = true;
            }
         //$surboAcePermission = ['permission'=> true,'application'=>$surboApplicationArray];
        }
        //$userPermissions[$key]  = json_encode($surboAcePermission);
    }

    public function deleteUserLastActivityKey($id)
    {
        \Log::info("++++++++++++deleteUserLastActivityKey+++++++++++++++++++++++");
        $key = 'last_activity_' .$id;
        Redis::del($key);
        return $this->successResponse('Sucess');
    }

     /**
     * @api {put} /agents/chat-notification-setting Change Chat Notification Status
     * @apiVersion 1.0.0
     * @apiName Change Chat Notification Status
     * @apiGroup Agent
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
     * @apiParam {Integer} chat_notification_status Sample value i.e 1 => Enable / 0 => Disable
     *
     * @apiParamExample {json} Request-Example:
     *   {
     *      "chat_notification_status":0
     *   }
     * @apiSuccessExample Success-Response:
     *  HTTP/1.1 200 OK
     *  {
     *     "message": "Success",
     *     "status": true,
     *     "data": []
     *   }
     *
     * @apiErrorExample Validation-Response:
     *   HTTP/1.1 422 OK
     *   {
     *      "message": "Given input is not valid for the entity",
     *      "status": false,
     *      "data": []
     *   }
     *
     * @apiErrorExample Unauthorized-Response:
     *   HTTP/1.1 401 OK
     *   {
     *      "message": "Unauthorized",
     *      "status": false,
     *      "data": []
     *   }
     *
     * @apiErrorExample Exception-Response:
     *   HTTP/1.1 200 OK
     *   {
     *      "message": "Something Went Wrong. Please try again",
     *      "status": false
     *   }
     */
    public function changeChatNotificationStatus(Request $request, $id='') {
        try {
            if (Gate::allows('not-admins')) {
                $data = $request->json()->all();
                if(!isset($data['chat_notification_status']) || !in_array($data['chat_notification_status'], config('constants.CHAT_NOTIFICATION_STATUS'))) {
                    return $this->failResponse(__('exceptions.invalid_input_exception'),[],422);
                }
                $request->user()->chat_notification_status = $data['chat_notification_status'];
                $request->user()->save();
                return $this->successResponse('Success');
            }
            return $this->failResponse(__('Unauthorized'),[],401);
        } catch (\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }
}
