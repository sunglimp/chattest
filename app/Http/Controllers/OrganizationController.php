<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use App\Models\Organization;
use Illuminate\Support\Facades\Lang;
use App\Http\Requests\Organization\StoreOrganizationRequest;
use App\Http\Requests\Organization\EditOrganizationRequest;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\Storage;
use Camroncade\Timezone\Facades\Timezone;
use function GuzzleHttp\json_encode;
use function GuzzleHttp\json_decode;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;


class OrganizationController extends BaseController
{

    public function index()
    {
        return view('organization.list');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {


        $timezoneList= config('timezone');

        foreach ($timezoneList as $k => $v) {
            $start= strpos($v, '(');
            $end= strpos($v, ')');
            $timezoneList[$k]= substr($v, $start+1, $end-$start-1);
        }

        $languages = config('config.languages');
        $view         = view('organization.add_organization_partial', [
                                        'timezone_list' => $timezoneList,
                                        'languages'     => $languages
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
    public function store(StoreOrganizationRequest $request)
    {
        $filename = '';
        if ($request->has('image')) {
            $filename = Storage::putFileAs('logo', $request->file('image'), time().'.png');
        }
        $request['logo'] = $filename;
        $request['surbo_unique_key'] = str_random(config('config.ORG_KEY_LENGTH'));

        if (!empty($request->language)) {
            $request['languages'] = json_encode($request->language);
        } else {
            $request['languages'] = json_encode([config('config.default_language')]);
        }
        $addOrganization = Organization::create($request->all());

        if (!$addOrganization) {
            return response()->json([
                        'status' => config('constants.STATUS_FAIL'),
                        'errors' => [[Lang::get('message.msg_somthing_wrong')]],
                            ], config('constants.STATUS_FAIL'));
        }

        return response()->json([
                    'status' => config('constants.STATUS_SUCCESS'),
                    'message' => [[Lang::get('message.msg_sucessfully_created')]],
                    'id' => $addOrganization->id,
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
        $detail = Organization::find($id);

        return response()->json([
                    'status' => config('constants.STATUS_SUCCESS'),
                    'message' => [],
                    'data' => $detail,
                        ], config('constants.STATUS_SUCCESS'));
    }

    /**
     * Display the All  Organization .
     *
     * @param  void
     * @return  All Organization  Data in Json For Data table
     */
    public function getOrganization()
    {
        $model= Organization::orderBy('created_at', 'desc')->get();
        return Datatables::of($model)

                            ->addColumn('account_type', function($org) {
                                return ($org->is_testing ? 'Demo' : 'Live');
                            })
                            ->addColumn('status', 'organization.datatables.status')
                            ->addColumn('logo', 'organization.datatables.logo')
                            ->addColumn('action', 'organization.datatables.action')
                            ->rawColumns(['status','action','logo'])
                            ->toJson();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $detail = Organization::find($id);
            $timezoneList= config('timezone');

            foreach ($timezoneList as $k => $v) {
                $start= strpos($v, '(');
                $end= strpos($v, ')');
                $timezoneList[$k]= substr($v, $start+1, $end-$start-1);
            }

            $orgLangauges = Organization::findLanguages($detail);

            $view = view('organization.edit_organization_partial', ['organization_detail' => $detail,
                                                                    'timezone_list'=>$timezoneList,
                                                                     'org_langauges' => $orgLangauges
                                                                    ])
                    ->render();
            return response()->json([
                'status' => true,
                'html' => $view
            ]);
        } catch (\Exception $exception) {
            log_exception($exception);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(EditOrganizationRequest $request)
    {
        if ($request->hasFile('image')) {
            $filename = Storage::putFileAs('logo', $request->file('image'), time().'.png');
            $request['logo']= $filename;
        }
        $request['languages'] = json_encode($request->language);

        $editOrganization = Organization::updateOrganization($request->all());

        if (!$editOrganization) {
            return response()->json([
                        'status' => config('constants.STATUS_FAIL'),
                        'errors' => [[Lang::get('message.msg_somthing_wrong')]],
                            ], config('constants.STATUS_FAIL'));
        }
        if($editOrganization->validity_date == '' || $editOrganization->validity_date > Carbon::yesterday()->toDateString()){
            $editOrganization->status = Organization::ACTIVE;
            $editOrganization->save();
            User::where('organization_id', $request->input('organization_id'))->update(['status'=>User::IS_ACTIVE]);
        }
        return response()->json([
                    'status' => config('constants.STATUS_SUCCESS'),
                    'message' => [[Lang::get('message.msg_sucessfully_created')]],
                    'id' => $editOrganization->id,
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
        $organization = Organization::destroy($id);

        if (!$organization) {
            return response()->json([
                        'status' => config('constants.STATUS_FAIL'),
                        'errors' => [[Lang::get('message.msg_somthing_wrong')]],
                            ], config('constants.STATUS_FAIL'));
        }

        return response()->json([
                    'status' => config('constants.STATUS_SUCCESS'),
                    'message' => [[Lang::get('message.msg_sucessfully_created')]],
                        ], config('constants.STATUS_SUCCESS'));
    }

    public function organizationStatus(Request $request)
    {
//        dd($request->toArray());
        $status = $request->status ?? '';
        $organizationId = $request->organization_id;
        if ($organizationId == '' || $status == '') {
            return response()->json([
                        'status' => config('constants.STATUS_FAIL'),
                        'errors' => Lang::get('message.msg_somthing_wrong'),
                            ], config('constants.STATUS_FAIL'));
        }

        $organization = Organization::find($organizationId);

        if($organization->validity_date != NULL && $organization->validity_date < Carbon::today()->toDateString() && $status== Organization::STATUS_ACTIVE){
          return response()->json([
                        'status' => 423,
                        'errors' =>Lang::get('message.validity_update_msg'),
                            ], config('constants.STATUS_FAIL'));
        }

        $organization->status = $status;

        $organization->save();

        User::where('organization_id', $organizationId)->update(['status'=>$status]);


        if (!$organization) {
            return response()->json([
                        'status' => config('constants.STATUS_FAIL'),
                        'errors' => Lang::get('message.msg_somthing_wrong'),
                            ], config('constants.STATUS_FAIL'));
        }

        return response()->json([
                    'status' => config('constants.STATUS_SUCCESS'),
                    'message' => Lang::get('message.msg_status_changed')
                        ], config('constants.STATUS_SUCCESS'));
    }

    /**
     * Function to get organization key.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrganizationKey(Organization $organization)
    {
        try {
            $key = Organization::getOrganizationKey($organization);
            if (!empty($key)) {
                return $this->successResponse(_('message.key_fetched'), $key);
            } else {
                return $this->failResponse(_('message.key_fetch_failed'));
            }
        } catch (\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }
}
