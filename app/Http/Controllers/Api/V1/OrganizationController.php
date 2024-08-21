<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Http\Controllers\BaseController;

class OrganizationController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
           $organizations = Organization::getOrgList();
           if (!$organizations->isEmpty()) {
               return $this->successResponse(__('message.org_list_success'), $organizations);
           } else {
               return $this->failResponse(__('message.org_list_fail'));
           }
        } catch(\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }
}
