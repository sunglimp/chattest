<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Models\ChatChannel;
use App\Models\ChatMessagesHistory;
use App\User;
use App\Http\Resources\ClientListCollection;
use App\Models\Client;
use App\Models\DownloadTrack;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\ChatTags;
use App\Models\Organization;
use App\Repositories\OrganizationRepository;
use App\Http\Requests\APIRequest\AllChatMessageRequest;
use App\Jobs\ChatDownloadAgentWise;
use Illuminate\Support\Facades\Log;
use App\Models\PermissionSetting;
use App\Models\OrganizationRolePermission;
use Illuminate\Support\Facades\DB;


class ArchiveChatControlller extends BaseController
{

    protected $STATUS_CODE = 200;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * @api {get} /chats/archive/{user}/client  Get Client List
     * @apiVersion 1.0.0
     * @apiName Get Client List
     * @apiGroup ArchiveChat
     *
     * @apiSuccess {String} start_date Start date for the archive chat history.
     * @apiSuccess {String} end_date Start date for the archive chat history.
     * @apiSuccess {Integer} type Search type
     * @apiSuccess {String} search Search word
     * @apiSuccess {String} startTime Start Time value for the archive will be blank.
     * @apiSuccess {String} endTime End Time value for the archive will be blank .
     * @apiSuccess {Boolean} is_ticket Is Ticket for the archive chat history will be false.
     * @apiSuccess {String} reportee Select from the dropdown .
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
     * @apiSuccessExample Client List Data:
     *     HTTP/1.1 200 OK
     *     {
     *       "data": [{
     *           "id": 240,
     *           "is_tagged": 0,
     *           "channel_id": 3112,
     *           "client_display_name": "919898989898",
     *           "source_type": "whatsapp",
     *           "date": "May 18, 2020"
     *       }],
     *       "status": true
     *      }
     *
     */

    public function clientList(Request $request, User $user = null)
    {
        date_default_timezone_set(Auth()->user()->timezone);
        if (!empty($request->input('startTime')) && !empty($request->input('endTime'))) {
            $startDate        = Carbon::parse($request->input('start_date') . ' ' . $request->input('startTime'))->timestamp;

            $endDate          = Carbon::parse($request->input('end_date') . ' ' . $request->input('endTime'))->timestamp;
        }
        else {
            $startDate = Carbon::parse($request->input('start_date'))->timestamp;
            $endDate   = Carbon::parse($request->input('end_date') . '23:59:59')->timestamp;
        }
        $type = $request->input('type');
        if ($type == 2) {
            $search = $request->input('search');
            if ($request->input('search') != null) {
                $search = explode(',', $request->input('search'));
            }
        } else {
            $search = strtolower($request->input('search'));
        }

        $organizationId = Auth()->user()->organization_id;
        $isTicket       = $request->is_ticket;
        $ticketType     = isset($request->ticket_type) ? $request->ticket_type : "BUSINESS TICKETS";

        $userIds = self::getUserIds($request, $isTicket);
        $page = $request->input('page') ?? 1;
        return (new ClientListCollection(ChatMessagesHistory::getClientList($userIds, $startDate, $endDate, $type, $search, $organizationId, $isTicket, $ticketType, $page)))->additional(['status' => true]);
    }

    /**
     * @api {get} /chats/archive/{user}/client/{client}/chat  Get ArchiveChat
     * @apiVersion 1.0.0
     * @apiName Get ArchiveChat
     * @apiGroup ArchiveChat
     *
     * @apiSuccess {String} start_date Start date for the archive chat history.
     * @apiSuccess {String} end_date Start date for the archive chat history.
     * @apiSuccess {Integer} channel_id Channel id.
     * @apiSuccess {Integer} client_id  Customer id.
     * @apiSuccess {Boolean} is_ticket Is Ticket for the archive chat history will be false.
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
     * @apiSuccessExample Chat Data:
     *     HTTP/1.1 200 OK
     *     {
	"data": [{
			"message": {
				"text": "Please help me regarding a query",
				"recipient": "BOT"
			},
			"recipient": "BOT",
			"message_type": "BOT",
			"created_at": {
				"date": "2020-05-18 13:56:41.000000",
				"timezone_type": 3,
				"timezone": "Asia\/Kolkata"
			}
		},
		{
			"message": {
				"text": "jkakka"
			},
			"recipient": "AGENT",
			"message_type": "public",
			"created_at": {
				"date": "2020-05-18 13:56:52.000000",
				"timezone_type": 3,
				"timezone": "Asia\/Kolkata"
			},
			"source_type": "whatsapp",
			"agent_display_name": "lassociate"
		}
	],
	"status": true
}
     *
     */
    public function clientChat(Request $request, User $user = null, Client $client = null)
    {
        date_default_timezone_set(Auth()->user()->timezone);
        $startDate = Carbon::parse($request->input('start_date'))->timestamp;
        $endDate   = Carbon::parse($request->input('end_date') . '23:59:59')->timestamp;
        $clientId  = $request->input('client_id');
        $channel   = ChatChannel::find($request->input('channel_id'));
        $channelId = $channel->root_channel_id ?? $channel->id;
        $userId    = $user->id;
        $clientId  = $client->id;
        $missedChat = $request->input('missed_chat') == 'true' ? true : false;
        $messages = ChatMessagesHistory::getOldArchiveMessages($clientId, $channelId, $startDate, $endDate, $missedChat);
        return (new \App\Http\Resources\ClientChatCollection($messages))->additional(['status' => true]);
    }

    /**
     * @api {get} /chats/get_reportees_dropdown  Get Reportee Dropdown Data
     * @apiVersion 1.0.0
     * @apiName Get Reportee
     * @apiGroup ArchiveChat
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
     *
     * @apiSuccessExample Reportee data:
     *     HTTP/1.1 200 OK
     *     {
     *      "17":"Self","15":"Lmanager",
     *      "16":"Lteamlead",
     *     }
     *
     */

    public function getReporteeDropdown(User $user = null)
    {
        try {
            $dropdownArray         = [];
            // get archive permission settings
            $archiveType           = config('constants.ARCHIVE_TYPE.HIERARCHICAL_ARCHIVE');
            $archivePermission     = OrganizationRolePermission::getOrganizationRolePermission(Auth::user()->organization_id, config('constants.PERMISSION.ARCHIVE_CHAT'), Auth::user()->role_id);
            $userArchivePermission = Auth::user()->user_permission[config('constants.PERMISSION.ARCHIVE_CHAT')] ?? false;
            if (count($archivePermission) > 0 && $userArchivePermission) {
                $settings = PermissionSetting::getPermissionSettingData(Auth::user()->organization_id, config('constants.PERMISSION.ARCHIVE_CHAT'));
                if ($settings) {
                    $settingArray = json_decode($settings->settings, true);
                    $archiveType  = $settingArray['archive_type'];
                }
            }

            if ($archiveType == config('constants.ARCHIVE_TYPE.COMPLETE_ARCHIVE') && Auth::user()->role_id != config('constants.user.role.admin')) {

                $users                           = User::where('organization_id', Auth::user()->organization_id)
                                ->where('role_id', '!=', config('constants.user.role.admin'))
                                ->where('id', '!=', Auth::user()->id)
                                ->get()->toArray();
                $dropdownArray[Auth::user()->id] = "Self";
                foreach ($users as $k => $v) {
                    $dropdownArray[$v['id']] = ucfirst($v['name']);
                }
            } else {
                $users = get_direct_reportees(Auth::user()->id);

                //for show and hide drop down
//            If the user does not have reportees below them then the dropdown should not be visible)
                if (!count($users['child']) || Auth::user()->role_id == config('constants.user.role.associate')) {
                    $this->STATUS_CODE = 201;
                }

                if (Auth::user()->role_id == config('constants.user.role.admin')) {
                    $users = User::where('organization_id', Auth::user()->organization_id)
                                    ->where('id', '!=', Auth::user()->id)
                                    ->get()->toArray();

//                $dropdownArray['team'] = "Team";
                    foreach ($users as $k => $v) {
                        $dropdownArray[$v['id']] = ucfirst($v['name']);
                    }
                } elseif (Auth::user()->role_id == config('constants.user.role.manager') || Auth::user()->role_id == config('constants.user.role.team_lead')) {
//                $dropdownArray['team'] = "Team";
                    $dropdownArray[Auth::user()->id] = "Self";
                    foreach ($users['child'] as $k => $v) {
                        $dropdownArray[$v['id']] = ucfirst($v['name']);
                    }
                } else {
                    $dropdownArray[Auth::user()->id] = "Self";
                }
            }
            return response()->json($dropdownArray, $this->STATUS_CODE);
        } catch (Exception $ex) {
            return response()->json($ex->getMessage(), 400);
        }
    }

    /**
     * @api {get} /chats/archive/{userId}/client/{clientId}/download-chat  Chat Download Data
     * @apiVersion 1.0.0
     * @apiName Get Download Chat
     * @apiGroup ArchiveChat
     *
     * @apiSuccess {String} start_date Start date for the chat.
     * @apiSuccess {String} end_date Start date for the chat.
     * @apiSuccess {Integer} channel_id Channel id.
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
     * @apiSuccessExample Chat data:
     *     HTTP/1.1 200 OK
     *    {
	"status": 200,
	"chats": "Source Type: whatsapp Tags: NULL\r\n\r\nChats \r\n=============================\r\n2020-05-18 07:26:41 BOT:\"Please help me regarding a query\"\r\n2020-05-18 07:26:41 919898989898:\"Okay, Yes tell me\"\r\n2020-05-18 07:26:41 BOT:\"How can I start PHP coding\"\r\n2020-05-18 07:26:41 919898989898:\"okay, sending you to live chat. They will suggest you better\"\r\n2020-05-18 07:26:41 BOT:\"Ya, sure. No issue\"\r\n2020-05-18 07:26:48 lassociate:\"jsjsjs\"\r\n2020-05-18 07:26:52 919898989898:\"jkakka\"",
	"file_name": "chats_log_3112_whatsapp_1588444200"
          }
     *
     */

    public function downloadChat(Request $request, User $user = null, Client $client = null)
    {
        $organization    = Auth::user()->organization;
        $organization_id = $organization->id;
        try {
            date_default_timezone_set(Auth()->user()->timezone);
            $startDate = Carbon::parse($request->input('start_date'))->timestamp;
            $endDate   = Carbon::parse($request->input('end_date') . '23:59:59')->timestamp;
            $clientId  = $request->input('client_id');
            $channel   = ChatChannel::find($request->input('channel_id'));
            $channelId = $channel->root_channel_id ?? $channel->id;
            $userId    = $user->id;
            $clientId  = $client->id;

            $tags            = ChatTags::getChatTags($channelId);
            $sourceType      = ChatMessagesHistory::getSourceType($channelId);
            $source          = $sourceType->source_type ?? '';
            $tagString       = $tags->tags ?? 'NULL';
            $fileName        = 'chats_log_' . $channelId . '_' . $source . '_' . $startDate;
            $sourceTypeLabel = default_trans($organization_id . '/archive.ui_elements_messages.source_type', __('default/archive.ui_elements_messages.source_type'));
            $tagsLabel       = default_trans($organization_id . '/archive.ui_elements_messages.tags', __('default/archive.ui_elements_messages.tags'));
            $chatsLabel      = default_trans($organization_id . '/archive.ui_elements_messages.chats', __('default/archive.ui_elements_messages.chats'));

            $headText = "$sourceTypeLabel: " . $source . ' ' . $tagsLabel . ': ' . $tagString . "\r\n";
            $headText .= "\r\n";
            $headText .= "$chatsLabel \r\n=============================\r\n";
            $messages = ChatMessagesHistory::getChatMessages($clientId, $channelId, $startDate, $endDate, $organization_id);
            // Masking Permission for User
            $identifierMaskPermission = Auth()->user()->checkPermissionBySlug('identifier_masking');
            if ($source=='whatsapp' && Auth::user()->checkPermissionBySlug('customer_information')) {
                $client  = DB::table('clients')->where('id',$clientId)->select('identifier', 'raw_info->>whatsapp->>name as client_name')->first();
                if ($client) {
                    $setting = Auth::user()->getPermissionSetting('customer_information');
                    $client_display_label = isset($setting['whatsapp']) ? $setting['whatsapp']['client_display_attribute'] : config('constants.CUSTOMER_CHAT_LABEL.WHATSAPP.NUMBER');
                    $client_display_name  = client_display_name($client_display_label, $identifierMaskPermission, $client->identifier, $client->client_name ?? null);
                }
            } else if ($identifierMaskPermission) {
                $client  = DB::table('clients')->where('id',$clientId)->select('identifier')->first();
            }

            if ($identifierMaskPermission || ($source=='whatsapp' && Auth::user()->checkPermissionBySlug('customer_information'))) {
                if ($client) {
                    $client = $client->identifier;
                    if ($identifierMaskPermission && !isset($client_display_name)) {
                        $maskIdentifier = mask($client);
                    } else {
                        $maskIdentifier = isset($client_display_name) ? $client_display_name : $client;
                    }
                    $messages = array_map(function($val) use($maskIdentifier,$client) {
                            return str_replace($client, $maskIdentifier, $val);
                    },$messages);
                }
            }
            // Masking Permission for User
            $chats    = implode("\r\n", $messages);
            $chats    = $headText . $chats;
            $payload  = ['status' => 200, 'chats' => $chats, 'file_name' => $fileName];
            return response()->json($payload, $this->STATUS_CODE);
        } catch (Exception $ex) {
            return response()->json(['status' => 400, 'message' => default_trans($organization_id . '/archive.fail_messages.chats', __('default/archive.fail_messages.chats'))], 400);
        }
    }

    /**
     * @api {get} /chats/archive/{userId}/download-tag-report   Download Tag Data
     * @apiVersion 1.0.0
     * @apiName Get Download Tag
     * @apiGroup ArchiveChat
     *
     * @apiSuccess {String} start_date Start date for the chat.
     * @apiSuccess {String} end_date Start date for the chat.
     * @apiSuccess {Integer} type  2 value in case of tag.
     * @apiSuccess {String} search .
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
     * @apiSuccessExample Tag data:
     *     HTTP/1.1 200 OK
     *    {
	"status": 200,
	"file_name": "chats_log_17_1577817000",
	"reports": [{
		"Identifier": "919090909090",
		"Date": "2020-02-04",
		"Time": "10:43:00",
		"Source Type": "whatsapp",
		"Tags": "ab"
	}, {
		"Identifier": "9090909090",
		"Date": "2020-01-07",
		"Time": "08:33:00",
		"Source Type": "whatsapp",
		"Tags": "ab"
	}],
	"data-flag": true
         }
     *
     */

    public function downloadTagReport(Request $request, User $user = null)
    {
        try {
            $organization   = Auth::user()->organization;
            $organizationId = $organization->id;
            date_default_timezone_set(Auth()->user()->timezone);
            $startDate      = Carbon::parse($request->input('start_date'))->timestamp;
            $endDate        = Carbon::parse($request->input('end_date') . '23:59:59')->timestamp;

            $userIds = self::getUserIds($request, false);
            $tagIds = [];
            $type   = $request->input('type'); //2- Tag Search
            if ($type == 2) {
                $search = $request->input('search');
                $tagIds = ($search != null) ? explode(',', $search) : [];
            }

            $reports = ChatMessagesHistory::getChatTagReports($userIds, $tagIds, $startDate, $endDate, $organizationId);
            if (count($reports) > 0) {
                $fileName = 'chats_log_' . $user->id . '_' . $startDate;
                $payload  = ['status' => 200, 'file_name' => $fileName, 'reports' => $reports, 'data-flag' => true];
                return response()->json($payload, $this->STATUS_CODE);
            } else {
                $payload = ['status' => 200, 'data-flag' => false];
                return response()->json($payload, $this->STATUS_CODE);
            }
        } catch (Exception $ex) {
            Log::error("API:ArchiveController::downloadTagReport==>Exception for API request " . $ex->getMessage());
            return response()->json(['status' => 400, 'message' => 'Something went wrong'], 400);
        }
    }

    /**
     * Get all chats
     *
     * @param AllChatMessageRequest $request
     * @return type
     */
    public function getAllChats(AllChatMessageRequest $request)
    {
        try {
            $startDate    = ($request->input('start_date') != '') ? Carbon::parse($request->input('start_date'))->timestamp : Carbon::parse(Carbon::now()->format('Y-m-d'))->timestamp;
            $endDate      = ($request->input('end_date') != '') ? Carbon::parse($request->input('end_date') . '23:59:59')->timestamp : Carbon::parse(Carbon::now()->format('Y-m-d') . '23:59:59')->timestamp;
            $clientId     = '';
            $agentId      = '';
            $token        = $request->bearerToken();
            $organization = new OrganizationRepository(new Organization());
            $organization = $organization->findBySurboUniqueKey(trim($token));
            $groupId      = $request->input('group_id');
            $groupName    = \App\Models\Group::where('id', $groupId)->where('organization_id', $organization->id)->value('name');
            if ($groupName == null) {
                return response()->json(['status' => 400, 'message' => 'The group is not associated with this organization'], 400);
            }
            if ($request->input('email') != '') {
                $agentDetail = User::where('email', $request->input('email'))->first();
                $agentId     = $agentDetail->id;
            }
            if ($request->input('identifier') != '') {
                $clientId     = 0;
                $clientDetail = Client::where('identifier', $request->input('identifier'))->first();
                if ($clientDetail) {
                    $clientId = $clientDetail->id;
                }
            }
            Log::info("API:ArchiveController::getAllChats==>Exception for API request ");
            Log::info($request->all());
            $messages = ChatChannel::getAllChats($clientId, $groupId, $startDate, $endDate, $agentId, $organization->timezone);
            $payload  = ['status' => 200, 'success' => true, 'group' => $groupName, 'data' => $messages];
            return response()->json($payload, $this->STATUS_CODE);
        } catch (Exception $ex) {
            Log::error("API:ArchiveController::getAllChats==>Exception for API request " . $ex->getMessage());
            return response()->json(['status' => 400, 'message' => 'Something went wrong'], 400);
        }
    }




private static function getUserIds($request, $isTicket){

      // get archive permission settings
        $organizationId = Auth::user()->organization_id;
        $archiveType           = config('constants.ARCHIVE_TYPE.HIERARCHICAL_ARCHIVE');
        $archivePermission     = OrganizationRolePermission::getOrganizationRolePermission($organizationId, config('constants.PERMISSION.ARCHIVE_CHAT'), Auth::user()->role_id);
        $userArchivePermission = Auth::user()->user_permission[config('constants.PERMISSION.ARCHIVE_CHAT')] ?? false;
        if (count($archivePermission) > 0 && $userArchivePermission) {
            $settings = PermissionSetting::getPermissionSettingData($organizationId, config('constants.PERMISSION.ARCHIVE_CHAT'));
            if ($settings) {
                $settingArray = json_decode($settings->settings, true);
                $archiveType  = $settingArray['archive_type'];
            }
        }

        if (isset($request->reportee) && !empty($request->reportee)) {
            if ($request->reportee == 'team') {
                if (Auth()->user()->role_id == config('constants.user.role.admin')) {
                    $userIds = User::where('organization_id', Auth::user()->organization_id)
                            ->where('id', '!=', Auth::user()->id)
                            ->pluck('id');
                } elseif (Auth()->user()->role_id != config('constants.user.role.admin') && $archiveType == config('constants.ARCHIVE_TYPE.COMPLETE_ARCHIVE') && $isTicket=='false') {
                        $userIds = User::where('organization_id', Auth::user()->organization_id)
                            ->where('role_id', '!=', config('constants.user.role.admin'))
                            ->pluck('id');
                } else {
                    $userIds = get_direct_reportees(Auth::user()->id, true);
                    array_push($userIds, Auth::user()->id);
                }
            } else {
                $userIds = [$request->reportee];
            }
        } else {
            if (Auth()->user()->role_id == config('constants.user.role.admin')) {
                $userIds = User::where('organization_id', Auth::user()->organization_id)
                        ->where('id', '!=', Auth::user()->id)
                        ->pluck('id');
            } elseif (Auth()->user()->role_id != config('constants.user.role.admin') && $archiveType == config('constants.ARCHIVE_TYPE.COMPLETE_ARCHIVE') && $isTicket=='false') {
                $userIds = User::where('organization_id', Auth::user()->organization_id)
                        ->where('role_id', '!=', config('constants.user.role.admin'))
                        ->pluck('id');
            } else {
                $userIds = get_direct_reportees(Auth::user()->id, true);
                array_push($userIds, Auth::user()->id);
            }
        }

        return $userIds;

    }

    /**
     * @api {get} /chats/archive/{user}/download-chat   Download Agent Wise Chat
     * @apiVersion 1.0.0
     * @apiName Get Download Agent Wise Chat
     * @apiGroup ArchiveChat
     *
     * @apiSuccess {String} start_date Start date for the chat.
     * @apiSuccess {String} end_date Start date for the chat.
     * @apiSuccess {String} reportee  Select from the dropdown .
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
     * @apiSuccessExample Agent Chat data:
     *     HTTP/1.1 200 OK
     *    {
	"status": true,
	"message":"Request is under process. You will receive the email shortly."
     *  "data":[]
     *    }
     *
     * * @apiSuccessExample Already Chat data In Process:
     *     HTTP/1.1 200 OK
     *    {
	"status": false,
	"message":"Your previous request is still under process, Please try after sometime."
     *  "data":[]
     *    }
     *
     */


public function downloadAgentWiseChat(Request $request, User $user = null)
    {
        $organization    = Auth::user()->organization;
        $organization_id = $organization->id;
        $startDate = $request->input('start_date');
        $endDate= $request->input('end_date');
        $reportee  = $request->input('reportee');
        if($reportee){
            $agent= User::find($reportee);
        }
        $key = ($reportee) ? $startDate . '-' . $endDate . '-' . $reportee : $startDate . '-' . $endDate;
        if (DownloadTrack::allowProcess($user->id, $key)) {
            //Place a job here
            $params = [
                'start_date' =>  $startDate,
                'end_date'   =>  $endDate,
                'reportee'  =>  $reportee,
                'organization_id' => $organization_id,
                'role_id' =>  Auth()->user()->role_id,
                'user_time_zone' => Auth()->user()->timezone,
                'user_email'     => $user->email,
                'user_id' => $user->id,
                'key' => $key,
                'user_name'=> $user->name,
                'agent_name' => $agent->name ?? 'Team'
                ];
            ChatDownloadAgentWise::dispatch($params)
                            ->onQueue(config('chat.queues.chat_download_agent_wise'));
            return $this->successResponse(default_trans($organization_id . '/archive.success_messages.agent_chat_download', __('default/archive.success_messages.agent_chat_download')));
        } else {
            return $this->failResponse(default_trans($organization_id . '/archive.fail_messages.agent_chat_download', __('default/archive.fail_messages.agent_chat_download')));

        }

    }
}
