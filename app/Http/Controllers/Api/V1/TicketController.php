<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\ChatChannel;
use http\Env\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\BaseController;
use Auth;
use App\Models\TicketField;
use App\Models\Ticket;
use App\Http\Requests\APIRequest\UpdateTicketRequest;
use App\Facades\TMSAPIFacade;
use App\Libraries\TMSAPI;
use App\Http\Requests\APIRequest\Ticket\AddTicketRequest;
use Illuminate\Support\Facades\Storage;

class TicketController extends BaseController
{

    private static $source = 'ticket';
    /**
     * @api {post} /update-ticket-fields Update Ticket fields
     * @apiVersion 1.0.0
     * @apiName Update Ticket fields
     * @apiGroup Tickets
     *
     *
     * @apiParamExample {json} Payload-Example:
     * {
     *       {
     *          "application_id": "3",
     *              "form_fields": [
     *               {
     *                  "fieldname": "firt_name",
     *                  "group_name": "Requester Details",
     *                  "is_mandatory": 1,
                        "representation_name": "First Name"
                    },
                    {
                        "field_name": "email",
                        "group_name": "Requester Details",
                        "is_mandatory": 1,
                        "representation_name": "Email"
                    },
                    {
                        "field_name": "mobile",
                        "group_name": "Requester Details",
                        "is_mandatory": 1,
                        "representation_name": "Mobile"
                    }
            ]
        }
     *}
     * @apiHeader {String} Authorization TMS Integeration key
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
     *
     * @apiSuccessExample Agent Available:
     *     HTTP/1.1 200 OK
     *     {
     *          "message":"Ticket fields updated successfully",
     *          "status":true,
     *          "data":[]
     *     }
     *
     * @apiError Ticket fields update failed
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *       "error": "Ticket fields update failed"
     *     }
     *
     */
    public function updateFields(UpdateTicketRequest $request)
    {
        try {
            $requestParams = $request->all();
            $tmsKey = $request->bearerToken();
            $isUpdated = TicketField::updateTicketFields($requestParams, $tmsKey);
            if (empty($isUpdated)) {
                return $this->failResponse(__("message.update_ticket_fail"), array(), Response::HTTP_BAD_REQUEST);
            } else {
                return $this->successResponse(__("message.update_ticket_success"));
            }
        } catch (\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }

    /**
     * @api {get} /tickets/fields/{type} Get Ticket fields
     * @apiVersion 1.0.0
     * @apiName Get Ticket fields
     * @apiGroup Tickets
     *
     *
     * @apiHeader {String} Authorization API token
     * @apiHeader {String} Content-Type Content type of the payload
     *
     * @apiParam {Integer} type 1=> LQS, 2=>LMS, 3=>TMS
     *
     * @apiHeaderExample {json} Content-Type:
     *     {
     *         "Content-Type" : "application/json"
     *     }
     * @apiHeaderExample {json} Authorization:
     *     {
     *         "Authorization" : "Bearer  l39I1Pyerw0DhDUTviio"
     *     }
     * @apiSuccessExample Agent Available:
     *     HTTP/1.1 200 OK
     *     {
     *          "message":"Ticket fields.",
     *          "status":true,
     *           "data": [
        {
            "fields": [
                {
                    "field_name": "first_name",
                    "group_name": "Basic Details",
                    "is_mandatory": 1,
                    "representation_name": "First name"
                },
                {
                    "field_name": "gender",
                    "group_name": "Basic Details",
                    "is_mandatory": 0,
                    "representation_name": "Gender"
                }
            ]
        }
    ]
     *     }
     *
     * @apiError ticket fields fetching failed
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *       "error": "Ticket fields failed"
     *     }
     *
     */

    public function getTicketFields($applicationId)
    {
        try {
            $organizationId = Auth::user()->organization_id;
            $ticketFields = TicketField::getFields($applicationId, $organizationId);
            if ($ticketFields) {
                $fields['fields'] = json_decode($ticketFields->fields_data);
                return  $this->successResponse("Ticket Fields", [$fields]);
            } else {
                return  $this->failResponse("No data found", []);
            }
        } catch (\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }

    /**
     * @api {post} /tickets/create-ticket Add Ticket fields
     * @apiVersion 1.0.0
     * @apiName Add Ticket fields
     * @apiGroup Tickets
     *
     ** @apiParam {Json} ticket_data Ticket fields data
     * @apiParam {Integer} chat_id Channel Id.
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
     * @apiSuccessExample Agent Available:
     *     HTTP/1.1 200 OK
     *     {
     *          "message":"Ticket Created Successfully",
     *          "status":true,
     *               "data": [
        {
            "ticket_id": 12
        }
    ]
     *     }
     *
     * @apiError ticket fields fetching failed
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *       "error": "Ticket fields failed"
     *     }
     *
     */

    public function addTicket(AddTicketRequest $request)
    {
        try {
            $organizationId = Auth::user()->organization_id;
            $applicationId = $request->input('application');
            $chatId = $request->input('chat_id');

            $ticketDataDetails = $request->all();
            unset($ticketDataDetails['application']);
            unset($ticketDataDetails['chat_id']);
            $ticketAttachments = self::ticketFileUploads($request, $applicationId, $organizationId);
            $ticketData = array_merge($ticketDataDetails, $ticketAttachments);
            $tmsData= ['channel'=>config('tms.channel'),'data'=> $ticketData];

            $ticket = ['chat_id'=> $chatId,'application_id'=>$applicationId,'organization_id'=>$organizationId,'ticket_data'=> json_encode($ticketData)];
            $TicketAdd = Ticket::create($ticket);

            $tmsKey = TicketField::getTmsKeyByOrganization($organizationId);

            //  curl request to create ticket on tms platform to get ticket_id
            $ticket = self::addTicketTms($tmsData, $tmsKey->tms_unique_key, $applicationId);

            if ($applicationId == config('constants.TICKET_APPLICATION.LQS')) {
                $ticketId = $ticket['lead_id']??'';
                $message =  default_trans($organizationId.'/chat.success_messages.lead_created', __('default/chat.success_messages.lead_created'));
            } elseif ($applicationId == config('constants.TICKET_APPLICATION.TMS')) {
                $ticketId = $ticket['ticket_id']??'';
                $message =  default_trans($organizationId.'/chat.success_messages.ticket_created', __('default/chat.success_messages.ticket_created'));
            }

            if ($ticketId != '') {
                $TicketAdd->ticket_id = $ticketId;
                $TicketAdd->save();

                $channel = ChatChannel::find($chatId);
                if ($channel->ticket_type != null) {
                    $channel->ticket_status = ChatChannel::CHAT_STATUS_ACCEPT;
                    $channel->save();
                }
            }
            if ($ticketId) {
                $data['ticket_id'] = $ticketId;
                return  $this->successResponse($message, [$data]);
            } else {
                return  $this->failResponse("Something went wrong", []);
            }
        } catch (\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }

    /**
     * Function for ticket/lead attachment file upload
     *
     * @param request $request
     * @param int $applicationId
     * @param int $organizationId
     * @return array
     */
    private static function ticketFileUploads($request, $applicationId, $organizationId)
    {
        $isS3 = (config('tms.ticket_attachment_disk')=='s3') ? true : false;
        $attachmentData = $attachments = [];
        $ticketFields = TicketField::getFields($applicationId, $organizationId);
        $fieldsDataDetails = $ticketFields['fields_data'] ?? [];
        $fieldsData = (!empty($fieldsDataDetails)) ? json_decode($fieldsDataDetails, true) : [];
        $fileSuffix = config('tms.ticket_attachment_file_suffix');
        foreach ($fieldsData as $field) {
            if (isset($field['is_attachment']) && ($field['is_attachment'] == 1)) {
                $attachments[] = $field['field_name'].$fileSuffix;
            }
        }

        foreach ($attachments as $attachment) {
            if (null !== $request->file($attachment)) {
                $file = $request->file($attachment);
                $fileName = get_file_name($file);
                $orginalName = $file->getClientOriginalName();
                info($orginalName);
                //File uploadeded to S3 bucket
                $filePath = upload_file($request->file($attachment), $fileName, $organizationId, self::$source, $isS3);
                /*$attachmentData[$attachment][0] = [
                    'filename'  => $fileName,
                    'file_path' => Storage::disk('s3')->url($filePath),
                    'extension' => $request->file($attachment)->getClientOriginalExtension()
                ];*/
                //$attachmentData[$attachment] = $orginalName;

                $attachmentData[$attachment] = ($isS3) ? Storage::disk('s3')->url($filePath) : url(Storage::url($filePath));
            }
        }

        info($attachmentData);
        return $attachmentData;
    }


    /**
     *
     * @param unknown $tmsKey
     * @throws Exception
     * @return boolean|unknown
     */
    private static function addTicketTms($ticketData, $tmsKey, $application)
    {
        try {
            $headers = [
                'x-api-key' => $tmsKey,
            ];
            if ($application == config('constants.TICKET_APPLICATION.LQS')) {
                $ticketData = array_merge($ticketData, ['application_id' => $application]);
                $suffixUrl  = config('tms.lqs_create_ticket');
            } else {
                $suffixUrl = config('tms.create_ticket');
            }

            $url = config('tms.ticket_integration_url').$suffixUrl;

            $ticketDetail = TMSAPIFacade::request(TMSAPI::POST, $url, $ticketData, $headers)->getResponse();
            $ticketStatus = $ticketDetail['status'] ?? false;
            if ($ticketStatus == false) {
                return false;
            } else {
                $ticketDetail = $ticketDetail['data'] ?? array();
                return $ticketDetail;
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }


    /**
     * @api {get} /tickets/ticket-details/{ticketId} Get Ticket Details
     * @apiVersion 1.0.0
     * @apiName Get Ticket Details
     * @apiGroup Tickets
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
     * @apiSuccessExample Agent Available:
     *     HTTP/1.1 200 OK
    {
        "message":"Ticket details fetched successfully.",
        "status":true,
        "data": [
        {
            "id": 101,
            "status": "Open",
            "details": {
                "requester_details": {
                    "first_name": "StatusFirst",
                    "last_name": "StatusLast",
                    "email": "status@mailinator.com",
                    "mobile": "6175556985"
                },
                "ticket_details": {
                    "ticket_name": "StatusName",
                    "subject": "StatusSubb",
                    "ticket_context": "Statusontext",
                    "ticket_description": "StatusDesc"
                },
                "other": {
                    "file": ""
                }
            },
            "activity": {
                "2019-04-08": {
                    "12:07:57": {
                        "time": "12:07",
                        "execute_time": "",
                        "name": "Distributed by Self(cto)"
                    }
                }
            }
        }
    ]
}
     *
     * @apiError Failed in fetching ticket details
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *       "error": "Failed in fetching ticket details"
     *     }
     *
     */
    public function getTicketDetails($ticketId)
    {
        try {
            $loggedInUser = \Illuminate\Support\Facades\Auth::user();
            $tmsKey = $loggedInUser->organization->tms_unique_key;
            $ticketDetails = TicketField::getTicketDetails($ticketId, $tmsKey);
            if ($ticketDetails == false) {
                return $this->failResponse(default_trans($loggedInUser->organization_id.'/ticket_enquire.fail_messages.ticket_details_failed', __('default/ticket_enquire.fail_messages.ticket_details_failed')), array(), Response::HTTP_BAD_REQUEST);
            } else {
                return $this->successResponse(__('message.ticket_details_success'), $ticketDetails);
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * @api {get} /tickets/lead-details/{leadId} Get Lead Details
     * @apiVersion 1.0.0
     * @apiName Get Lead Details
     * @apiGroup Tickets
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
     * @apiSuccessExample Agent Available:
     *     HTTP/1.1 200 OK
        {
            "message":"Lead details fetched successfully.",
            "status":true,
            "data": [
                {
                    "id": 1063,
                    "status": "Fresh Lead",
                    "details": {
                        "contact-detail": {
                            "mobile": "9090909090",
                            "alternate_no": "919090909090"
                        },
                        "professional-detail": {
                            "company_name": "jsjsj"
                        }
                    },
                    "activity": {
                        "2019-10-03": {
                            "1": {
                                "time": "06:29 AM",
                                "execute_time": "",
                                "name": " Created through Livechat",
                                "remarks": []
                            }
                        }
                    }
                }
            ]
        }
     * @apiError Failed in fetching lead details
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *       "error": "Failed in fetching lead details"
     *     }
     *
     */
    public function getLeadDetails($leadId)
    {
        try {
            $loggedInUser = \Illuminate\Support\Facades\Auth::user();
            $tmsKey = $loggedInUser->organization->tms_unique_key;
            $leadDetails = TicketField::getLeadDetails($leadId, $tmsKey);
            if ($leadDetails == false) {
                return $this->failResponse(default_trans($loggedInUser->organization_id.'/lead_enquire.fail_messages.lead_details_failed', __('default/lead_enquire.fail_messages.lead_details_failed')), array(), Response::HTTP_BAD_REQUEST);
            } else {
                return $this->successResponse(__('message.lead_details_success'), $leadDetails);
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
}
