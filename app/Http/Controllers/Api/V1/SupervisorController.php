<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\BaseController;
use App\Models\ChatChannel;
use App\User;
use App\Http\Resources\Channel as ChannelResource;
use App\Http\Resources\ChannelCollection;
use Auth;
use Illuminate\Support\Facades\Log;

class SupervisorController extends BaseController
{


    /**
     * @api {get} /supervisors/{userId}/channels  Get Channel
     * @apiVersion 1.0.0
     * @apiName Get Channel
     * @apiGroup Supervisor
     *
     *
     * @apiHeader {String} Authorization API token
     * @apiHeader {String} Content-Type Content type of the payload
     *
     * @apiParam {Integer} userId Id of the user with supervisors role
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
     * @apiSuccessExample Channels Available:
     *     HTTP/1.1 200 OK
     *   {
     *        "data": [
     *           {
     *               "id": 1,
     *               "channel_name": "visitor-44097a41-8aa8-4c9e-a8d6-8de73fb65844",
     *               "group_id": 1,
     *               "client_id": 1,
     *               "parent_id": null,
     *               "agent_name": "Associate One",
     *               "role": "Associate",
     *               "client_display_name": "9090111159",
     *               "source_type": null,
     *               "client_raw_info": {
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
     *               "unread_count": 1,
     *               "recent_message": {
     *                   "text": "hi SATISH"
     *               },
     *               "channel_agent_id": 5,
     *               "channel_type": "basic",
     *               "status": "1",
     *               "has_history": 1
     *           }
     *       ],
     *       "status": true
     *   }
     *
     */

    public function channel($userId)
    {
        try {
            $agentIds = get_children(Auth::user()->id);
            $channels     = empty($agentIds) ? collect([]) : ChatChannel::getChannels(null, false, $agentIds);
            return (new ChannelCollection($channels))
                ->additional(['status' => true]);
            //return ChannelResource::collection($channels)->additional(['status' => true]);
        } catch (\Exception $exception) {
            Log::error("SupervisorController::channel==>Following Exception Handled for no agents online");
            log_exception($exception);
        }
    }


    /**
     * @api {get} /supervisors/{userId}/agents  Get Agent
     * @apiVersion 1.0.0
     * @apiName Get Agent
     * @apiGroup Supervisor
     *
     *
     * @apiHeader {String} Authorization API token
     * @apiHeader {String} Content-Type Content type of the payload
     *
     * @apiParam {Integer} userId Id of the user with supervisors role
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
     *   {
     *       "message": "",
     *        "status": true,
     *       "data": [
     *           {
     *               "id": 5,
     *               "user_name": "Associate One",
     *               "role": "Associate"
     *           },
     *           {
     *               "id": 6,
     *               "user_name": "Associate Two",
     *               "role": "Associate"
     *           }
     *       ]
     *   }
     *
     */

    public function agent($userId)
    {
        $agents = User::getSupervisorUsers(Auth::user()->id);
        return $this->successResponse('', $agents);
    }
}