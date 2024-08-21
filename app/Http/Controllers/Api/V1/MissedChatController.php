<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Models\ChatChannel;
use App\Models\Client;
use App\Models\MissedChatAction;

use Carbon\Carbon;
use App\Http\Resources\MissedChatClientCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class MissedChatController extends BaseController
{

    /**
    *    @api {get} /chats/missed Get Missed Chat Clients
    *    @apiVersion 1.0.0
    *    @apiName Get Missed Chat Clients
    *    @apiGroup Missed Chat Clients
    *
    *    @apiHeader {String} Authorization API token
    *    @apiHeader {String} Content-Type Content type of the payload
    *    @apiHeaderExample {json} Content-Type:
    *       {
    *            "Content-Type" : "application/json"
    *       }
    *    @apiHeaderExample {json} Authorization:
    *        {
    *            "Authorization" : "Bearer  l39I1Pyerw0DhDUTviio"
    *        }
    *    @apiParam {String} start_date dd-mm-yyyy
    *    @apiParam {String} end_date dd-mm-yyyy
    *    @apiParam {Integer} status 0- Contact Customer, 1- Customer Contacted, 2- Chat Rejected
    *    @apiParam {integer} page

    *    @apiSuccess {integer} status 0: not contacted, 1- WA Pushed, 2- Rejected
    *    @apiSuccess {string} client_display_name  Identifier of client
    *    @apiSuccess {integer} chat_channel_id chat channel id
    *    @apiSuccessExample Success:
    *    {
    *    "data": [
    *       {
    *           "client_display_name": "9090111159",
    *           "chat_channel_id": 407,
    *           "client_id":1,
    *           "source_type": "whatsapp",
    *           "status": 1,
    *           "message": "Customer Contacted",
    *           "date": "Sep 08, 2020 16:57"
    *       },
    *       {
    *           "client_display_name": "9090111183",
    *           "chat_channel_id": 376,
    *           "client_id":40
    *           "source_type": "whatsapp",
    *           "status": 0,
    *           "message": "Contact Customer",
    *           "date": "Aug 25, 2020 15:07"
    *       },
    *
    *    ],
    *    "links": {
    *       "first": "{baseURL}/api/v1/chats/missed?page=1",
    *       "last": "{baseURL}/api/v1/chats/missed?page=1",
    *       "prev": null,
    *       "next": null
    *    },
    *    "meta": {
    *        "current_page": 1,
    *        "from": 1,
    *        "last_page": 1,
    *        "path": "{baseURL}/api/v1/chats/missed",
    *        "per_page": 15,
    *        "to": 2,
    *        "total": 2
    *    },
    *         "status": true
    *    }
    */
    public function index(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');
        $status    = $request->input('status', null);
        return (new MissedChatClientCollection(ChatChannel::getAllMissedChatClients($startDate, $endDate, $status)))->additional(['status' => true]);
    }

    /**
    *    @api {post} /chats/missed/{chatChannelId} Action on missed chat
    *    @apiVersion 1.0.0
    *    @apiName Action on missed chat
    *    @apiGroup Missed Chat Clients
    *
    *    @apiHeader {String} Authorization API token
    *    @apiHeader {String} Content-Type Content type of the payload
    *    @apiHeaderExample {json} Content-Type:
    *       {
    *            "Content-Type" : "application/json"
    *       }
    *    @apiHeaderExample {json} Authorization:
    *        {
    *            "Authorization" : "Bearer  l39I1Pyerw0DhDUTviio"
    *        }
    *    @apiParamExample {json} Request-Example:
    *    {"action":1}
    *    @apiParam {integer} chatChannelId received in Get Missed Chat Clients
    *    @apiParam {integer} action  Value 1 for WA Push, 2 for Reject
    *    @apiSuccessExample Success:
    *    {
    *        "message": "Successfully Pushed",
    *        "status": true,
    *        "data": []
    *    }
    *    {
    *        "message": "Successfully Rejected",
    *        "status": true,
    *        "data": []
    *    }
    *    {
    *        "message": "Action already done",
    *        "status": false,
    *        "data": {
    *            "status": 2
    *        }
    *    }
    */
    public function update($chatChannelId, Request $request)
    {
        $action = ($request->action == config('constants.MISSED_CHAT_ACTION.WA_PUSHED')) ? $request->action : config('constants.MISSED_CHAT_ACTION.REJECTED');
        $alreadyExists = MissedChatAction::where('chat_channel_id', $chatChannelId)->first();
        if (isset($alreadyExists->id)) {
            //Means action on this already has taken
            return $this->failResponse(default_trans(Auth::user()->organization->id . '/missed_chat.fail_messages.action_already_done', __('default/missed_chat.fail_messages.action_already_done')), ['status' => $alreadyExists->status]);
        }
        $missedChatAction = MissedChatAction::create([
            'chat_channel_id' => $chatChannelId,
            'template_id'     => '',
            'status'          => $action

        ]);
        if ($action == config('constants.MISSED_CHAT_ACTION.WA_PUSHED')) {
            return MissedChatAction::sendWAPush($chatChannelId) ? $this->successResponse(default_trans(Auth::user()->organization->id . '/missed_chat.success_messages.sucessfully_pushed', __('default/missed_chat.success_messages.sucessfully_pushed')))
                :
                $this->failResponse(default_trans(Auth::user()->organization->id . '/missed_chat.fail_messages.wa_push_failed', __('default/missed_chat.fail_messages.wa_push_failed')));
        } else {
            return $this->successResponse(default_trans(Auth::user()->organization->id . '/missed_chat.success_messages.sucessfully_rejected', __('default/missed_chat.success_messages.sucessfully_rejected')));

        }
    }
}
