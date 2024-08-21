<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ChatChannel;
use Auth;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\PermissionController;
use App\Models\OfflineRequesterDetail;
use Yajra\DataTables\DataTables;
use App\Facades\SurboAPIFacade;
use App\Libraries\SurboAPI;
use App\Models\PermissionSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;


class ChatController extends BaseController
{

    protected $titles = [
        'chat' => 'Chat',
        'archive' => 'Archive',
        'supervise' => 'Supervise & Tip off',
        'missed' => 'Missed Chats'
    ];

    const STATUS = [
      1 => 'Contact Customer',
      2 => 'Customer Contacted',
      3 => 'Rejected'
    ];

    private $rejected = 3;
    private $picked = 2;
    private $unpicked = 1;
    private $freeTypePush = 1;
    private $paidTypePush = 2;
    private $bothTypePush = 3;
    private $isFreePush = 1;

    public function __construct()
    {
        $this->middleware('can:not-admins', ['except' => ['archive','offlineQueries','getOfflineQueries', 'missedChats', 'cannedResponse']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($route = 'chat')
    {
        return $this->getAngularView($route);
    }

    public function archive()
    {
        return $this->index('archive');
    }
    public function missedChats()
    {
        return $this->index('missed');
    }
    public function ticket()
    {
        return $this->index('ticket');
    }
    public function cannedResponse(){
        return  $this->index('canned');
    }
    public function supervise()
    {
        return $this->index('supervise');
    }


    public function status()
    {

        return $this->index('status');
    }

    public function leadStatus()
    {

        return $this->index('lead-status');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        broadcast(new \App\Events\MessageArrived(
            $request->input('message'),
            $request->input('type')
        ))->toOthers();
        return response()->json(['status' => 'ok']);
    }


    /**
     * Function for offline query view
     *
     * @return type
     */
    public function offlineQueries()
    {
        $action_column[] = [ 'data' => 'action', 'name' =>'action', 'class'  => '', 'searchable' => false, 'orderable' => false];
        $columns = [
            [ 'data' =>  'group_id', 'name' => 'group.name', 'class' => '', 'searchable' => true, 'trim' => false, 'orderable' => false],
            [ 'data' => 'source_type', 'name' => 'source_type', 'class'  => '', 'searchable' => true, 'trim' => true],
            [ 'data' => 'mobile', 'name' => 'client_info->identifier', 'class'  => '', 'searchable' => true, 'trim' => true],
            [ 'data' => 'client_query', 'name' => 'client_query', 'class'  => '', 'searchable' => false, 'trim' => false],
            [ 'data' => 'status', 'name' => 'status', 'class'  => '', 'searchable' => false, 'trim' => false],
            [ 'data' => 'created_at', 'name' => 'created_at', 'class'  => '', 'searchable' => false, 'trim' => false]
        ];
        if (!Gate::allows('admin')) {
            $columns = array_merge($columns, $action_column);
        }
        return view('chat.offline-queries', ['table_columns' => json_encode($columns),'status'=>self::STATUS]);
    }

    /**
     * Function for get offline queries
     *
     * @param Request $request
     * @return Object data table object
     */
    public function getOfflineQueries(Request $request)
    {
        $startDate = Carbon::parse($request->input('start_date'))->timestamp;
        $endDate   = Carbon::parse($request->input('end_date') . '23:59:59')->timestamp;
        $status = $request->input('status') ?? '';
        $organizationId = Auth::user()->organization_id;
        $loggedInUserId = Auth::id();
        $identifierMaskPermission = checkIndentifierMaskPermission($loggedInUserId);
        $timeZone = auth()->user()->timezone;
        $offlineSettings = PermissionSetting::where('organization_id', $organizationId)
                ->where('permission_id', config('constants.PERMISSION.OFFLINE-FORM'))
                ->select('settings->offline_query_type as offline_query_type')->first();
        $offlineQueriesType = json_decode($offlineSettings->offline_query_type) ?? config('constants.OFFLINE_QUERIES_TYPE.ORGANIZATION');

        $offlineQuries = (new OfflineRequesterDetail)->getOfflineQuery($organizationId, $offlineQueriesType, $startDate, $endDate, $status);
        return Datatables::of($offlineQuries)
            ->addColumn('action', function ($data) {
                if (!Gate::allows('admin')) {
                    return view('chat.datatables.action', [
                         'id' => $data['id'],
                         'source_type' => $data['source_type'],
                         'status' => $data['status'],
                    ]);
                }
            })
            ->editColumn('group_id', function (OfflineRequesterDetail  $data) {
                return $data->group->name ?? '';
            })
            ->editColumn('mobile', function (OfflineRequesterDetail  $data) use ($identifierMaskPermission) {
                if($data['mobile'] != 'null') {
                     return ($identifierMaskPermission ? mask($data['mobile']) : $data['mobile']);
                } else {
                     return '';
                }
            })
            ->editColumn('created_at', function (OfflineRequesterDetail  $data) use ($timeZone) {
                return  Carbon::createFromTimestamp($data->created_at->timestamp, $timeZone)->format('Y-m-d H:i');
            })
            ->addColumn('client_query', function ($data) {
                return view('chat.datatables.client-query', [
                    'client_query' => $data->offlineQuery->client_query ?? '',
                    'data' => $data,
                    'id' => $data['id']
                ]);
            })
            ->addColumn('status', function (OfflineRequesterDetail $data) {
                return self::STATUS[$data->status] ?? '';
            })
        ->rawColumns(['client_query', 'action'])
        ->make(true);
    }

    /**
     *
     * @param Request $request
     * @return type
     */
    public function sendWaPush(Request $request)
    {
        $requestId = $request->request_id;
        $isFreePush = $request->is_free_push ?? 0;
        $loggedInUserId = Auth::id();
        $offlineRequest = OfflineRequesterDetail::find($requestId);
        if($offlineRequest->status == $this->picked){
          return response()->json([
                'status'  => config('constants.STATUS_FAIL'),
                'message' => default_trans(Session::get('userOrganizationId').'/offline_queries.validation_messages.wa_already_pushed', __('default/offline_queries.validation_messages.wa_already_pushed')),
            ], config('constants.STATUS_FAIL'));
        }
        if($offlineRequest->status == $this->rejected){
          return response()->json([
                'status'  => config('constants.STATUS_FAIL'),
                'message' => default_trans(Session::get('userOrganizationId').'/offline_queries.validation_messages.wa_already_rejected', __('default/offline_queries.validation_messages.wa_already_rejected')),
            ], config('constants.STATUS_FAIL'));
        }
        $whatsappData = PermissionSetting::where('organization_id', $offlineRequest->organization_id)->where('permission_id', config('constants.PERMISSION.OFFLINE-FORM'))->select('settings->whatsapp as whatsapp_data')->first();
        $jsonDecodeWhatsappData = json_decode($whatsappData->whatsapp_data, true);
        if ($jsonDecodeWhatsappData != null && count($jsonDecodeWhatsappData) > 0) {
            $jsonDecodeClientData = json_decode($offlineRequest->client_info, true);
            if($isFreePush == $this->isFreePush){
             $whatsappPyaload =  self::freeWhatsappPayload($request, $jsonDecodeClientData, $jsonDecodeWhatsappData , $request->template_id);
            }
            else{
              $whatsappPyaload = self::paidWhatsappPayload($request, $jsonDecodeClientData, $jsonDecodeWhatsappData);
            }
            try {
                SurboAPIFacade::request(SurboAPI::POST, $whatsappPyaload['url'], $whatsappPyaload['body'], $whatsappPyaload['headers'])->getResponse();
            } catch (\Exception $e) {
                \Log::error($e->getMessage());
            }
            (new OfflineRequesterDetail)->changeStatus($requestId, $loggedInUserId, $this->picked);
            return response()->json([
                'status'  => config('constants.STATUS_SUCCESS'),
                'message' => default_trans(Session::get('userOrganizationId').'/offline_queries.success_messages.sucessfully_pushed', __('default/offline_queries.success_messages.sucessfully_pushed')),
            ], config('constants.STATUS_SUCCESS'));
        }
        return response()->json([
            'status'  => config('constants.STATUS_FAIL'),
            'message' => default_trans(Session::get('userOrganizationId').'/offline_queries.fail_messages.wa_push_failed', __('default/offline_queries.fail_messages.wa_push_failed')),
        ], config('constants.STATUS_FAIL'));
    }

    /**
     *
     * @param Request $request
     * @return type
     */
    public function rejectQuery(Request $request)
    {
        $requestId = $request->request_id;
        $loggedInUserId = Auth::id();
        $offlineRequest = OfflineRequesterDetail::find($requestId);
        if($offlineRequest->status == $this->picked){
          return response()->json([
                'status'  => config('constants.STATUS_FAIL'),
                'message' => default_trans(Session::get('userOrganizationId').'/offline_queries.validation_messages.wa_already_pushed', __('default/offline_queries.validation_messages.wa_already_pushed')),
            ], config('constants.STATUS_FAIL'));
        }
        if($offlineRequest->status == $this->rejected){
          return response()->json([
                'status'  => config('constants.STATUS_FAIL'),
                'message' => default_trans(Session::get('userOrganizationId').'/offline_queries.validation_messages.wa_already_rejected', __('default/offline_queries.validation_messages.wa_already_rejected')),
            ], config('constants.STATUS_FAIL'));
        }
        (new OfflineRequesterDetail)->changeStatus($requestId, $loggedInUserId, $this->rejected);
        return response()->json([
            'status'  => config('constants.STATUS_SUCCESS'),
            'message' => default_trans(Session::get('userOrganizationId').'/offline_queries.success_messages.sucessfully_rejected', __('default/offline_queries.success_messages.sucessfully_rejected')),
        ], config('constants.STATUS_SUCCESS'));
    }

    /**
     *
     * @param Request $request
     * @return type
     */
    public function getOfflineWhatsappTemplates(Request $request)
    {

        $requestId = $request->request_id;
        $offlineRequest = OfflineRequesterDetail::find($requestId);
        $whatsappData = PermissionSetting::where('organization_id', $offlineRequest->organization_id)->where('permission_id', config('constants.PERMISSION.OFFLINE-FORM'))->select('settings->whatsapp as whatsapp_data')->first();
        $jsonDecodeWhatsappData = json_decode($whatsappData->whatsapp_data, true);
        $templates = [];
        $isFreePush = 0;
        $isFreePushTimeOver = 0;
        if ($jsonDecodeWhatsappData != null && count($jsonDecodeWhatsappData) > 0) {
          if($jsonDecodeWhatsappData['sessionPush'] == $this->freeTypePush  || ($jsonDecodeWhatsappData['sessionPush'] == $this->bothTypePush  && $offlineRequest->created_at->timestamp > Carbon::now()->subDay()->timestamp)){
           $templates = [$jsonDecodeWhatsappData['freeTemplateId']];
           $isFreePush = $this->isFreePush;
          }
          else{
            $templates = explode(',', $jsonDecodeWhatsappData['templateId']);
          }
          if($jsonDecodeWhatsappData['sessionPush'] == $this->freeTypePush  && $offlineRequest->created_at->timestamp < Carbon::now()->subDay()->timestamp){
          $isFreePushTimeOver = 1;
          }

        }
        return response()->json(['templates' => $templates,'is_free_push' => $isFreePush,'is_free_push_time_over' => $isFreePushTimeOver,
            'status'  => config('constants.STATUS_SUCCESS'),
            'message' => default_trans(Session::get('userOrganizationId').'/offline_queries.success_messages.sucessfully_rejected', __('default/offline_queries.success_messages.sucessfully_rejected')),
        ], config('constants.STATUS_SUCCESS'));
     }


     private static function paidWhatsappPayload($request, $jsonDecodeClientData, $jsonDecodeWhatsappData){
       $body = [
                "bot_id" => $jsonDecodeWhatsappData['botId'],
                "text" => "",
                "mobileNumber" => $jsonDecodeClientData['identifier'],
                "service" => "surbo",
                "template_info" =>  $request->template_id
            ];
            $headers = [
                'Authorization' => 'Token ' . $jsonDecodeWhatsappData['token'],
            ];

            $url = rtrim($jsonDecodeWhatsappData['api']);


        return ['body' => $body , 'headers' => $headers , 'url' => $url];


     }


     private static function freeWhatsappPayload($request, $jsonDecodeClientData, $jsonDecodeWhatsappData, $templateId)
    {

        $body    = [
            "messages" => [[
            "type"          => 'text',
            "text"          => ["body"=>$request->template_id],
            "mobile"        => $jsonDecodeClientData['identifier'],
                ]
            ]
        ];
        $headers = [
            'Authorization' => 'Token ' . $jsonDecodeWhatsappData['token'],
        ];

        $url = rtrim($jsonDecodeWhatsappData['freeApi'] . '/' . $jsonDecodeWhatsappData['botId']);


        return ['body' => $body, 'headers' => $headers, 'url' => $url];
    }

}
