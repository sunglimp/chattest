<?php
namespace App;

use App\Models\Role;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\ChatChannel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;
use App\Models\Organization;
use Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use App\Models\UserGroup;
use App\Models\Group;
use App\Models\Permission;
use App\Models\OrganizationRolePermission;
use App\Models\PermissionSetting;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\Api\V1\AgentController;
use App\Models\LoginHistory;
use Illuminate\Database\Eloquent\Model;
use function GuzzleHttp\json_decode;
use App\Events\UserOffline;
use PushNotification;

class User extends Authenticatable
{
    use Notifiable,
        SoftDeletes;

    const STATUS_ONLINE = 1;

    const STATUS_OFFLINE = 0;

    const STATUS_ACTIVE = 1;

    const ROLE_SUPERADMIN = 1;

    const ROLE_ADMIN = 2;

    const IS_ACTIVE = 1;

    const IS_DEACTIVE = 0;

    const IS_LOGIN = 1;

    const IS_LOGOUT = 0;

    const CHECK_CHAT_AVAILABLE = 1;

    const CHECK_CHAT_NOT_AVAILABLE = 0;

    const MAKE_CHAT_TERMINATED = 1;

    protected $dateFormat = 'U';

    public $timestamps = true;

    protected $dates = [
        'deleted_at'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'gender',
        'mobile_number',
        'role_id',
        'report_to',
        'organization_id',
        'image',
        'no_of_chats',
        'timezone',
        'api_token',
        'user_permission',
        'language'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token'
    ];

    public function report()
    {
        return $this->hasOne('App\Models\Role', 'id', 'report_to');
    }

    public function scopeSuper($query)
    {
        return $query->where('id', 1);
    }

    public function parent()
    {
        return $this->hasOne(self::class, 'id', 'report_to');
    }

    public function child()
    {
        return $this->hasMany(self::class, 'report_to', 'id');
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'user_groups', 'user_id', 'group_id');
    }

    public static function updateUser($request, $filename)
    {
        $user = User::find($request['user_id']);
        $user->name = $request['name'];
        $user->gender = $request['gender'];
        $user->mobile_number = $request['mobile_number'];
        $user->email = $request['email'];
        $user->role_id = $request['role_id'];
        $user->timezone = $request['timezone'];
        $user->language = $request['language'] ?? config('config.default_language');

        if (isset($request['report_to'])) {
            $user->report_to = $request['report_to'];
        }

        if (isset($request['no_of_chats'])) {
            $user->no_of_chats = $request['no_of_chats'];
        }
        $user->organization_id = $request['organization_id'];
        if ($filename != '') {
            $user->image = $filename;
        }

        $user->save();

        return $user;
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }

    public function getimageAttribute()
    {
        if (! empty($this->attributes['image'])) {
            return asset('storage/' . $this->attributes['image']);
        }
        return asset('images/user.jpeg');
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    /**
     * Function to get user details by user token.
     *
     * @param string $value
     * @return User
     */
    public static function getUserDetailByToken($value)
    {
        return User::where('token', '=', $value)->first();
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'id');
    }

    /**
     * Function to format groups.
     *
     * @param Collection $userGroups
     * @throws \Exception
     *
     *
     */
    public static function formatGroup($userGroups)
    {
        try {
            $groups = '';
            $formattedGroups = '';
            if ($userGroups != null) {
                foreach ($userGroups as $group_name) {
                    $groups .= $group_name->name . ',';
                }
                $formattedGroups = trim($groups, ',');
            }
            return $formattedGroups;
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    public static function updatePassword($request)
    {
        $user = User::find($request['user_id']);
        $user->password = $request['password'];

        $user->save();

        return $user;
    }

    /**
     * Function to change user online status.
     *
     * @param string $value
     * @return User
     */
    public static function changeStatus($agentId, $status)
    {
        $user = User::find($agentId);
        $user->online_status = $status;
        $user->save();

        return $user;
    }

    public static function checkReportees($userId)
    {
        try {
            $isUserReportee = self::getReportTo($userId)->count();
            if (empty($isUserReportee)) {
                return true;
            } else {
                $reportee = self::getReportTo($userId)->first();
                $otherCount = $isUserReportee - 1;
                $response = array(
                    'reportee' => $reportee->name ?? '',
                    'otherCount' => $otherCount
                );
                return $response;
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public static function scopeGetReportTo($q, $userId)
    {
        return $q->where('report_to', $userId);
    }

    /**
     *
     * @todo Need to fix remove DB use eloquent
     */
    public static function updateLastLoginDate()
    {
        if (Auth::check()) {
            DB::table('users')->where('id', Auth::User()->id)->update([
                'last_login' => \Carbon\Carbon::now()->timestamp
            ]);
        }
    }

    /**
     * Function to get given permission setting eg- auto_chat delay .
     *
     * @param string $slug
     * @return array
     */
    public function getPermissionSetting($slug)
    {
        $permission = Permission::where('slug', $slug)->first();
        // $organizationRolePermissions= OrganizationRolePermission::where('organization_id', $this->organization_id)
        // ->where('role_id', $this->role_id)
        // ->where('permission_id', $permission->id)
        // ->exists();
        $permissionStatus = (isset($this->user_permission[$permission->id]) && ($this->user_permission[$permission->id] == true)) ? true : false;

        // check permission assign or not
        $permissionSettings = [];
        if ($permissionStatus) {
            $permissionSetting = PermissionSetting::where('organization_id', $this->organization_id)->where('permission_id', $permission->id)->first();
            if (isset($permissionSetting->settings)) {
                $permissionSettings = json_decode($permissionSetting->settings, true);
            }
        }
        return $permissionSettings;
    }

    /**
     * Function to check given permission exist or not.
     *
     * @param string $slug
     * @return boolean T/F
     */
    public function checkPermissionBySlug($slug)
    {
        $permission = Permission::where('slug', $slug)->first();
        $checkRolePermission = OrganizationRolePermission::where('organization_id', $this->organization_id)->where('permission_id', $permission->id)
            ->where('role_id', $this->role_id)
            ->first();
        if (! $checkRolePermission) {
            return false;
        }
        $permissionStatus = (isset($this->user_permission[$permission->id]) && ($this->user_permission[$permission->id] == true)) ? true : false;

        return $permissionStatus;

        //
    }

    public function isSuperAdmin()
    {
        return $this->role_id == self::ROLE_SUPERADMIN;
    }

    public function isAdmin()
    {
        return $this->role_id == self::ROLE_ADMIN;
    }

    public function getLastLoginTzAttribute($timeZone)
    {
        $lastLogin = 'N/A';
        if (isset($this->last_login)) {
            $timeZone = $timeZone ? $timeZone : config('settings.default_timezone');
            $lastLogin = \Carbon\Carbon::createFromTimestamp($this->last_login, $timeZone)->format(config('settings.date_display_format'));
        }
        return $lastLogin;
    }

    public static function getUserCountByRole($organizationId)
    {
        $userCounts = User::select('role_id', DB::raw('count(*) as count'))->whereNotIn('role_id', [
            config('constants.user.role.super_admin'),
            config('constants.user.role.admin')
        ])
            ->where('organization_id', $organizationId)
            ->groupBy('role_id')
            ->pluck('count', 'role_id')
            ->toArray();

        return $userCounts;
    }

    public function getUserPermissionAttribute($value)
    {
        return \json_decode($value, true);
    }

    public static function getSupervisorUsers($userId)
    {
        $users = User::select('users.id as id', 'users.name As user_name', 'roles.name As role')->join('roles', 'users.role_id', '=', 'roles.id')
            ->whereIn('users.id', get_children($userId, false))
            ->get();
        return $users;
    }

    public static function getAgentDetail($agentId)
    {
        $agentDetail = User::select('users.name As agent_name', 'roles.name As role', 'users.role_id as role_id')->join('roles', 'users.role_id', '=', 'roles.id')
            ->where('users.id', $agentId)
            ->first();
        return $agentDetail;
    }

    /**
     * Function to logout user on session inactivity.
     *
     * @throws \Exception
     */
    public static function checkLastActivity()
    {
        try {
            $settingId = config('constants.PERMISSION.SESSION_TIMEOUT');
            $defaultSessionTime = config('constants.LAST_ACTIVITY_SESSION_TIME') * 60;
            $currentTime = Carbon::now()->timestamp;
            $users = self::select('id', 'user_session', 'role_id', 'organization_id', 'chat_notification_status')
                ->where('is_login', self::IS_LOGIN)->get();
            $userIds = [];
            foreach ($users as $user) {
                $organizationId = $user->organization_id ?? '';
                $timeoutValue = ($organizationId) ? PermissionSetting::getSessionTimeoutSettings($organizationId, $settingId) : '';
                $sessionTime = ($timeoutValue) ? ($timeoutValue* 60) : $defaultSessionTime;
                $lastActivityTime = Redis::get("last_activity_" . $user->id);
                $logoutTime = $sessionTime + $lastActivityTime;
                if ($lastActivityTime) {
                    if ($logoutTime < $currentTime) {
                        $isDeleted = Redis::del("last_activity_" . $user->id);
                        if ($isDeleted) {
                            $userIds[] = $user->id;
                            if ($user->role_id != config('constants.user.role.super_admin') && $user->role_id != config('constants.user.role.admin')) {
                                $agent = new AgentController();
                                $agent->offline($user->id, self::CHECK_CHAT_AVAILABLE);
                                $agent->offline($user->id, self::CHECK_CHAT_NOT_AVAILABLE, self::MAKE_CHAT_TERMINATED);
                                event(new UserOffline($user->id, self::MAKE_CHAT_TERMINATED));
                            }
                            self::deleteRedisUserSession($user->user_session);
                            \Log::info("Auto logout removed session for :".$user->id);
                            /*********************Send Push Notification**********************/
                            if ($user->chat_notification_status == config('constants.CHAT_NOTIFICATION_STATUS.ENABLE'))
                            {
                                $loginInfo = LoginHistory::select('device_token', 'device_type')->where('user_id', $user->id)->where('logout_time', null)->orderBy('id', 'desc')->first();
                                if (!empty($loginInfo) && $loginInfo->device_type != config('constants.DEVICE_TYPE.Web') && $loginInfo->device_token !=null && $loginInfo->device_token!='')
                                {
                                    $notificationTitle = default_trans($user->organization_id.'/chat_notifications.session_timeout.title', __('default/chat_notifications.session_timeout.title'));
                                    $notificationBody  = default_trans($user->organization_id.'/chat_notifications.session_timeout.body', __('default/chat_notifications.session_timeout.body'));
                                    $notificationData = ['title' => $notificationTitle , 'body' => $notificationBody];
                                    try {
                                        PushNotification::sendTo($loginInfo->device_token,$notificationData);
                                    } catch (\Exception $e){
                                        \Log::info("Error from the push notification :".$e->getMessage());
                                    }
                                }
                            }
                            /*********************Send Push Notification**********************/
                            // update logout time in history
                            LoginHistory::updateLogoutTime($user->id, $user->role_id);
                        }
                    }
                }
            }
            // Update User table
            if (count($userIds) > 0) {
                \Log::info("Auto logout remove from table for users :". json_encode($userIds));
                self::whereIn('id', $userIds)->update([
                    'is_login' => self::IS_LOGOUT,
                    'online_status' => self::STATUS_OFFLINE,
                    'remember_token' => null,
                    'api_token' => null
                ]);
            }
        } catch (\Exception $exception) {
             \Log::error("Auto logout exception :". json_encode($exception));
            throw $exception;
        }
    }

    /**
     * Function for remove particular user session details
     *
     * @param int $userId
     */
    public static function removeUserSession($userId)
    {
        $user = self::select('id', 'user_session', 'role_id', 'organization_id')
                ->where(['is_login' => self::IS_LOGIN, 'id' => $userId])->first();
        Redis::del("last_activity_" . $userId);
        if ($user) {
            self::deleteRedisUserSession($user->user_session);
        }
        return self::where('id', $userId)->update([
            'is_login' => self::IS_LOGOUT,
            'online_status' => self::STATUS_OFFLINE,
            'remember_token' => null,
            'api_token' => null
        ]);

    }

    /**
     *
     * @param type $userId
     * @return type
     */
    public static function checkUserOnline($userId)
    {
        $user = self::where('id', $userId)->where('online_status', self::STATUS_ONLINE)->first();

        return $user;
    }

    /**
     * FUnction to check whether user logged in
     * @param integer $userId
     * @return Model
     */
    public static function checkUserLogIn($userId)
    {
        try {
            return self::join('organizations', 'organizations.id', '=', 'users.organization_id')
                        ->where('users.id', $userId)
                        ->where(function ($query) {
                            $query->where('is_login', config('config.user_log_in'))
                            ->orWhere('users.status', config('constants.USER_STATUS.INACTIVE'))
                            ->orWhere('organizations.status', config('constants.ORGANIZTION_STATUS.INACTIVE'));
                        })
                    ->first();
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Function to get user languages.
     *
     * @param integer $userId
     * @throws \Exception
     */
    public static function getUserLanguages($userId)
    {
        try {
            $user = self::find($userId);
            $organizationLang = $user->organization->languages;
            $organizationLang = json_decode($organizationLang, true);

            //for old organization, if no organization is set, then for user language will be english
            if (empty($organizationLang)) {
                $organizationLang = [config('config.default_language')];
            }

            $userLanguage = self::find($userId)->language;
            $userSelectedLanguage = [];

            foreach ($organizationLang as $lang) {
                if (!empty($userLanguage) && $lang == $userLanguage) {
                    $userSelectedLanguage[$lang] = ['value' => true, 'label'=>config('config.languages.'.$lang)];
                } else {
                    $userSelectedLanguage[$lang] = ['value' => false, 'label'=>config('config.languages.'.$lang)];
                }
            }
            return $userSelectedLanguage;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Delete user session from redis
     * If we delete this user will session out from the system
     *
     * @param string $userSession
     */
    private static function deleteRedisUserSession($userSession)
    {
        $cacheName = config('cache.prefix');
        Redis::del($cacheName.":".$userSession);
    }

    public static function changeUsersStatus($expiredOrganizationId){

        return self::whereIn('organization_id', $expiredOrganizationId)->update(['status'=>self::IS_DEACTIVE]);
    }

    /**
     *
     * @param int $organizationId
     * @return type
     */
    public static function getAdminUser($organizationId)
    {
        return self::where(['organization_id' => $organizationId, 'role_id' => self::ROLE_ADMIN, 'status' => self::STATUS_ACTIVE])->first();
    }
    
    public static function getSidebar($user, $organizationId)   
    {
     $sidebar = [];   
     if($user->role_id == self::ROLE_SUPERADMIN){
         $sidebar = [
             config('constants.PERMISSION.DASHBOARD-ACCESS') => default_trans($organizationId.'/sidebar.ui_elements_messages.dashboard', __('default/sidebar.ui_elements_messages.dashboard')),
             config('constants.FIXED_SIDEBAR_CUSTOM_PERMISSION_IDS.ORGANIZATION_LIST') =>  default_trans($organizationId.'/sidebar.ui_elements_messages.organization_list', __('default/sidebar.ui_elements_messages.organization_list')),
             config('constants.FIXED_SIDEBAR_CUSTOM_PERMISSION_IDS.PERMISSION_LIST') => default_trans($organizationId.'/sidebar.ui_elements_messages.permission_list', __('default/sidebar.ui_elements_messages.permission_list')),
             config('constants.FIXED_SIDEBAR_CUSTOM_PERMISSION_IDS.USER_LIST') => default_trans($organizationId.'/sidebar.ui_elements_messages.user_list', __('default/sidebar.ui_elements_messages.user_list')),
             config('constants.PERMISSION.CANNED-RESPONSE') => default_trans($organizationId.'/sidebar.ui_elements_messages.canned_response', __('default/sidebar.ui_elements_messages.canned_response')),
             config('constants.PERMISSION.BAN-USER') => default_trans($organizationId.'/sidebar.ui_elements_messages.banned_users', __('default/sidebar.ui_elements_messages.banned_users')),
             config('constants.PERMISSION.LOGIN-HISTORY') => default_trans($organizationId.'/sidebar.ui_elements_messages.user_logging', __('default/sidebar.ui_elements_messages.user_logging')),
             config('constants.FIXED_SIDEBAR_CUSTOM_PERMISSION_IDS.CUSTOMIZE_FIELDS') => default_trans($organizationId.'/sidebar.ui_elements_messages.customize_fields', __('default/sidebar.ui_elements_messages.customize_fields'))             
             ];  
    }
    if($user->role_id == self::ROLE_ADMIN){
        $permissionSidebar = OrganizationRolePermission::getUserSideBarPermissions($user, config('constants.ADMIN_PERMISSION_IDS_SIDEBAR'));
         $staticSidebar = [
            config('constants.FIXED_SIDEBAR_CUSTOM_PERMISSION_IDS.PERMISSION_LIST') => default_trans($organizationId.'/sidebar.ui_elements_messages.permission_list', __('default/sidebar.ui_elements_messages.permission_list')),
            config('constants.PERMISSION.ARCHIVE_CHAT') => default_trans($organizationId.'/sidebar.ui_elements_messages.archive', __('default/sidebar.ui_elements_messages.archive')),
             config('constants.FIXED_SIDEBAR_CUSTOM_PERMISSION_IDS.USER_LIST') => default_trans($organizationId.'/sidebar.ui_elements_messages.user_list', __('default/sidebar.ui_elements_messages.user_list')),
            config('constants.FIXED_SIDEBAR_CUSTOM_PERMISSION_IDS.CUSTOMIZE_FIELDS') => default_trans($organizationId.'/sidebar.ui_elements_messages.customize_fields', __('default/sidebar.ui_elements_messages.customize_fields')),             
             ];
         $sidebar = $staticSidebar + $permissionSidebar;
    }
    if($user->role_id == config('constants.user.role.manager') || $user->role_id == config('constants.user.role.team_lead') || $user->role_id == config('constants.user.role.associate') ){
        $permissionSidebar = OrganizationRolePermission::getUserSideBarPermissions($user, config('constants.PERMISSION_IDS_SIDEBAR')); 
        $staticSidebar = [
           config('constants.PERMISSION.CHAT') =>   default_trans($organizationId.'/sidebar.ui_elements_messages.chat', __('default/sidebar.ui_elements_messages.chat')),
           config('constants.PERMISSION.ARCHIVE_CHAT') =>  default_trans($organizationId.'/sidebar.ui_elements_messages.archive', __('default/sidebar.ui_elements_messages.archive')),
             ];
        $sidebar = $staticSidebar + $permissionSidebar;
    }     
     return  $sidebar;   
        
    }
}
