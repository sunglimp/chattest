<?php

namespace App\Http\Controllers;

use App\Events\UserOnline;
use App\Models\Organization;
use App\Models\OrganizationRolePermission;
use App\Models\Permission;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\EditUserRequest;
use App\Models\Role;
use Illuminate\Support\Facades\Lang;
use App\Models\Group;
use Illuminate\Support\Facades\Log;
use Yajra\Datatables\Datatables;
use Auth;
use App\Models\UserGroup;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\User\UpdatePasswordRequest;
use App\Http\Requests\User\UserIdRequest;
use App\Events\UserOffline;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;
use App\Models\ChatChannel;
use App\Models\UserLogin;

class UserController extends BaseController
{
    public function __construct()
    {
        $this->middleware('can:all-admin');
    }

    public function index()
    {
        $organization = Organization::active()->orderBy('created_at', 'desc')->get();
        return view('user.list', ['organization' => $organization]);
    }

    public function getUsersByOrganization(Request $request)
    {
        if (isset($request->organization_id) && $request->has('organization_id')
            && !empty($request->organization_id)) {
            $organizations = User::with('role')->where('id', '!=', Auth::user()->id)
                ->where('organization_id', $request->organization_id)->orderBy('created_at', 'desc')->get();
        } else {
            $organizations = User::with('role')->where('status', '!=', User::IS_DEACTIVE)->where('role_id', '!=', config('constants.user.role.super_admin'))
                ->orderBy('created_at', 'desc')->get();
        }
        $timeZone = auth()->user()->timezone;
        
        $is_sneak_in = $this->canUserSneakIn();
        
        return Datatables::of($organizations)
            ->addColumn('image', 'user.datatables.image')
            ->addColumn('status', 'user.datatables.status')
            ->addColumn('action', function ($data) use ($is_sneak_in) {
                return view('user.datatables.action', [
                    'is_sneak_in' => $is_sneak_in,
                    'id' => $data['id'],
                    'is_login' => $data['is_login']
                ]);
            })
            ->addColumn('password', "user.datatables.update_password")
            ->editColumn('last_login', function (User $user) use ($timeZone) {
                return  $user->getLastLoginTzAttribute($timeZone);
            })
            ->editColumn('role_id', function (User $user) {
                return $user->role->name;
            })
            ->rawColumns(['image', 'action', 'status','password'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $organizationId = $request->organization_id;
        $timezoneList= config('timezone');

        foreach ($timezoneList as $k => $v) {
            $start= strpos($v, '(');
            $end= strpos($v, ')');
            $timezoneList[$k]= substr($v, $start+1, $end-$start-1);
        }


        $orgDetails = Organization::getOrganizationDetails($organizationId);
        
        $orgLanguages = Organization::getLanguagesByOrgId($organizationId);
        
        $roles          = Role::userRole(Auth::user()->role_id)
            ->whereHas('rolepermission', function ($q) use ($organizationId) {
                $q->where('permission_id', 1);
                $q->where('organization_id', $organizationId);
            })->get();


        $groups = Group::where('organization_id', $organizationId)
            ->where('name', '!=', 'Default')
            ->get();

        $defaultGroup= Group::where('organization_id', $organizationId)
            ->where('name', 'Default')
            ->first();

        $orgTimeZone = $orgDetails->timezone;
        $detail                  = new User;
        $detail->organization_id = $organizationId;
        $view                    = view('user.add_user_partial', [
            'timezone_list' => $timezoneList,
            'user_roles' => $roles,
            'groups' => $groups,
            'default_group'=>$defaultGroup,
            'organization_timezone' => $orgTimeZone,
            'org_languages' => $orgLanguages
        ])
            ->render();
        return response()->json([
            'status' => true,
            'html'   => $view
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUserRequest $request)
    {
        $filename = '';
        if ($request->file('logo') != '') {
            $extension = $request->file('logo')->extension();
            $filename  = Storage::putFileAs('user', $request->file('logo'), time() . "." . $extension);
        }
        $request['image'] = $filename;
        $request['user_permission']=$this->getUserDefaultPermission($request->role_id, $request->organization_id);
        $request['api_token'] = get_user_api_token();

        $addUser          = User::create($request->all());
        $createdBy        = Auth::user()->id;

        if (!empty($request->get('group'))) {
            foreach ($request->get('group') as $group_id) {
                $group [] = ['user_id' => $addUser->id, 'group_id' => $group_id, 'created_by' => $createdBy, 'created_at' => Carbon::now()->timestamp];
            }
            \App\Models\UserGroup::insert($group);
        }

        if (!$addUser) {
            return response()->json([
                'status' => config('constants.STATUS_FAIL'),
                'errors' => default_trans(Session::get('userOrganizationId').'/user_list.fail_messages.something_wrong', __('default/user_list.fail_messages.something_wrong')),
            ], config('constants.STATUS_FAIL'));
        }

        return response()->json([
            'status'  => config('constants.STATUS_SUCCESS'),
            'message' => default_trans(Session::get('userOrganizationId').'/user_list.success_messages.user_created_success', __('default/user_list.success_messages.user_created_success')),
            'id'      => $addUser->id,
        ], config('constants.STATUS_SUCCESS'));
    }


    protected function getUserDefaultPermission($role_id, $organization_id)
    {
        $data=OrganizationRolePermission::where('organization_id', $organization_id)->where('role_id', $role_id)->pluck('permission_id');
//        $permissionData= Permission::pluck('id');
        $permissionData=OrganizationRolePermission::where('organization_id', $organization_id)
            ->where('role_id', 2)
            ->pluck('permission_id');


        $userPermissionData=[];
        foreach ($permissionData as $v) {
            if ($data->search($v, true)) {
                $userPermissionData[$v]=true;
            } else {
                $userPermissionData[$v]=false;
            }
        }

        return json_encode($userPermissionData);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $detail = User::with('role', 'report', 'parent', 'groups')->find($id);
        $userGroups = User::formatGroup($detail->groups);
        $view   = view('user.detail_partial', ['user_detail' => $detail,
            'user_groups' => $userGroups
        ])
            ->render();
        return response()->json([
            'status' => true,
            'html'   => $view
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::find($id);

        $timezoneList= config('timezone');

        foreach ($timezoneList as $k => $v) {
            $start= strpos($v, '(');
            $end= strpos($v, ')');
            $timezoneList[$k]= substr($v, $start+1, $end-$start-1);
        }


        $groups = $this->getUserGroups($user, $id);

        $organizationId = $user->organization_id;
        $roles          = Role::userRole(Auth::user()->role_id)->whereHas('rolepermission', function ($q) use ($organizationId) {
            $q->where('permission_id', 1);
            $q->where('organization_id', $organizationId);
        })->get();

        $reportTo = get_reporters($organizationId, $user->role_id);
        
        $userLanguages = User::getUserLanguages($id);
        $defaultGroup= Group::where('organization_id', $organizationId)
            ->where('name', 'Default')
            ->first();


        $view     = view('user.edit_user_partial', [
            'user_detail' => $user,
            'user_roles' => $roles,
            'groups' => $groups,
            'default_group'=>$defaultGroup,
            'timezone_list' => $timezoneList,
            'report_to' => $reportTo,
            'user_languages' => $userLanguages
        ])
            ->render();

        return response()->json([
            'status' => true,
            'html'   => $view
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(EditUserRequest $request)
    {
        $oldRoleId= User::where('id', $request->user_id)->first()->role_id;


        if ($request->get('role_id') == config('constants.user.role.admin')) {
            $admin = User::where('organization_id', $request->get('organization_id'))->where('id', '!=', $request->user_id)->where('role_id', $request->get('role_id'))->first();

            if (is_object($admin)) {
                return response()->json([
                    'errors' => default_trans(Session::get('userOrganizationId').'/user_list.fail_messages.admin_exist', __('default/user_list.validation_messages.admin_exist')),
                    'status' => 423]);
            }
        }
        $filename = '';
        if ($request->file('logo') != '') {
            $extension = $request->file('logo')->extension();
            $filename  = Storage::putFileAs('user', $request->file('logo'), time() . "." . $extension);
        }
        
        $editUser = User::updateUser($request->all(), $filename);

        if ($editUser->role_id != $oldRoleId) {
            Log::alert("=============Role  is Changed for ".$request->email." &&  Permission Restored As Per Role ==============");
            $editUser->user_permission= $this->getUserDefaultPermission($editUser->role_id, $editUser->organization_id);
            $editUser->save();
        }

        if (!$editUser) {
            return response()->json([
                'status' => config('constants.STATUS_FAIL'),
                'errors' =>  default_trans(Session::get('userOrganizationId').'/user_list.fail_messages.something_wrong', __('default/user_list.fail_messages.something_wrong')),
            ], config('constants.STATUS_FAIL'));
        }
        $createdBy = Auth::user()->id;
        $userOldGroupIds = UserGroup::where('user_id', $request->user_id)->pluck('group_id')->all();
        $userNewGroupIds= [];
        UserGroup::where('user_id', $request->user_id)->delete();
        if (!empty($request->get('group'))) {
            foreach ($request->get('group') as $group_id) {
                $group [] = ['user_id' => $request->user_id, 'group_id' => $group_id, 'created_by' => $createdBy, 'created_at' => Carbon::now()->timestamp];
                $userNewGroupIds[] = $group_id;
            }
            UserGroup::insert($group);
        }
        $this->updateGroupInfoInCache($userOldGroupIds, $userNewGroupIds, $request->user_id);
        return response()->json([
            'status'  => config('constants.STATUS_SUCCESS'),
            'message' => default_trans(Session::get('userOrganizationId').'/user_list.success_messages.user_updated_success', __('default/user_list.success_messages.user_updated_success')),
        ], config('constants.STATUS_SUCCESS'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::destroy($id);

        if (!$user) {
            return response()->json([
                'status' => config('constants.STATUS_FAIL'),
                'errors' => default_trans(Session::get('userOrganizationId').'/user_list.fail_messages.something_wrong', __('default/user_list.fail_messages.something_wrong')),
            ], config('constants.STATUS_FAIL'));
        }
        event(new UserOffline($id, 1));
        return response()->json([
            'status'  => config('constants.STATUS_SUCCESS'),
            'message' => default_trans(Session::get('userOrganizationId').'/user_list.success_messages.user_deleted_success', __('default/user_list.success_messages.user_deleted_success')),
        ], config('constants.STATUS_SUCCESS'));
    }

    public function userStatus(Request $request)
    {
        $status = $request->status ?? '';
        $userId = $request->user_id;
        if ($userId == '' || $status == '') {
            return response()->json([
                'status' => config('constants.STATUS_FAIL'),
                'errors' => default_trans(Session::get('userOrganizationId').'/user_list.fail_messages.something_wrong', __('default/user_list.fail_messages.something_wrong')),
            ], config('constants.STATUS_FAIL'));
        }
        $user         = User::find($userId);
        $user->status = $status;
        $user->save();
        if (!$user->status) {
            event(new UserOffline($user->id, 1));
        }
        
        if (!$user) {
            return response()->json([
                'status' => config('constants.STATUS_FAIL'),
                'errors' =>  default_trans(Session::get('userOrganizationId').'/user_list.fail_messages.something_wrong', __('default/user_list.fail_messages.something_wrong')),
            ], config('constants.STATUS_FAIL'));
        }

        return response()->json([
            'status'  => config('constants.STATUS_SUCCESS'),
            'message' => default_trans(Session::get('userOrganizationId').'/user_list.success_messages.user_status_success', __('default/user_list.success_messages.user_status_success'))
        ], config('constants.STATUS_SUCCESS'));
    }

    public function getReportTo(Request $request)
    {
        if ($request->user_id != null) {
            $organizationId = User::find($request->user_id)->organization_id;
        } else {
            $organizationId = $request->organization_id;
        }
        $reportTo = User::where('organization_id', $organizationId)
            ->where('role_id', '<', $request->role_id)->where('role_id', '!=', config('constants.user.role.super_admin'));
        if (isset($request->user_id) || $request->user_id != '') {
            $reportTo->where('id', '!=', $request->user_id);
        }
        $report = $reportTo->get();
        $view  = view('user.report_to_partial', ['report_to' => $report])
            ->render();

        return response()->json([
            'status' => true,
            'html'   => $view
        ]);
    }

    /**
     * Function to get selected groups combined with all groups.
     *
     * @param object $detail
     * @param integer $id
     */
    private function getUserGroups($user, $id)
    {
        $groups              = Group::getGroup($user->organization_id, $user->id);
        $groupArray          = $groups->toArray();
        return $groupArray;
    }

    public function getUpdatePassword($userId)
    {
        $view     = view('user.update_password_partial', ['userId'=>$userId])
            ->render();
        return response()->json([
            'status' => true,
            'html'   => $view
        ]);
    }

    public function updateUserPassword(UpdatePasswordRequest $request)
    {
        $user = User::find($request->input('user_id'));
        $user->password = $request->input('password');
        $user->save();
        if (!$user) {
            return response()->json([
                'status' => config('constants.STATUS_FAIL'),
                'errors' => Lang::get('message.msg_somthing_wrong'),
            ], config('constants.STATUS_FAIL'));
        }
        return response()->json([
            'status'  => config('constants.STATUS_SUCCESS'),
            'message' => default_trans(Session::get('userOrganizationId').'/user_list.success_messages.update_pwd_success', __('default/user_list.success_messages.update_pwd_success'))
        ], config('constants.STATUS_SUCCESS'));
    }

    /**
     * Function to check whether user to be deleted is reporting manager.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkReportee(UserIdRequest $request)
    {
        try {
            $requestParams = $request->validated();
            $userId = $requestParams['userId'];
            $isReportee = User::checkReportees($userId);
            if ($isReportee === true) {
                return $this->successResponse(default_trans(Session::get('userOrganizationId').'/user_list.validation_messages.no_reportee_found', __('default/user_list.validation_messages.no_reportee_found')));
            } else {
                $countReportees = ($isReportee['otherCount'] > 0) ? ' and +'.$isReportee['otherCount'].' users' : '';
                $message = default_trans(Session::get('userOrganizationId').'/user_list.validation_messages.reportee_found', __('default/user_list.validation_messages.reportee_found', ['name'=> $isReportee['reportee'], 'count' => $countReportees]), ['name'=> $isReportee['reportee'], 'count' => $countReportees]);
                return $this->failResponse($message, $message);
            }
        } catch (\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }



    private function updateGroupInfoInCache($userOldGroupIds, $userNewGroupIds, $userId)
    {
        foreach ($userOldGroupIds as $group_id) {
            Redis::srem('group_' . $group_id, $userId);
        }
        if (User::find($userId)->online_status) {
            foreach ($userNewGroupIds as $group_id) {
                Redis::sadd('group_' . $group_id, $userId);
            }
        }
    }

    public function loginUpdate()
    {
        $userId= Auth::user()->id;
        User::where('id', $userId)->update(['last_login'=>Carbon::now()->timestamp]);
    }
    
    /*
     * Show User Level Permission the specified resource in storage.
     *
     */
    public function showUserPermission($id)
    {
        $userData= User::find($id);

        if ($userData->role_id == config('constants.user.role.associate')) {
            $permission_data = Permission::where('id', '!=', config('constants.PERMISSION.SUPERVISE-TIP-OFF'))->get();
        } else {
            $permission_data = Permission::get();
        }
        foreach ($permission_data as $k => $v) {
            if (isset($userData->user_permission[$v->id])&& $userData->user_permission[$v->id]==true) {
                $permission_data[$k]->permission_status=true;
            } else {
                $permission_data[$k]->permission_status=false;
            }
        }

        //filter permission as per role data

        $rolePermissionData= OrganizationRolePermission::where('organization_id', $userData->organization_id)
            ->where('role_id', $userData->role_id)
            ->pluck('permission_id');
        foreach ($permission_data as $k => $v) {
            if (!$rolePermissionData->search($v->id)) {
                unset($permission_data[$k]);
            }
        }
        if (count($permission_data) > 0) {
            $view = view('user.user_permission_partial', compact('permission_data', 'userData'))
            ->render();
            return response()->json([
            'status' => true,
            'html'   => $view
            ]);
        }
    }

    /**
     * updateUserPermission the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function updateUserPermission(Request $request)
    {
        $requestPermission=$request->user_permisions;

        $masterPermission=OrganizationRolePermission::where('organization_id', $request->organization_id)
            ->where('role_id', config('constants.user.role.admin'))
            ->pluck('permission_id');

        /*
         * This comment part is for -- you  can not assign user permission more than its role
         */
        //checking the  parents permission
//        foreach($request->user_permisions as $k=>$v) {
//            if (!in_array($k, $rolePermissionData)) {
//                return response()->json([
//                    'status' => config('constants.STATUS_FAIL'),
//                    'errors' => Lang::get('Some Extra Permission Found'),
//                ], config('constants.STATUS_FAIL'));
//            }
//        }
        $userPermissionData=[];
        foreach ($masterPermission as $k => $v) {
            if ($requestPermission && array_key_exists($v, $requestPermission)) {
                $userPermissionData[$v]=true;
            } else {
                $userPermissionData[$v]=false;
            }
        }
       
        $user=User::find($request->user_id);
        $user->user_permission= json_encode($userPermissionData);
        $user->save();
        Log::alert(" Before User Online status: ". $user->online_status);
        if ($user->online_status) {
            $permissionStatus=(isset($user->user_permission[config('constants.PERMISSION.CHAT')]) &&($user->user_permission[config('constants.PERMISSION.CHAT')] ==true))?true:false;
            Log::alert("Permission status :: ". $permissionStatus);
            if (!$permissionStatus) {
                event(new UserOffline($user->id));
                Log::alert(" Inside User Online status: ". $user->online_status);
            }
        }
        Log::alert(" Finish User Online status: ". $user->online_status);
        return response()->json([
            'status'  => config('constants.STATUS_SUCCESS'),
            'message' => default_trans(Session::get('userOrganizationId').'/user_list.success_messages.permission_update', __('default/user_list.success_messages.permission_update'))
        ], config('constants.STATUS_SUCCESS'));
    }
    
    /**
     * Fucntion to check user can sneak in.
     *
     * @return boolean
     */
    private function canUserSneakIn()
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $permission = Permission::find(config('constants.PERMISSION.SNEAK'));
        if (Gate::allows('admin') && Session::has('sneak_in')) {
            $is_sneak_in = false;
        } else {
            $is_sneak_in = $user->can('check', $permission);
        }
        return $is_sneak_in;
    }
    
    /**
     * Function for manually clear user login sessions 
     * 
     * @param Request $request
     * @param int $id
     * @return  \Illuminate\Http\JsonResponse
     */
    public function clearLogin(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        if(!$request->ajax()){
            return response()->json([
                'status' => config('constants.STATUS_FAIL'),
                'errors' => "Bad Request"
            ], config('constants.STATUS_FAIL'));
        }
        try {
            $user = User::removeUserSession($id);
            if (!$user) {
                return response()->json([
                    'status' => config('constants.STATUS_FAIL'),
                    'errors' => default_trans(Session::get('userOrganizationId').'/user_list.fail_messages.something_wrong', __('default/user_list.fail_messages.something_wrong')),
                ], config('constants.STATUS_FAIL'));
            }
            
            //Forcefully terminate agent chat
            ChatChannel::makeChatTerminated($id);
            $status = config('constants.STATUS_OFFLINE');
            User::changeStatus($id, $status);
            UserLogin::updateOfflineTime($id);
            event(new UserOffline($id, 1));
            
            return response()->json([
                'status'  => config('constants.STATUS_SUCCESS'),
                'message' => default_trans(Session::get('userOrganizationId').'/user_list.success_messages.user_session_success', __('default/user_list.success_messages.user_session_success')),
            ], config('constants.STATUS_SUCCESS'));
        } catch (\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }
}
