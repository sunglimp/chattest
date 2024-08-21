<?php

/* Channel for outsiders (e.g. Surbo) to start with Live Chat */

namespace App\Http\Controllers\Api\V1;

    use App\Events\InformAgentPrivateChannel;
    use App\Http\Controllers\BaseController;
    use App\Models\ChatChannel;
    use App\Models\ChatMessagesHistory;
    use App\Models\Client;
    use App\Models\Group;
    use App\Models\OrganizationRolePermission;
    use App\Models\PermissionSetting;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use \App\User;


class ChannelController extends BaseController
{
    /**
     * @api {post} /channels Request For Channel
     * @apiVersion 1.0.0
     * @apiName Get Channels
     * @apiGroup Surbo Api
     *
     * @apiParamExample {json} Request-Example:
     * {
     *       "group_id": 223,
     *       "source_type": "whatsapp"
     *       "transcript": [
     *       {
     *       "text": "Please help me regarding a query",
     *       "recipient": "BOT"
     *       },
     *       {
     *       "text": "Okay, Yes tell me",
     *       "recipient": "VISITOR"
     *       },
     *       {
     *       "text": "How can I start PHP coding",
     *       "recipient": "BOT"
     *      },
     *      {
     *       "text": "okay, sending you to live chat. They will suggest you better",
     *       "recipient": "VISITOR"
     *      },
     *       {
     *       "text": "Ya, sure. No issue",
     *       "recipient": "BOT"
     *       },
     *       {
     *     "text": "sending",
     *      "recipient": "VISITOR"
     *       }
     *       ],
     *       "client": {
     *       "name": "MM Khanna",
     *       "mobile": "919873908694",
     *       "email": "dummy.kumar@vfirst.com",
     *       "browser": "Chrome 10.0",
     *       "city": "Mzn",
     *       "identifier": "Rr Malhotra"
     *      }
     *       }
     *
     * @apiHeader {String} Authorization Live chat Integeration key
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
     *          "message":"",
     *          "status":true,
     *          "data":{
     *             "channel_id": 2936,
                   "channel_name": "visitor-ea41330b-ad45-458f-92d5-7833a26c95b5",
                   "agent_id": "17"
     *          }
     *     }
     *
     * @apiSuccessExample Agent Not Available:
     *     HTTP/1.1 200 OK
     *     {
     *       "message":"No Agent Online",
     *       "status":false,
     *       "data":{
     *                "show_offline_form": true,
                      "offline_requester_detail_id": 89
     *              }
     *     }
     * @apiSuccessExample Agent Banned:
     *     HTTP/1.1 200 OK
     *     {
     *       "message":"You are banned for this organization",
     *       "status":false,
     *       "data":[]
     *     }
     * @apiError GroupNotFound Group Id associated with the Access Token Not found
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "GroupNotFound"
     *     }
     *
     */
    public function store(Request $request)
    {
       // return "test";
        $data           = $request->json()->all();

        $sourceType     = $data['source_type']??'web';
        $identifier     = $data['client']['identifier'] ?? 'Guest';
        $endpoint       = isset($data['endpoint']) ?$data['endpoint']:null;
        $token          = isset($data['token']) ? $data['token']:null ;
        //TODO If user send identifier as Guest. We need to handle this case.
        $organizationId = Group::getOrganizationIdByGroup($data['group_id']);


        if ($data['client']['identifier'] != '') {
            $banKeyExist = Redis::get("ban_user_" . $organizationId . "_" . strtolower($identifier));
            if ($banKeyExist) {
                return $this->failResponse(__('message.organization_banned'), [], config('constants.STATUS_BAN_USER'));
            }
        }
        $messageData = PermissionSetting::where('organization_id', $organizationId)->where('permission_id', config('constants.PERMISSION.OFFLINE-FORM'))->select('settings->message as message','settings->qc as show_offline_form')->first();
        $onlineUsers = Redis::smembers('group_' . $data['group_id']);
        if (empty($onlineUsers)) {

            // need to enter in offline requester table
            $clientRequesterId = '';
            $clientRequeterData = [];
            $clientRequeterData['group_id'] = $data['group_id'];
            $clientRequeterData['source_type'] = $data['source_type'];
            $clientRequeterData['organization_id'] = $organizationId;
            $clientRequeterData['client_info'] = json_encode( $data['client']);
            $clientRequeterData['transcript'] = json_encode( $data['transcript']);
            $clientrequester = \App\Models\OfflineRequesterDetail::create($clientRequeterData);
            if($clientRequeterData){
                $clientRequesterId = $clientrequester->id;
            }
//            $offlineData['show_offline_form'] = OrganizationRolePermission::where('organization_id', $organizationId)
//                    ->where('role_id', config('constants.user.role.admin'))
//                    ->where('permission_id', config('constants.PERMISSION.OFFLINE-FORM'))
//                    ->exists();
            $offlineData['show_offline_form'] = (($messageData->show_offline_form != null) && ($messageData->show_offline_form == 'true'))
                                ? true
                                : false;
            $offlineData['offline_requester_detail_id'] = $clientRequesterId;
            /**
             * @todo vatika is hardcoded need to be dynamic or removed
             */
            $message    = (($messageData['show_offline_form'])
                            && ($messageData->message != 'null'))
                                ? json_decode($messageData->message,'JSON_NUMERIC_CHECK')
                                : __('message.no_agent_online', ['organization' => 'Vatika']);

            return $this->failResponse(
                $message,
                $offlineData,
                config('constants.STATUS_NO_AGENT_ONLINE')
            );
        }

        //@TODO Client is repeated or not, apply logic here
        $clientData                    = [];
        $clientData['identifier']       = $identifier ?? '';
        $clientData['organization_id']  = $organizationId;
        $clientData['raw_info']        = json_encode([$sourceType => $data['client']]);
        //
        $clientRawInfo =  Client::where('identifier',$identifier)->where('organization_id',$organizationId)->first();
        if($clientRawInfo){
           $clientData['raw_info'] = json_encode(array_merge(json_decode($clientRawInfo->raw_info, true),json_decode($clientData['raw_info'] , true)));
        }
        $clientInfo = json_decode($clientData['raw_info'], true);
        if ($data['client']['identifier'] == '') {
            $client = Client::create($clientData);
        } else {
            $clientData['updated_at'] = Carbon::now()->timestamp; // dont remove this.
            $client                   = Client::updateOrCreate([
                    "identifier" => $identifier,
                    "organization_id" => $organizationId
                ], $clientData);
        }
        $ChannelData                  = [];
        $ChannelData['end_point']     = $endpoint;
        $ChannelData['token']         = $token;
        $ChannelData['source_type'] = $sourceType;
        $ChannelData['group_id']  = $data['group_id'];
        $ChannelData['client_id'] = $client->id;
        $channel                  = ChatChannel::create($ChannelData);
        //Preparing bot's messages
        $chatHistory              = [];

        // save source type to redis
        $expiredAt = config('config.source_type_expire');
        Redis::set("source_type".$channel->id, $sourceType,'EX',$expiredAt);

        foreach ($data['transcript'] as $transcriptMsg) {
            $message = $transcriptMsg;
            if(!empty($transcriptMsg['url'])) {
                $url = $transcriptMsg['url'];
                $extension = substr($url, strrpos($url, ".")+1);
                $file_name = substr($url, strrpos($url, "/")+1);
                $message =  array('text' => null, 'file_name'=> $file_name, 'extension' => $extension, 'path' => $url, "botChat"=> true);
            }

            // this code is for location messages in transcript
            if(!empty($transcriptMsg['location'])) {
                $message =  array('text' => null, "location"=> $transcriptMsg['location'],"botChat"=> true);
            }

            $recipient     = $transcriptMsg['recipient'];
            unset($transcriptMsg['recipient']);
            $chatHistory[] = [
                'organization_id' => $organizationId,
                'chat_channel_id' => $channel->id,
                'root_channel_id' => $channel->id,
                'identifier'      => $client->identifier,
                'client_id'       => $client->id,
                'user_id'         => $channel->agent_id,
                'message'         => json_encode($message),
                'recipient'       => $recipient,
                'message_type'    => 'BOT',
                'source_type'     => $sourceType,
                'created_at'      => Carbon::now()->timestamp,
                'updated_at'      => Carbon::now()->timestamp
            ];
        }
        if ($channel->agent_id) {

            // Get agent name and role
            $agentName = '';
            $role = '';
            $agentDetail = User::getAgentDetail($channel->agent_id);
            if ($agentDetail) {
                $agentName = $agentDetail->agent_name;
                $role = $agentDetail->role;
            }

            $maskPermission = checkIndentifierMaskPermission($channel->agent_id);
            if ($maskPermission) {
                if(isset($clientInfo['identifier']) && $clientInfo['identifier']!='') {
                    $clientInfo['identifier'] =  mask($clientInfo['identifier']);
                }
                $clientInfo[$sourceType]['identifier'] = mask($clientInfo[$sourceType]['identifier']);
            }

            /***********************Identifier Modification**************************/
            if ($sourceType=='whatsapp' && isset($identifier)) {
                $client_display_label = checkOrganizationChatLabel($channel->agent_id);
                $identifier = client_display_name($client_display_label, $maskPermission, $identifier, $data['client']['name'] ?? null);
                if ($maskPermission && $client_display_label!=config('constants.CUSTOMER_CHAT_LABEL.WHATSAPP.NUMBER') && isset($clientInfo[$sourceType]['name'])) {
                    $clientInfo[$sourceType]['name'] = mask($clientInfo[$sourceType]['name']);
                    $clientInfo['name'] =  isset($clientInfo['name']) ? mask($clientInfo['name']) : null;
                }
            }
            /***********************Identifier Modification**************************/

            broadcast(new InformAgentPrivateChannel([
                        'event'               => config('broadcasting.events.new_chat'),
                        'agent_id'            => $channel->agent_id,
                        'channel_agent_id'    => $channel->agent_id,
                        'group_id'            => $channel->group_id,
                        'id'                  => $channel->id,
                        'channel_name'        => $channel->channel_name,
                        'client_id'           => $channel->client_id,
                        'source_type'         => $channel->source_type,
                        'agent_name'          => $agentName,
                        'role'                => $role,
                        //'client_display_name' => isset($identifier) ? ($maskPermission ? mask($identifier) : $identifier) : 'Guest',
                        'client_display_name' => $identifier ?? 'Guest',
                        'client_raw_info'     => $clientInfo,//$data['client'],
                        'unread_count'        => count($data['transcript']),
                        'has_history'         => $client->wasRecentlyCreated ? 0 : 1,
                        'recent_message'      => $transcriptMsg,
                        'channel_type'       => config('config.NOTIFICATION_EVENT_KEYS.new_chat'),
                    ]))->toOthers();
        }
        send_chat_notification($channel->agent_id, config('constants.CHAT_NOTIFICATION_EVENTS.NEW_CHAT'));
        ChatMessagesHistory::insert($chatHistory);

        return $this->successResponse('', [
                    'channel_id'   => $channel->id,
                    'channel_name' => $channel->channel_name,
                    'agent_id'     => $channel->agent_id
        ]);
    }

    /**
     * @api {post} /feedback Feedback Api
     * @apiVersion 1.0.0
     * @apiName FeedBack Api
     * @apiGroup Surbo Api
     *
     * @apiParam {String} channel_name To identify the agent using channel name
     * @apiParam {Integer} feedback Feedback given by user to agent.

     *
     * @apiHeader {String} Authorization Live chat Integeration key
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
     * @apiSuccessExample FeedBack Added:
     *     HTTP/1.1 200 OK
     *     {
     *          "message":"Feedback Successfully Saved",
     *          "status":true,
     *          "data":[]
     *     }
     *
     * @apiError Channel Id  Not found
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "ChannelNotFound"
     *     }
     *
     */
    public function feedback(Request $request)
    {
        try {
            $channel           = ChatChannel::where('channel_name', $request->input('channel_name'))->first();
            $channel->feedback = $request->get('feedback');
            $channel->save();

            return $this->successResponse('Feedback Successfully Saved.', []);
        } catch (Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }

    /**
     * @api {post} /chat/close Chat Close Intimation
     * @apiVersion 1.0.0
     * @apiName CloseChat
     * @apiGroup Surbo Api
     *
     * @apiParam {String} channel_name Channel name to be closed.
     * @apiParam {Boolean} is_session_timeout Session  Timeout true for Session Timeout, False for user left.
     *
     * @apiHeader {String} Authorization Live chat Integeration key
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
     * @apiSuccessExample Channel Closed:
     *     HTTP/1.1 200 OK
     *     {
     *          "message":"Chat terminated successfully",
     *          "status":true,
     *          "data":[]
     *     }
     *
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "message":"Chat closing failed",
     *          "status":false,
     *          "data":[]
     *     }
     *
     *
     */
    public function closeByVisitor(Request $request)
    {
        $toBeClosed = ChatChannel::where('channel_name', $request->channel_name)->latest();
        $isSessionTimeout = isset($request->is_session_timeout)?(($request->is_session_timeout === true || $request->is_session_timeout == false )? $request->is_session_timeout : true) :true;
        if ($toBeClosed->exists()) {
            try {
                $toBeClosed->first()->close(ChatChannel::CHANNEL_STATUS_TERMINATED_BY_VISITOR, $isSessionTimeout);
                return $this->successResponse(__('message.chat_close_success'));
            } catch (Exception $e) {
                return $this->failResponse(__('message.chat_close_fail'));
            }
        } else {
            return $this->failResponse(__('message.invalid_chat_channel'));
        }
    }
}
