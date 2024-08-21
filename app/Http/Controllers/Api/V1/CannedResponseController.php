<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\CannedResponse;
use App\Http\Resources\CannedResponseCollection;
use App\Http\Controllers\BaseController;
use App\Http\Requests\APIRequest\UserIdRequiredRequest;
use Auth;
use App\Http\Requests\APIRequest\CannedResponse\CannedResponseRequest;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\APIRequest\CannedResponse\EditCannedResponseRequest;
use Illuminate\Http\Request;

class CannedResponseController extends BaseController
{

    /**
     * @api {get} /cannedResponses?userId={userId} Request Canned Response
     * @apiVersion 1.0.0
     * @apiName Canned Response
     * @apiGroup Canned-Response
     *
     * @apiSuccess {Object[]} data Response data payload
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
     * @apiSuccessExample Canned Responses:
     *     HTTP/1.1 200 OK
     *     {
     *             "data": [
     *                  {
     *                       "#canned": [
     *                           "canned1"
     *                       ]
     *                  },
     *                  {
     *                      "#canned2c": [
     *                           "canned2"
     *                          ]
     *                  }
     *                   ]
     *       }
     */
    
    
    public function index(UserIdRequiredRequest $request)
    {
        try {
            $requestParams  = $request->validated();
            $userId         = Auth::user()->id;
            $cannedResponse = CannedResponse::getCannedResponse($userId);
            $cannedResponse = collect($cannedResponse);
            return new CannedResponseCollection($cannedResponse);
        } catch (\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }

    /**
     * @api {post} /cannedResponses/add Canned Response Add
     * @apiVersion 1.0.0
     * @apiName Canned Response Add
     * @apiGroup Canned-Response
     *
     *
     * @apiHeader {String} Authorization API token
     * @apiHeader {String} Content-Type Content type of the payload
     * 
     * @apiParam {String} shortcut Canned Response Shortcut
     * @apiParam {String} response Canned Response Response
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
     * @apiSuccessExample Add Response:
     *     HTTP/1.1 200 OK
     *     {
     *      "message": "Canned Response added successfully",
     *      "status": true,
     *       "data": []
     *     }
     *
     * @apiErrorExample Duplicate Error:
     *     HTTP/1.1 422 OK
     *     {
     * "message": "Duplicate combination of shortcut and response is not allowed",
     * "status": false,
     * "data": []
     *    }
     *
     * @apiErrorExample Size Error:
     *     HTTP/1.1 422 OK
     *     {
     * "message": "The given data was invalid.",
     * "errors": {
     *   "shortcut": [
     *       "Only 20 characters allowed"
     *   ]
     * }
     * } 
     *
     */
    
    
    public function store(CannedResponseRequest $request)
    {
        try {
            $requestParams  = $request->all();
            $shortcut       = $requestParams['shortcut'];
            $response       = $requestParams['response'];
            $loggedInUserId = Auth::id();
            $isUnique       = CannedResponse::checkUnique($shortcut, $response, $loggedInUserId);
            if ($isUnique === false) {
                return $this->failResponse(default_trans(Session::get('userOrganizationId') ?? Auth::user()->organization_id . '/canned_response.validation_messages.unique_combination', __("message.unique_combination")), array(), 422);
            }

            $isAdded = CannedResponse::add($requestParams);
            if ($isAdded instanceof CannedResponse) {
                return $this->successResponse(default_trans(Session::get('userOrganizationId') ?? Auth::user()->organization_id . '/canned_response.success_messages.canned_response_sucess', __('message.canned_response_sucess')));
            } else {
                return $this->failResponse(default_trans(Session::get('userOrganizationId') ?? Auth::user()->organization_id . '/canned_response.fail_messages.canned_response_fail', __('message.canned_response_fail')));
            }
        } catch (\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }

    /**
     * @api {put} /cannedResponses/update Canned Response Update
     * @apiVersion 1.0.0
     * @apiName Canned Response Update
     * @apiGroup Canned-Response
     *
     *
     * @apiHeader {String} Authorization API token
     * @apiHeader {String} Content-Type Content type of the payload
     * 
     * @apiParam {String} shortcut Canned Response Shortcut
     * @apiParam {String} response Canned Response Response
     * @apiParam {String} cannedResponseId Canned Response Id
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
     * @apiSuccessExample Edit Response:
     *     HTTP/1.1 200 OK
     *     {
     * "message": "Canned Response edited successfully",
     * "status": true,
     * "data": []
     * }
     *
     */
    
    
    public function update(EditCannedResponseRequest $request)
    {
        try {
            $requestParams  = $request->validated();
            $loggedInUserId = Auth::id();
            $shortcut       = $requestParams['shortcut'];
            $response       = $requestParams['response'];
            $isUnique       = CannedResponse::checkUnique($shortcut, $response, $loggedInUserId, $requestParams['cannedResponseId']);
            if ($isUnique === false) {
                return $this->failResponse(__("message.unique_combination"), array(), 422);
            }
            $isEdited = CannedResponse::edit($requestParams, $loggedInUserId);
            if($isEdited){           
            return $this->successResponse(__('message.canned_response_edit_sucess'));
            }
            else{
             return $this->failResponse(__('Canned response edit failed'));
            }
        } catch (\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }

    /**
     * @api {delete} /cannedResponses/{cannedResponseId}  Canned Response Delete
     * @apiVersion 1.0.0
     * @apiName Canned Response Delete
     * @apiGroup Canned-Response
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
     * @apiSuccessExample Delete Response:
     *     HTTP/1.1 200 OK
     *     {
     * "message": "Canned Response deleted successfully",
     * "status": true,
     * "data": []
     * }
     */
    
    
    public function delete($encryptCannedResponseId)
    {
        try {
            $loggedInuserId = Auth::id();
            $isDeleted      = CannedResponse::deleteCannedResponse($encryptCannedResponseId, $loggedInuserId);
            if ($isDeleted) {
                return $this->successResponse(default_trans(Session::get('userOrganizationId') ?? Auth::user()->organization_id . '/canned_response.success_messages.canned_response_delete_sucess', __('message.canned_response_delete_sucess')));
            } else {
                return $this->failResponse(default_trans(Session::get('userOrganizationId') ?? Auth::user()->organization_id . '/canned_response.fail_messages.canned_response_delete_fail', __('message.canned_response_delete_fail')));
            }
        } catch (\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }

    /**
     * @api {get} /cannedResponses/canned-response/{cannedResponseId} Canned Response Detail
     * @apiVersion 1.0.0
     * @apiName Canned Response Detail
     * @apiGroup Canned-Response
     *
     * @apiSuccess {Object[]} data Response data payload
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
     * @apiSuccessExample Detail Canned Response:
     *     HTTP/1.1 200 OK
     *     {
     * "message": "Canned Response fetched successfully",
     * "status": true,
     * "data": {
     *   "shortcut": "hi",
     *   "response": "hi, how are you?"
     *   }
     * }
     */
    
    
    public function getCannedResponseById($encryptCannedResponseId)
    {
        try {
            $cannedResponse = CannedResponse::getCannedResponseById($encryptCannedResponseId);
            if (is_null($cannedResponse)) {
                return $this->failResponse(default_trans(Session::get('userOrganizationId') ?? Auth::user()->organization_id . '/canned_response.fail_messages.canned_response_fetch_fail', __('message.canned_response_fetch_fail')));
            } else {
                return $this->successResponse(default_trans(Session::get('userOrganizationId') ?? Auth::user()->organization_id . '/canned_response.success_messages.canned_response_fetch_sucess', __('message.canned_response_fetch_sucess')), $cannedResponse);
            }
        } catch (\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }

    /**
     * @api {get} /cannedResponses/canned-responses Canned Response List
     * @apiVersion 1.0.0
     * @apiName Canned Response List
     * @apiGroup Canned-Response
     *
     * @apiSuccess {Object[]} data Response data payload
     *
     * @apiHeader {String} Authorization API token
     * @apiHeader {String} Content-Type Content type of the payload
     *
     * @apiParam {String} search Search value. Searching on shortcut and response column.
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
     * @apiSuccessExample List Canned Response:
     *     HTTP/1.1 200 OK
     *     {
     * "message": "Canned Response fetched successfully",
     * "status": true,
     *"data": {
     *"current_page": 1,
     * "data": [
     * {
     * "shortcut": "#hello",
     * "response": "Hello, How are you?",
     * "created_date": "Sep 23, 2020",
     * "id": 0,
     * "can_update": 1,
     * "cannedResponsId": "eyJpdiI6ImxRK0ZtVHVwZnZuYkw2RDNrVXpjdnc9PSIsInZhbHVlIjoiR3ZJZFBtSUJYMlVCM0hYZWJWQkJEQT09IiwibWFjIjoiZTk3ZGVmNWQxYTBhMWM3YjhmOGNhYjc1YTU3MjMzMWMyYTVmMzg4M2UwM2QzZjU0ZDhkMGJmMjhlMGNmMDMxNiJ9"
     * },
     * {
     * "shortcut": "#Finance",
     * "response": "How we can help you in finance?",
     * "created_date": "Sep 23, 2020",
     * "id": 0,
     * "can_update": 1,
     * "cannedResponsId": "eyJpdiI6IlwvTGc4Q2JsYmdEd2JhclljWHZiN1BnPT0iLCJ2YWx1ZSI6Ilh2SlhsV0RONWRUQk1kMFpMZ1V5Q0E9PSIsIm1hYyI6Ijc1YjhiMDlhNWQ3Y2RmM2YzMmE1MjAxNTZkMTcxZjM3M2Q0ODA4OWVjZGM4OTgxNmU4ODNiZGJmMTdlMDJmODgifQ=="
     * },
     * ],
     * "first_page_url": "{baseURL}/api/v1/cannedResponses/get-canned-responses?page=1",
     * "from": 1,
     * "last_page": 1,
     * "last_page_url": "{baseURL}/api/v1/cannedResponses/get-canned-responses?page=1",
     * "next_page_url": null,
     * "path": "{baseURL}/api/v1/cannedResponses/get-canned-responses",
     * "per_page": 10,
     * "prev_page_url": null,
     * "to": 2,
     * "total": 2
     * }
     * }
     */
    
    
    public function getCannedResponses(Request $request)
    {
        try {
            $search = $request->input('search') ?? '';
            $userId = Auth::id() ?? 0;
            date_default_timezone_set(Auth()->user()->timezone);
            $data   = CannedResponse::getCannedResponse($userId, false, $search);
            return $this->successResponse(default_trans(Session::get('userOrganizationId') ?? Auth::user()->organization_id . '/canned_response.success_messages.canned_response_fetch_sucess', __('message.canned_response_fetch_sucess')), $data);
        } catch (\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }

}
