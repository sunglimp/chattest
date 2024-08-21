<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use App\Models\ChatChannel;
use Auth;
use App\Models\Client;
use App\Models\PermissionSetting;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;
use App\Models\BannedClient;
use Illuminate\Support\Facades\Gate;
use App\Models\ChatMessagesHistory;
use App\Http\Resources\ClientChatCollection;
use App\Http\Resources\BannedClientCollection;
use App\Http\Requests\APIRequest\BannedClientRequest;
use App\Http\Resources\BannnedClientChatCollection;

class BannedClientController extends BaseController
{
    /**
     * Get Banned Client List.
     *
     *
     /**
     * @api {get} /bannedClients/ Banned Visitor List
     * @apiVersion 1.0.0
     * @apiName Banned Visitor List
     * @apiGroup Banned
     *
     * @apiSuccess {String} start_date Start date for the unban client list.
     * @apiSuccess {String} end_date Start date for the unban client list.
     * @apiSuccess {String} organization_id Organization Id.
     * @apiSuccess {Integer} keyword
     * @apiSuccess {String} search Search word

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
	    "data": [{
		"id": 293,
		"channel_id": null,
		"client_display_name": "9090111159",
		"source_type": "whatsapp",
		"client_raw_info": {
			"whatsapp": {
				"city": "Mzn",
				"name": "SS Shri",
				"email": "dummy.kumar@vfirst.com",
				"mobile": "919873908694",
				"browser": "Chrome 10.0",
				"identifier": "9090111159"
			}
		},
		"date": "May 18, 2020"
	}],
	"links": {
		"first": "http:\/\/livechat.local\/api\/v1\/bannedClients?page=1",
		"last": "http:\/\/livechat.local\/api\/v1\/bannedClients?page=1",
		"prev": null,
		"next": null
	},
	"meta": {
		"current_page": 1,
		"from": 1,
		"last_page": 1,
		"path": "http:\/\/livechat.local\/api\/v1\/bannedClients",
		"per_page": 15,
		"to": 1,
		"total": 1
	},
	"status": true,
	"message": "Banned clients list fetched successfully"
         }
     *
     */


    public function index(BannedClientRequest $request)
    {
        try {
            $requestParams = $request->all();
            if( Gate::allows('superadmin')) {
                $organizationId = $requestParams['organization_id'];
            } else {
                $organizationId = Auth::user()->organization_id;
            }

            $bannedCLients = BannedClient::list($requestParams, $organizationId);
            if (!$bannedCLients->isEmpty()) {
                return (new BannedClientCollection($bannedCLients))
                ->additional(['status'=> true, 'message' => __('message.banned_list_success')]);
            } else {
                return $this->failResponse(__('message.banned_list_fail'));
            }
        } catch(\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

     /**
     * @api {post} /bannedClients/{channelId} Banned Visitor
     * @apiVersion 1.0.0
     * @apiName Banned Visitor
     * @apiGroup Banned
     *

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
     * @apiSuccessExample Banned Client:
     *     HTTP/1.1 200 OK
     *     {
     *          "message":"Client banned successfully.",
     *          "status":true,
     *          "data":[]
     *     }
     *
     */



    public function store($channelId)
    {
        try {
            $channel = ChatChannel::findorFail($channelId);
            $clientId = $channel->client_id;
            $agentId =  $channel->agent_id;
            $organizationId = Auth::user()->organization_id;
            $client = Client::findOrFail($clientId);
            $settingData = PermissionSetting::where('organization_id', $organizationId)->where('permission_id', config('constants.PERMISSION.BAN-USER'))->first();
            $expiresAt = 24 *60;
            if ($settingData) {
                $data = json_decode($settingData->settings);
                $expiresAt = $data->days * 60 * 60 * 24;
            }
            $now = Carbon::now(config('settings.default_timezone'))->timestamp;
            $expires = $now + $expiresAt;

            $isBanned = BannedClient::ban($expires, $agentId, $clientId, $now);

            if ($isBanned == true) {
                Redis::set("ban_user_".$organizationId."_".strtolower($client->identifier), 1, 'EX', $expiresAt);
                ChatChannel::closeChat($agentId, $channelId);
                return $this->successResponse("Client banned successfully.");
            } else {
                return $this->failResponse(__('message.client_banned_failed'));
            }
        } catch (\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }

    /**
     * Get Banned Client Message.
     *
     *
     /**
     * @api {get} /bannedClients/{clientId} Banned Visitor message
     * @apiVersion 1.0.0
     * @apiName Banned Visitor Message
     * @apiGroup Banned
     *

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
	"data": [{
		"message": {
			"text": "Please help me regarding a issue",
			"recipient": "BOT"
		},
		"recipient": "BOT",
		"message_type": "BOT",
		"source_type": "whatsapp",
		"created_at": {
			"date": "2020-04-17 09:15:42.000000",
			"timezone_type": 3,
			"timezone": "Asia\/Kolkata"
		},
		"agent_display_name": "Surbo"
	}, {
		"message": {
			"text": "Okay, Yes tell me",
			"recipient": "VISITOR"
		},
		"recipient": "VISITOR",
		"message_type": "BOT",
		"source_type": "whatsapp",
		"created_at": {
			"date": "2020-04-17 09:15:42.000000",
			"timezone_type": 3,
			"timezone": "Asia\/Kolkata"
		},
		"agent_display_name": "Surbo"
	}],
	"status": true,
	"message": "Banned client chats fetched successfully"
         }
     *
     */


    public function show($clientId)
    {
        try {
            $messages = ChatMessagesHistory::getBannedClientMessages($clientId);

            if(!$messages->isEmpty()) {
                return (new BannnedClientChatCollection($messages))
                ->additional(['status'=> true, 'message' => __('message.banned_client_detail_success')]);
            } else{
                return $this->failResponse(__('message.banned_client_detail_fail'));
            }
        } catch(\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($clientId)
    {

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * UnBan Client.
     *
     *
     /**
     * @api {delete} /bannedClients/{clientId} UnBanned Visitor
     * @apiVersion 1.0.0
     * @apiName UnBanned Visitor
     * @apiGroup Banned
     *

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
     *      "message":"Client has been unbanned successfully",
     *      "status":true,
     *      "data":[]
     * }
     *
     */

    public function destroy($clientId)
    {
        try {
            if (!empty($clientId)) {
                $isBanned = BannedClient::unBanClient($clientId);
                if ($isBanned == true) {
                    return $this->successResponse(__('message.unban_client_success'));
                } else {
                    return $this->failResponse(__('message.unban_client_fail'));
                }
            } else {
                return $this->failResponse(__('message.unban_client_fail'));
            }
        } catch (\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }
}
