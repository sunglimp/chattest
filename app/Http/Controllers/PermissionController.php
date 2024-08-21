<?php

namespace App\Http\Controllers;

use App\Factory\PermissionFactory;
use Illuminate\Http\Request;
use App\Models\Permission;
use App\Models\Organization;
use App\Models\Role;
use Illuminate\Support\Facades\Gate;
use Auth;
use App\Models\OrganizationRolePermission;
use Illuminate\Support\Facades\Lang;
use App\Models\PermissionSetting;
use App\Http\Requests\Preferences\UploadAttachmentRequest;
use App\Http\Requests\Preferences\TimerRequest;
use App\Http\Requests\Preferences\ChatFeedbackRequest;
use App\User;
use DB;
use App\Http\Requests\Preferences\AddBanUserRequest;
use App\Http\Requests\Preferences\TmsKeyRequest;
use App\Models\Ticket;
use App\Models\TicketField;
use Illuminate\Http\Response;
use App\Http\Requests\Preferences\ClassifiedChatRequest;
use App\Http\Requests\Preferences\NotificationRequest;
use App\Http\Requests\Preferences\EmailRequest;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\Preferences\OfflineFormRequest;
use App\Http\Requests\Preferences\SessionTimerRequest;
use App\Http\Requests\Preferences\AutoTransferTimerRequest;
use App\Http\Requests\Preferences\MissedChatRequest;
use App\Http\Requests\Preferences\CustomerInformationRequest;
use Illuminate\Support\Facades\Redis;
use App\Repositories\ExpireChatRepository;


class PermissionController extends BaseController
{
    public function __construct()
    {
        $this->middleware('can:all-admin');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $organizationList = [];
        if (Gate::allows('superadmin')) {
            $organizationList = Organization::where('status', '!=', Organization::STATUS_DEACTIVE)->orderBy('created_at', 'desc')->get();
            $masterPermission = Permission::all();
            $userRoles = Role::userRole(config('constants.user.role.super_admin'))->get();
            $adminOrganizationPermission = [
                'organization_list' => $organizationList,
                'master_permission' => $masterPermission,
                'user_roles' => $userRoles,
            ];
        } else {
            $organizationId = Auth::user()->organization_id;
            $adminOrganizationPermission = OrganizationRolePermission::getAdminOrganizationPermission($organizationId);
        }
        return View('permission.permission', $adminOrganizationPermission);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
//        //Geting Organization level permission from request
//        $requestAdminPermission= $request->permission[config('constants.user.role.admin')];
//
//        $data= OrganizationRolePermission::where('organization_id',$request->organization_id)
//            ->where('role_id',config('constants.user.role.admin'))
//            ->pluck('permission_id');
//        $deletePermissionArray=[];
//        $deletePermissionIdString = "";
//        foreach ($data as$v )
//        {
//            if(!array_key_exists($v,$requestAdminPermission))
//            {
//                array_push($deletePermissionArray,$v);
//                $deletePermissionIdString = $deletePermissionIdString . " '$.\"" . $v . "\" ' " . ',';
//            }
//
//
//        }
//        //remove last comma
//        $deletePermissionIdString = rtrim($deletePermissionIdString, ',');
//        //check any permission delete or not at organization/admin level
//        //  if yes then delete from user level permission
//        if(count($deletePermissionArray)) {
//            User::where('organization_id', $request->organization_id)
//                ->update(['user_permission' => DB::raw("JSON_REMOVE(user_permission, $deletePermissionIdString)")]);
//        }
//        //==================End of User Level Permission changes================

        $final_array = [];
        $isEnableTimeoutPermission = 0;
        if (Gate::allows('superadmin')) {
            $organizationId = $request->input('organization_id');
            OrganizationRolePermission::where('organization_id', $organizationId)->delete();
        } else {
            $organizationId = Auth::user()->organization_id;
            OrganizationRolePermission::where('organization_id', $organizationId)->where('role_id', '!=', config('constants.user.role.admin'))->delete();
        }
        if (Gate::allows('superadmin')) {
            foreach ($request->permission as $roleId => $option) {
                if ($roleId == config('constants.user.role.admin')) {
                    $keyArray = array_keys($option);
                }
            }
        }
        foreach ($request->permission as $roleId => $option) {
            foreach ($option as $permissionKey => $value) {
                if (($roleId==config('constants.user.role.admin')) && ($permissionKey==config('constants.PERMISSION.TIMEOUT'))) {
                    $isEnableTimeoutPermission = 1;
                }
                if (Gate::allows('superadmin') && $roleId != config('constants.user.role.admin')) {
                    if (!in_array($permissionKey, $keyArray)) {
                        continue;
                    }
                }
                $finalArray[] = ['organization_id' => $organizationId, 'role_id' => $roleId, 'permission_id' => $permissionKey, 'created_by' => Auth::user()->id, 'created_at' => \Carbon\Carbon::now()->timestamp];
            }
        }
        OrganizationRolePermission::insert($finalArray);

        if ($isEnableTimeoutPermission==1) {
            $expireTime = (new PermissionSetting)->getChatTimeoutSettings($organizationId);
            (new ExpireChatRepository)->setOrganizationChatExpireTime($organizationId, $expireTime);
        } else {
            (new ExpireChatRepository)->setOrganizationChatExpireTime($organizationId, 'false');
        }

        return response()->json([
            'status' => config('constants.STATUS_SUCCESS'),
            'message' => default_trans((Session::get('userOrganizationId').'/permission.success_messages.msg_success_updated'), __('default/permission.success_messages.msg_success_updated')),
            'organization_id'=>$organizationId,
        ], config('constants.STATUS_SUCCESS'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function organizationPermission(Request $request)
    {
        try {
            $organizationId = $request->input('organization_id');
            if (Gate::allows('superadmin')) {
                $permissions = OrganizationRolePermission::getSuperAdminOrganizationPermission($organizationId);
            } else {
                $permissions = OrganizationRolePermission::getAdminOrganizationPermission($organizationId);
            }
            $view = view('permission.permission_partial', $permissions)
                ->render();
            return response()->json([
                'status' => true,
                'html' => $view
            ]);
        } catch (\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }


    public function updateAttachmentSize(UploadAttachmentRequest $request)
    {
        $sizeEncode = json_encode(['size' => $request->get('size')]);
        $organizationId = $request->input('organization_id');
        if (Gate::allows('admin')) {
            $organizationId = Auth::user()->organization_id;
        }
        PermissionSetting::updateSetting($organizationId, $sizeEncode, config('constants.PERMISSION.SEND-ATTACHMENT'));
        return response()->json([
            'status' => config('constants.STATUS_SUCCESS'),
        ], config('constants.STATUS_SUCCESS'));
    }

    public function showSetting(Request $request)
    {
//        dd($request->toArray());
        $viewSetting= PermissionFactory::view($request);
        return $viewSetting->viewSetting();
    }

    public function updateAutoChatTransfer(AutoTransferTimerRequest $request)
    {
        $viewSetting= PermissionFactory::view($request);
        $sizeEncode = json_encode([
            'hour' => $request->get('hour'),
            'minute' => $request->get('minute'),
            'second' => $request->get('second'),
            'transfer_limit' => $request->get('transfer_limit')
        ]);
        $organizationId = $request->input('organization_id');
        if (Gate::allows('admin')) {
            $organizationId = Auth::user()->organization_id;
        }
        PermissionSetting::updateSetting($organizationId, $sizeEncode, config('constants.PERMISSION.AUTO-CHAT-TRANSFER'));

        // save organization transfer limit to redis
        Redis::set('organization_'.$organizationId.'_auto_transfer',$request->get('transfer_limit'));

        return response()->json([
            'status' => config('constants.STATUS_SUCCESS'),
        ], config('constants.STATUS_SUCCESS'));
    }



    public function updateChatFeedback(ChatFeedbackRequest $request)
    {
        $feedback = json_encode(['feedback' => $request->get('feedback')]);
        $organizationId = $request->input('organization_id');
        if (Gate::allows('admin')) {
            $organizationId = Auth::user()->organization_id;
        }
        PermissionSetting::updateSetting($organizationId, $feedback, config('constants.PERMISSION.CHAT-FEEDBACK'));
        return response()->json([
            'status' => config('constants.STATUS_SUCCESS'),
        ], config('constants.STATUS_SUCCESS'));
    }

    public function updateChatNotifier(TimerRequest $request)
    {
        $sizeEncode = json_encode([
            'hour' => $request->get('hour'),
            'minute' => $request->get('minute'),
            'second' => $request->get('second')
        ]);
        $organizationId = $request->input('organization_id');
        if (Gate::allows('admin')) {
            $organizationId = Auth::user()->organization_id;
        }
        PermissionSetting::updateSetting($organizationId, $sizeEncode, config('constants.PERMISSION.CHAT-NOTIFIER'));
        return response()->json([
            'status' => config('constants.STATUS_SUCCESS'),
        ], config('constants.STATUS_SUCCESS'));
    }


    public function updateChatTimeout(TimerRequest $request)
    {
        $sizeEncode = json_encode([
            'hour' => $request->get('hour'),
            'minute' => $request->get('minute'),
            'second' => $request->get('second')
        ]);
        $organizationId = $request->input('organization_id');
        if (Gate::allows('admin')) {
            $organizationId = Auth::user()->organization_id;
        }
        PermissionSetting::updateSetting($organizationId, $sizeEncode, config('constants.PERMISSION.TIMEOUT'));
        //redis update time
        $organizationPermission = (new OrganizationRolePermission)->getOrganizationChatTimeoutPermission($organizationId);
        if($organizationPermission) {
            $expireTime = ((int)$request->get('hour')*3600) +  ((int)$request->get('minute')*60) + ((int)$request->get('second'));
            (new ExpireChatRepository)->setOrganizationChatExpireTime($organizationId, $expireTime);
        }
        return response()->json([
            'status' => config('constants.STATUS_SUCCESS'),
        ], config('constants.STATUS_SUCCESS'));
    }

    public static function checkPermissions($organizationId, $roleId, $permissionId)
    {
        $permission = 0;
        $user = Auth::user();
        if ($user->role_id == config('constants.user.role.super_admin')) {
            return 1;
        }
        $organizationPermissions = OrganizationRolePermission::getOrganizationRolePermission(
            $organizationId,
            $permissionId,
            $roleId
        );
        if (empty($organizationPermissions)) {
            return $permission;
        }

        $userPermissionList = $user->user_permission;

        if ($userPermissionList) {
            if (in_array($permissionId, $userPermissionList)) {
                if (isset($userPermissionList[$permissionId]) && $userPermissionList[$permissionId] == true) {
                    $permission = 1;
                }
            }
        }
        return $permission;
    }

    /**
     *
     * @param OfflineFormRequest $request
     * @return type
     */
    public function updateOfflineMessage(OfflineFormRequest $request)
    {

        $whatsapp = [];
        $email = [];
        $message = $request->get('message');
        $thank_you_message = $request->get('thank_you_message');
        $qc = false;
        $offlineQueryType = $request->input('offline_query_type') ?? config('constants.OFFLINE_QUERIES_TYPE.ORGANIZATION');
        if ($request->has('qc_slider')) {
            $qc = (bool)$request->get('qc_slider') ?? false;

            if ($request->has('email_slider')) {
                $email =  [
                    'emailId' => $request->get('email_id'), 'subject' => $request->get('subject'),
                    'emailBody' => $request->get('email_body'), 'botTranscript' => (bool)$request->get('bot_transcript') ?? false,
                    'sendEmailOnQC' => (bool)$request->get('send_email_on_qc') ?? false
                ];
            }

            if ($request->has('wa_push_slider')) {
                $whatsapp = [
                    'api' => $request->get('api'), 'templateId' => $request->get('template_id'),
                    'botId' => $request->get('bot_id'), 'token' => $request->get('token'),
                    'freeApi' => $request->get('free_api'),'freeTemplateId' => $request->get('free_template_id'),
                    'sessionPush'=> $request->get('session_push')
                ];
            }
        }

        $messageEncode = json_encode([
            'email' => $email,
            'whatsapp' => $whatsapp,
            'message' => $message,
            'thank_you_message'=> $thank_you_message,
            'qc' =>  $qc,
            'offline_query_type' => $offlineQueryType
        ]);

        $organizationId = $request->input('organization_id');
        if (Gate::allows('admin')) {
            $organizationId = Auth::user()->organization_id;
            /**********************Check to only update Message and Thank u message in case of Admin***********/
            $permissionData = PermissionSetting::getPermissionSettingData($organizationId,config('constants.PERMISSION.OFFLINE-FORM'));
            $permissionData = json_decode($permissionData->settings, TRUE);
            $permissionData['message'] = $message;
            $permissionData['thank_you_message'] = $thank_you_message;
            if (isset($permissionData['whatsapp']) && !empty($permissionData['whatsapp'])) {
                $permissionData['whatsapp']['templateId'] = $request->get('template_id');
                $permissionData['whatsapp']['freeTemplateId'] =  $request->get('free_template_id');
            }
            $messageEncode = json_encode($permissionData);
            /**********************Check to only update Message and Thank u message in case of Admin***********/
        }

        PermissionSetting::updateSetting($organizationId, $messageEncode, config('constants.PERMISSION.OFFLINE-FORM'));
        return response()->json([
            'status' => config('constants.STATUS_SUCCESS'),
        ], config('constants.STATUS_SUCCESS'));
    }

    public function updateBanDay(AddBanUserRequest $request)
    {
        $daysEncode = json_encode(['days' => $request->get('ban_days')]);
        $organizationId = $request->input('organization_id');
        if (Gate::allows('admin')) {
            $organizationId = Auth::user()->organization_id;
        }
        PermissionSetting::updateSetting($organizationId, $daysEncode, config('constants.PERMISSION.BAN-USER'));
        return response()->json([
            'status' => config('constants.STATUS_SUCCESS'),
        ], config('constants.STATUS_SUCCESS'));
    }

    /**
     * Function to update tms key.
     *
     */
    public function updateTmsKey(TmsKeyRequest $request)
    {
        try {
            $tms_key = $request->get('tms_key');
            $organizationId = $request->input('organization_id');
            if (Gate::allows('admin')) {
                $organizationId = Auth::user()->organization_id;
            }
            $isTMSIntegerated = TicketField::saveFieldData($tms_key, $organizationId);

            if ($isTMSIntegerated === true) {
                $organization = Organization::find($organizationId);
                $organization->tms_unique_key = $tms_key;
                $organization->save();
                return response()->json([
                    'status' => config('constants.STATUS_SUCCESS'),
                ], config('constants.STATUS_SUCCESS'));
            } elseif ($isTMSIntegerated == Response::HTTP_UNAUTHORIZED) {
                return $this->failResponse(default_trans(Session::get('userOrganizationId').'/permission.fail_messages.api_key_failed', __('default/permission.fail_messages.api_key_failed')));
            } else {
                return $this->failResponse(default_trans(Session::get('userOrganizationId').'/permission.fail_messages.update_tms_key_failed', __('default/permission.fail_messages.update_tms_key_failed')));
            }
        } catch (\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }


    public function updateclassifiedToken(ClassifiedChatRequest $request)
    {
        try {
            info('update classified token', [$request->get('classified_token'), $request->all()]);
            $mlModelToken = json_encode(['ml_model_token' => $request->get('classified_token')]);


            $organizationId = $request->input('organization_id', null);
            if (empty($organizationId) && Gate::allows('admin')) {
                $organizationId = Auth::user()->organization_id;
            }

            info('update permission setting', [config('constants.PERMISSION.CLASSIFIED-CHAT')]);
            PermissionSetting::updateSetting(
                $organizationId,
                $mlModelToken,
                config('constants.PERMISSION.CLASSIFIED-CHAT')
            );

            info('permission setting updated');

            return response()->json([
                'status' => config('constants.STATUS_SUCCESS'),
            ], config('constants.STATUS_SUCCESS'));
        } catch (\Exception $ex) {
            \Log::error($ex->getMessage());
            return $this->exceptionRespnse($exception);
        }
    }

    /**
     * Function to add notification setting.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addNotificationSettting(NotificationRequest $request)
    {
        try {
            $requestParams = $request->all();
            $notificationData = [
                'notificationEvents' => $requestParams['notificationEvents']
            ];
            PermissionSetting::updateSetting(
                $requestParams['organizationId'],
                json_encode($notificationData),
                config('constants.PERMISSION.AUDIO-NOTIFICATION')
            );
            return $this->successResponse(default_trans(Session::get('userOrganizationId').'/permission.success_messages.notification_setting_updated', __('default/permission.success_messages.notification_setting_updated')));
        } catch (\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }

    public function updateEmailCredentials(EmailRequest $request)
    {
        // Server side check to ensure only superadmin is able to update credentials
        if (Gate::allows('not-superadmin')) {
            return response()->json([
                'status' => false,
                'error'  => 'Unauthorized'
            ], config('constants.STATUS_FAIL'));
        }

        try {
            $emailEncode = json_encode([
            'username' => $request->input('username'),
            'password' => $request->input('password'),
            'host' => $request->input('host'),
            'port' => $request->input('port'),
            'encryption'=> $request->input('encryption'),
            'provider_type'=>  $request->input('provider_type'),
            'from_email' => $request->input('from_email')
        ]);
            $organizationId = $request->input('organization_id');
            if (Gate::allows('admin')) {
                $organizationId = Auth::user()->organization_id;
            }

            PermissionSetting::updateSetting($organizationId, $emailEncode, config('constants.PERMISSION.EMAIL'));
            return response()->json([
            'status' => config('constants.STATUS_SUCCESS'),
        ], config('constants.STATUS_SUCCESS'));
        } catch (\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }

    public function updateTagSettings(Request $request)
    {
        try {
            $tagEnabledRoles = $request->roles;
            $userRoles = Role::userRole(config('constants.user.role.super_admin'))->get(['id'])->toArray();
            $roleIds = array_column($userRoles, 'id');
            $organizationId = $request->organization_id;
            $permissionId = $request->permission_id;
            foreach ($roleIds as $roleId) {
                if (!isset($tagEnabledRoles[$roleId])) {
                    $existingValue = false;
                    if (Auth::user()->role_id == $roleId) {
                        $tagSettings = PermissionSetting::getTagSettings($organizationId, $permissionId);
                        $existingValue = $tagSettings['tag_creation'][$roleId];
                    }
                    $tagEnabledRoles[$roleId] = $existingValue;
                } else {
                    $tagEnabledRoles[$roleId] = true; //bcs true needs to convert to boolean type
                }
            }
            $tagSettings['tag_creation'] = $tagEnabledRoles;
            $tagSettings['tag_required'] = ($request->tag_required == 'true') ? true : false;

            if (PermissionSetting::updateSetting($organizationId, json_encode($tagSettings), $permissionId)) {
                return response()->json([
                    'status'  => config('constants.STATUS_SUCCESS'),
                    'message' => default_trans((Session::get('userOrganizationId').'/permission.success_messages.msg_success_updated'), __('default/permission.success_messages.msg_success_updated')),
                    'id'      => '',
                ], config('constants.STATUS_SUCCESS'));
            }

            return response()->json([
                'status' => config('constants.STATUS_FAIL'),
                'errors' => default_trans((Session::get('userOrganizationId').'/permission.fail_messages.something_wrong'), __('default/permission.fail_messages.something_wrong')),
            ], config('constants.STATUS_FAIL'));
        } catch (\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }

    }

    /**
     * Update session timeout
     *
     * @param SessionTimerRequest $request
     * @return type
     */
    public function updateSessionTimeout(SessionTimerRequest $request)
    {
        $time = json_encode([
            'hour' => $request->get('hour'),
            'minute' => $request->get('minute'),
            'second' => $request->get('second'),
            'max_hours'=> $request->get('max_hours'),
        ]);
        $organizationId = $request->input('organization_id');
        if (Gate::allows('admin')) {
            $organizationId = Auth::user()->organization_id;
        }
        PermissionSetting::updateSetting($organizationId, $time, config('constants.PERMISSION.SESSION_TIMEOUT'));
        return response()->json([
            'status' => config('constants.STATUS_SUCCESS'),
        ], config('constants.STATUS_SUCCESS'));
    }


    /**
     * Update archive chat type
     * @return type
     */
    public function updateArchiveChat(Request $request)
    {
        $archiveValue = $request->input('archive_type') ?? 1;
        $archiveType = json_encode(['archive_type' => $archiveValue]);

        $organizationId = $request->input('organization_id');

        if (Gate::allows('admin')) {
            $organizationId = Auth::user()->organization_id;
        }

        PermissionSetting::updateSetting($organizationId, $archiveType, config('constants.PERMISSION.ARCHIVE_CHAT'));

        return response()->json([
            'status' => config('constants.STATUS_SUCCESS'),
        ], config('constants.STATUS_SUCCESS'));


      }



    public function updateChatDownload(Request $request)
    {
        $chatDownload = json_encode([
            'agent_wise_chat_download' => $request->get('chat_download'),
        ]);

        $organizationId = $request->input('organization_id');

        if (Gate::allows('admin')) {
            $organizationId = Auth::user()->organization_id;
        }

        PermissionSetting::updateSetting($organizationId, $chatDownload, config('constants.PERMISSION.CHAT-DOWNLOAD'));

        return response()->json([
            'status' => config('constants.STATUS_SUCCESS'),
        ], config('constants.STATUS_SUCCESS'));
    }

    public function updateMissedChatSettings(MissedChatRequest $request)
    {
        // Server side check to ensure only superadmin is able to update credentials
        if (Gate::allows('not-superadmin')) {
            return response()->json([
                'status' => false,
                'error'  => 'Unauthorized'
            ], config('constants.STATUS_FAIL'));
        }

        $settings = json_encode([
            'api' => $request->get('api'),
            'templateId' => $request->get('template_id'),
            'botId' => $request->get('bot_id'),
            'token' => $request->get('token'),
        ]);
        $organizationId = $request->input('organization_id');
        if (Gate::allows('admin')) {
            $organizationId = Auth::user()->organization_id;
        }
        PermissionSetting::updateSetting($organizationId, $settings, config('constants.PERMISSION.MISSED-CHAT'));
        return response()->json([
            'status' => config('constants.STATUS_SUCCESS'),
        ], config('constants.STATUS_SUCCESS'));
    }

    /**
     * Function to update Customer Information setting
     * @param
     * @return [type] [description]
     */
    public function updateCustomerInformationSettings(CustomerInformationRequest $request)
    {
        try {

            $settings = json_encode(['whatsapp'=> [
                'client_display_attribute' => $request->get('customerChatInfoLabel')
            ]]);

            $organizationId = $request->input('organization_id');
            if (Gate::allows('admin')) {
                $organizationId = Auth::user()->organization_id;
            }

            PermissionSetting::updateSetting($organizationId, $settings, config('constants.PERMISSION.CUSTOMER-INFORMATION'));

            return response()->json([
                'status' => config('constants.STATUS_SUCCESS'),
            ], config('constants.STATUS_SUCCESS'));
        } catch (Exception $e) {
            log_exception($e->getMessage());
        }
    }
}
