<?php

namespace App\Http\Controllers;

use Doctrine\DBAL\Events;
use Illuminate\Http\Request;
use Auth;
use App\Models\Group;
use App\Http\Requests\Preferences\AddGroupRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;

class GroupController extends BaseController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if(auth()->user()->can('groups.create')) {
            $organizationId = $request->input('organization_id');
            $groups = Group::where('organization_id', $organizationId)->get();
            $view = view('permission.add_group_popup', ['group' => $groups])
                ->render();
            return response()->json([
                'status' => true,
                'html' => $view
            ]);
        }
        else{
            return response()->json(['status' => false]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AddGroupRequest $request)
    {
        try {
        $authUser = Auth::user();
        $request['created_by']= $authUser->id;
        $addGroup = Group::create($request->all());
        
        return response()->json([
                    'status' => config('constants.STATUS_SUCCESS'),
                    'data'=> $addGroup,
                ], config('constants.STATUS_SUCCESS'));
         } catch (\Exception $exception) {
            \Log::info("GroupContoller::Store error is ".json_encode($exception));
            return response()->json([
            'status' => config('constants.STATUS_FAIL') 
            ], config('constants.STATUS_FAIL'));  
            }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
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
        $group= Group::find($id);
        $user = Auth::user();
        try {
            if ($user->can('delete', $group)) {
                $is_exist = Group::isGroupUsed($id);
                if (!empty($is_exist)) {
                    $fail_msg = default_trans(Session::get('userOrganizationId').'/permission.fail_messages.group_already_used', __('default/permission.validation_messages.group_already_used'));
                    return $this->failResponse($fail_msg);
                }
                Group::destroy($id);
                return response()->json([
                    'status' => config('constants.STATUS_SUCCESS'),
                ], config('constants.STATUS_SUCCESS'));
            }
        } catch (\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
        
        return response()->json([
            'status' => config('constants.STATUS_FAIL'),
        ], config('constants.STATUS_FAIL'));
    }
}
