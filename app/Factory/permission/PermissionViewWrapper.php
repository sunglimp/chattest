<?php

namespace App\Factory\permission;

use App\Models\PermissionSetting;
use Illuminate\Support\Facades\Gate;
use Auth;
use App\Models\Organization;

class PermissionViewWrapper
{

    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function viewSetting()
    {

        $data = [];
        $organization_id = $this->request->input('organization_id');
        if (Gate::allows('admin')) {
            $organization_id = Auth::user()->organization_id;
        }
        if (config('constants.PERMISSION.' . $this->request->input('permission')) != config('constants.PERMISSION.TMS-KEY')) {
            $settingData = PermissionSetting::getPermissionSettingData($organization_id, config('constants.PERMISSION.' . $this->request->input('permission')));
            $data        = [];
            if (config('constants.PERMISSION.' . $this->request->input('permission')) == config('constants.PERMISSION.SESSION_TIMEOUT')) {
                $max_hour = (config('constants.LAST_ACTIVITY_SESSION_TIME') > 59) ? (config('constants.LAST_ACTIVITY_SESSION_TIME')/60) : 0;
                $minute = (config('constants.LAST_ACTIVITY_SESSION_TIME') > 59) ? (config('constants.LAST_ACTIVITY_SESSION_TIME')%60) : 0;
                $max_minute = (config('constants.LAST_ACTIVITY_SESSION_TIME') < 60) ? config('constants.LAST_ACTIVITY_SESSION_TIME') : (($minute!=0) ? $minute : 30);
                $data = ['max_hour' => (int)$max_hour, 'max_minute' => (int)$max_minute];
            }
            if ($settingData) {
                $data = json_decode($settingData->settings);
            }
        } else {
            $settingData = Organization::find($organization_id);
            $data        = "";
            if ($settingData) {
                $data = $settingData->tms_unique_key;
            }
        }
        
        $view = view($this->request->input('popup'), ['data' => $data])
                ->render();
        return response()->json([
            'status' => true,
            'html'   => $view
        ]);
    }
}
