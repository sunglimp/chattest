<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organization;
use Yajra\Datatables\Datatables;
use App\Models\LoginHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;

class LoginHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $organization = Organization::active()->orderBy('created_at', 'desc')->get();
        return view('loginHistory.list', ['organization' => $organization]);
    }
    
    public function getUsersLoginHistory(Request $request)
    {
        $startDate = Carbon::parse($request->input('startDate'))->format('Y-m-d');
        $endDate   = Carbon::parse($request->input('endDate'))->format('Y-m-d');
        
        $organizationId = $request->input('organization_id');
        $userIds = [];
        if (!Gate::allows('superadmin') && !Gate::allows('admin')) {
            $userIds = get_children_with_self(auth()->user()->id);
        }
        $users = LoginHistory::getLoggedInUserList($startDate, $endDate, $organizationId, $userIds);
        $timeZone = auth()->user()->timezone;
        
        return Datatables::of($users)
                            ->addColumn('name', 'loginHistory.datatables.name')
                            ->editColumn('last_login', function (LoginHistory  $users) use ($timeZone) {
                                return  Carbon::createFromTimestamp($users->last_login, $timeZone)->format('Y-m-d H:i');
                            })
                            ->editColumn('duration', function (LoginHistory $users) {
                                return  Carbon::createFromTimestamp($users->duration)->format('H:i');
                            })
                            ->rawColumns(['name'])
                            ->make(true);
    }
    
    public function getUserHistory($id)
    {
        return view('loginHistory.user_history_list')->with('id', $id);
    }
    
    public function getUserLoginHistory(Request $request)
    {
        $startDate = Carbon::parse($request->input('startDate'))->format('Y-m-d');
        $endDate   = Carbon::parse($request->input('endDate'))->format('Y-m-d');      
        $userId = $request->input('user_id');
        $timeZone = auth()->user()->timezone;
        $users = LoginHistory::getUserHistoryList($startDate, $endDate, $userId);
        
        return Datatables::of($users)
                            ->addColumn('chat_count', 'loginHistory.datatables.chat_count')
                            ->editColumn('login_time', function (LoginHistory  $users) use ($timeZone) {
                                return  Carbon::createFromTimestamp($users->login_time, $timeZone)->format('Y-m-d H:i');
                            })
                            ->editColumn('logout_time', function (LoginHistory $users) use ($timeZone) {
                                return  Carbon::createFromTimestamp($users->logout_time, $timeZone)->format('Y-m-d H:i');
                            })
                            ->editColumn('duration', function (LoginHistory $users) {
                                return  Carbon::createFromTimestamp($users->duration)->format('H:i');
                            })
                            ->rawColumns(['chat_count'])
                            ->make(true);
    }
    
}
