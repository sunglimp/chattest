<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\ChatTransferExternal;
use App\Events\ChatTransferInternal;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\APIRequest\ChatTransferRequest;
use App\Http\Resources\Channel as ChannelResource;
use App\Models\ChatChannel;
use App\Models\ChatMessage;
use App\Models\ChatMessagesHistory;
use App\Models\Client;
use App\Models\InternalCommentChannel;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use function GuzzleHttp\json_decode;
use App\Repositories\ExpireChatRepository;

class ChatController extends BaseController
{
    /**
     * Function to pick chat.
     *
     * @param integer $agentId
     * @param integer $chatId
     */


      /**
     * @api {post} /agents/{agentId}/chat/{channelId}/pick Pick Chat
     * @apiVersion 1.0.0
     * @apiName Pick Chat
     * @apiGroup Agent
     *
     * @apiParam {Integer} agentId Agent id
     * @apiParam {Integer} channelId Channel id
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
     * @apiSuccessExample Success:
     *     HTTP/1.1 200 OK
     *    {
     *        "message": "Chat picked up successfully",
     *        "status": true,
     *        "data": []
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

    public function pick($agentId, $chatId)
    {
        try {
            if (!empty($agentId) && !empty($chatId)) {
                $channel = ChatChannel::find($chatId);
                if ($channel->agent_id == $agentId) {
                    //If channel owner select chat then updating read_at
                    ChatMessage::where('chat_channel_id', $chatId)->whereNull('read_at')->update(['read_at' => Carbon::now()->timestamp, 'updated_at' => Carbon::now()->timestamp]);
                    $root_channel_id = $channel->root_channel_id ?? $channel->id;
                    ChatMessagesHistory::where('root_channel_id', $root_channel_id)->whereNull('read_at')->update(['read_at' => Carbon::now()->timestamp, 'updated_at' => Carbon::now()->timestamp]);

                }
                $isChatPicked = $channel->status == config('constants.CHAT_STATUS.UNPICKED') ? ChatChannel::pickChat($agentId, $chatId) : true;

                if ($isChatPicked == true) {
                    return $this->successResponse(__('message.chat_pick_success'));
                } else {
                    return $this->failResponse(__('message.chat_pick_fail'));
                }
            } else {
                return $this->failResponse(__('message.chat_pick_params'));
            }
        } catch (\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }



      /**
     * @api {post} /agents/{agentId}/chat/{channelId}/close Close Chat
     * @apiVersion 1.0.0
     * @apiName Close Chat
     * @apiGroup Agent
     *
     * @apiParam {integer} agentId Agent id
     * @apiParam {integer} channelId Channel id of chat
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
     * @apiSuccessExample Response-Success:
     *     HTTP/1.1 200 OK
     *     {
     *          "message":"Chat terminated successfully",
     *          "status":true,
     *          "data":[]
     *     }
     *
     * @apiSuccessExample Response-Failed:
     *     HTTP/1.1 200 OK
     *     {
     *       "message":"Chat closing failed",
     *       "status":false,
     *       "data":[]
     *     }
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 200
     *     {
     *       "message":"Please check whether agent id and chat id are passed",
     *       "status":false,
     *       "data":[]
     *     }
     *
     */

    /**
     * Function to close chat.
     *
     * @param integer $agentId
     * @param integer $chatId
     * @return \Illuminate\Http\JsonResponse
     */
    public function close($agentId, $chatId)
    {
        try {
            if (!empty($agentId) && !empty($chatId)) {
                if (ChatChannel::find($chatId)->agent_id != $agentId) {
                    //Means supervisor type of user want to close
                    return $this->successResponse(__('message.chat_close_success'));
                }
                $isChatClosed = ChatChannel::closeChat($agentId, $chatId);
                if ($isChatClosed == true) {
                    return $this->successResponse(__('message.chat_close_success'));
                } else {
                    return $this->failResponse(__('message.chat_close_fail'));
                }
            } else {
                return $this->failResponse(__('message.chat_close_params'));
            }
        } catch (\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }

     /**
     * @api {post} /chats/transfer/channels/{channelId}/agents/{agentId} Internal Chat Transfer
     * @apiVersion 1.0.0
     * @apiName Internal Chat Transfer
     * @apiGroup Chat
     *
     * @apiParam {string} comment Comment for the chat transfer
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
     * @apiSuccessExample Internal Transfer:
     *     HTTP/1.1 200 OK
     *     {
      *     "message":"Chat is transferred successfully",
      *     "status":true,
      *     "data":[]
      *    }
     *
     */

    /**
     * Function to transfer internal chats.
     *
     * @param integer $channelId
     * @param integer $agentId
     * @param ChatTransferRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function internalTransfer($channelId, $agentId, ChatTransferRequest $request)
    {
        $transferData = $request->validated();
        $isAgentOnline = User::checkUserOnline($agentId);
        if(!$isAgentOnline){
          return $this->failResponse(__('message.chat_transfer_agent_offline'));
        }
        $user = User::find($agentId);
            if (!$user->checkPermissionBySlug('chat')) {
              return $this->failResponse(__('message.agent_no_chat_permission'));
            }
        event(new ChatTransferInternal($channelId, $agentId, $transferData));
        (new ExpireChatRepository)->destroyChatKey($channelId);
        return $this->successResponse(__('message.chat_transfer_success'));
    }

    /**
     * @api {post} /chats/transfer/channels/{channelId}/groups/{groupId} External Chat Transfer
     * @apiVersion 1.0.0
     * @apiName External Chat Transfer
     * @apiGroup Chat
     *
     * @apiParam {string} comment Comment for the chat transfer
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
     * @apiSuccessExample External Transfer:
     *     HTTP/1.1 200 OK
     *     {
      *     "message":"Chat is transferred successfully",
      *     "status":true,
      *     "data":[]
      *    }
     *
     */



    /**
     * Function to transfer external chats.
     *
     * @param integer $channelId
     * @param integer $groupId
     * @param ChatTransferRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function externalTransfer($channelId, $groupId, ChatTransferRequest $request)
    {
        $transferData = $request->validated();
        event(new ChatTransferExternal($channelId, $groupId, $transferData));
        (new ExpireChatRepository)->destroyChatKey($channelId);
        return $this->successResponse(__('message.chat_transfer_success'));
    }

    /**
     * Function to delete internal comments mapping with chat.
     *
     * @param integer $chatId
     * @param integer $agentId
     */
    public function deleteInternalComments($chatId, $agentId)
    {
        try {
            $isDeleted = InternalCommentChannel::deleteInternalComments($chatId, $agentId);
            if ($isDeleted == true) {
                return $this->successResponse(__('message.internal_chat_delete_success'));
            } else {
                return $this->successResponse(__('message.internal_chat_delete_fail'));
            }
        } catch (\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }

    /**
     *
     * @param unknown $agentId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInternalCommentChannels($agentId)
    {
        try {
            $channels = ChatChannel::getChannels($agentId, true) ?? collect([]);
            return ChannelResource::collection($channels)->additional(['status' => true]);
        } catch (\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }

    /**
     * Function for language interpretation.
     *
     * @api {get} /chats/language Request for user language interpretation
     * @apiVersion 1.0.0
     * @apiName User Language Interpretation
     * @apiGroup ArchiveChat
     *
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
     * @apiSuccessExample Groups Available:
     *     HTTP/1.1 200 OK
     *    {
	"message": "",
	"status": true,
	"data": {
		"language": "hi",
		"interpretation": {
			"chat": {
				"ui_elements_messages": {
					"active": "",
					"awaiting": "",
					"chats": "chatsss",
					"online": "",
					"offline": "",
					"tag_delete_confirm": ""
				},
				"success_messages": {
					"tag_added": ""
				},
				"fail_messages": {
					"tag_Add_fail": "",
					"unexpected_error": "",
					"error_occured": ""
				},
				"validation_messages": {
					"tag_Add_valid": "",
					"file_format_not_allowed": "",
					"file_size_exceeded": ""
				}
			},
			"archive": {
				"ui_elements_messages": {
					"text": "text hindi",
					"tags": "",
					"comment": "",
					"submit": "",
					"days": ""
				},
				"validation_messages": [],
				"success_messages": [],
				"fail_messages": []
			}
		}
	}
}
     */


    public function languageInterpretation()
    {
        try {
            $language = Auth::user()->language ?? config('config.default_language');
            $organization = Auth::user()->organization;
            $organization_id = $organization->id ?? 0;
            $chat_interpretation = default_trans($organization_id.'/chat', __('default/chat'));
            $interpretation = [
                'chat' => $chat_interpretation,
                'archive' => default_trans($organization_id.'/archive', __('default/archive')),
                'banned_users' => default_trans($organization_id.'/banned_users', __('default/banned_users')),
                'classified' => default_trans($organization_id.'/classified', __('default/classified')),
                'lead_enquire' => default_trans($organization_id.'/lead_enquire', __('default/lead_enquire')),
                'ticket_enquire' => default_trans($organization_id.'/ticket_enquire', __('default/ticket_enquire')),
                'supervise_tipoff' => default_trans($organization_id.'/supervise_tipoff', __('default/supervise_tipoff')),
                'missed_chat' => default_trans($organization_id.'/missed_chat', __('default/missed_chat')),
                'canned_response' => default_trans($organization_id.'/canned_response', __('default/canned_response')),
            ];

            $response = [
                'language' => $language,
                'interpretation' => $interpretation
            ];
            return $this->successResponse('', $response);
        } catch (\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }

     /**
     * @api {get} /chats/queue_count/{agentId} Request for chat queue count
     * @apiVersion 1.0.0
     * @apiName Chat Queue Count
     * @apiGroup ArchiveChat
     *
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
     * @apiSuccessExample Chat Count:
     *     HTTP/1.1 200 OK
     *    {
           "message": "",
            "status": true,
            "data": {
                     "count": 4
                    }
           }
     */

    public function getQueueCount($agentId)
    {
        try {
            $agentsQueueInfo = ChatChannel::getQueueCount(null, $agentId);
            $queueCount['count'] = $agentsQueueInfo[$agentId] ?? 0;
            return $this->successResponse('', $queueCount);
        } catch (\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }

    public function getClientIdentifier($clientId) {
        try {
            $client = Client::where('id',$clientId)->select('identifier', 'raw_info')->get();
            if ($client) {
                $raw_info = json_decode($client[0]->raw_info, true);
                $name = isset($raw_info['whatsapp']) &&  isset($raw_info['whatsapp']['name']) ? json_decode($client[0]->raw_info, true)['whatsapp']['name'] : null;
                $client[0]->name = $name;
                unset($client[0]->raw_info);
            }
            return $this->successResponse(__('success'),$client);
        } catch (\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }

    /**
     * @api {get} /chats/clients/{id} Get client details
     * @apiVersion 1.0.0
     * @apiName Get client details
     * @apiGroup Archive
     *
     * @apiParam {Integer} id Client id
     * @apiParam {Integer} channel_id Channel id
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
     * @apiSuccessExample Success:
     *     HTTP/1.1 200 OK
     *    {
     *       "message": "success",
     *       "status": true,
     *       "data": {
     *           "whatsapp": {
     *               "city": "Mzn",
     *               "name": "SS Shri",
     *               "email": "dummy.kumar@vfirst.com",
     *               "mobile": "919873908694",
     *               "browser": "Chrome 10.0",
     *               "identifier": "90901111599"
     *           }
     *       }
     *   }
     *
     *
     */
    public function getClientInfo($clientId) {
        try {
            $aClient = [];
            $channelId = request()->input('channel_id');
            $clientDisplaySetting = 1;
            $client = ChatMessagesHistory::select('chat_messages_history.source_type', 'clients.raw_info')
                                ->join('clients', 'clients.id', 'chat_messages_history.client_id')
                                ->where('chat_channel_id', $channelId)
                                ->where('clients.id', $clientId)->first();
            if ($client) {
                $source = $client['source_type'];
                $aClient = json_decode($client['raw_info'], true);
            }

            if(Auth::user()->checkPermissionBySlug('customer_information')) {
                $setting = Auth::user()->getPermissionSetting('customer_information');
                $clientDisplaySetting = isset($setting['whatsapp']) ? $setting['whatsapp']['client_display_attribute'] : config('constants.CUSTOMER_CHAT_LABEL.WHATSAPP.NUMBER');
            }

           /* $client    = Client::find($clientId)->raw_info;
            $aClient   = json_decode($client, true);*/
            if(Auth::user()->checkPermissionBySlug('identifier_masking')) {
                foreach ($aClient as $key => $val) {
                    if (is_array($val) && isset($val['identifier'])){
                        $aClient[$key]['identifier'] = mask($val['identifier']);
                        if (isset($val['name'])) {
                            $aClient[$key]['name'] = ($source=='whatsapp' && $clientDisplaySetting!= config('constants.CUSTOMER_CHAT_LABEL.WHATSAPP.NUMBER')) ? mask($val['name']) : $val['name'];
                        }
                    } else if ($key == 'identifier') {
                        $aClient[$key] = mask($aClient[$key]);
                    }

                    if ($key == 'name') {
                        $aClient[$key] = ($source=='whatsapp' && $clientDisplaySetting!= config('constants.CUSTOMER_CHAT_LABEL.WHATSAPP.NUMBER')) ? mask($aClient[$key]) : $aClient[$key];
                    }
                }
            }
            return $this->successResponse(__('success'), $aClient);
        } catch (\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }

    public function getClientDisplayName($clientId) {
        try {
            $client = $this->getClientIdentifier($clientId);
            if (!empty($client) && !empty($client->getData()->data)) {
                $clientOriginalName        = $client->getData()->data[0]->name ?? null;
                $identifier                = $client->getData()->data[0]->identifier ?? null;
                $setting                   = Auth::user()->getPermissionSetting('customer_information');
                $maskPermission            = Auth::user()->checkPermissionBySlug('identifier_masking');
                $client_display_label      = isset($setting['whatsapp']) ? $setting['whatsapp']['client_display_attribute'] : config('constants.CUSTOMER_CHAT_LABEL.WHATSAPP.NUMBER');
                $this->client_display_name = client_display_name($client_display_label, $maskPermission, $identifier, $clientOriginalName);
                $clientInfo = ['identifier'=>$this->client_display_name];
                if (!empty($setting)) {
                    $clientInfo['name'] =  $clientOriginalName;
                }
            } else {
                return $client;
            }
            return $this->successResponse(__('success'), array($clientInfo));
        } catch (\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }
}
