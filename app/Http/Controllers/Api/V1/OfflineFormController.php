<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\OfflineForm;
use App\Http\Controllers\BaseController;
use App\Models\PermissionSetting;
use App\Models\Organization;
use App\Jobs\SendChatEmail;
use App\Models\OfflineRequesterDetail;
use App\Facades\TMSAPIFacade;
use App\Libraries\TMSAPI;
use App\Models\TicketField;
use Illuminate\Support\Facades\Log;
use App\Http\Utilities\CommonHelper;

class OfflineFormController extends BaseController
{

    /**
     * @api {post} /offlineForm Offline Form
     * @apiVersion 1.0.0
     * @apiName Offline Form
     * @apiGroup Surbo Api
     *
     * @apiParam {Integer} group_id To identify the chat group and organization.
     * @apiParam {String} Identifier To identify the user.
     * @apiParam {String} client_query Client query when no agent is online.
     * @apiParam {Integer} offline_requester_detail_id  Id gets in channel api when all agents offline

     *
     * @apiHeader {String} Authorization Live chat Integeration key
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
     * @apiSuccessExample Offline form submitted :
     *     HTTP/1.1 200 OK
     *     {
     *          "message":"Offline query submitted successfully.",
     *          "status":true,
     *          "data":[]
     *
     *     }
     *
     *
     */
    public function store(Request $request)
    {
        try {
            $identifier               = $request->input('identifier')?? 'Guest';
            $groupId                  = $request->input('group_id');
            $query                    = $request->input('client_query')??'';
            $offlineRequesterDetailId = $request->input('offline_requester_detail_id');
            $organizationId           = Group::getOrganizationIdByGroup($groupId);
            $data                     = ['organization_id'=> $organizationId,
                'identifier'                  => $identifier,
                'client_query'                => $query,
                'offline_requester_detail_id' => $offlineRequesterDetailId
            ];

            //$offlineForm              = OfflineForm::create($data);
            $offlineForm              = OfflineForm::updateOrCreate(['offline_requester_detail_id' => $offlineRequesterDetailId], $data);
            if ($offlineForm) {
                // send email
                $mailData           = PermissionSetting::where('organization_id', $organizationId)->where('permission_id', config('constants.PERMISSION.OFFLINE-FORM'))->select('settings->email as mail_data','settings->thank_you_message as thank_you_message')->first();
                //get the organization mail settings
                $jsonDecodeMailData = json_decode($mailData->mail_data, true);
                if ($jsonDecodeMailData != null && count($jsonDecodeMailData) > 0 && isset($jsonDecodeMailData['sendEmailOnQC']) && $jsonDecodeMailData['sendEmailOnQC']=='true') {
                    $organizationDetail               = Organization::find($organizationId);
                    $organizationEmail                = $organizationDetail->email;
                    $organizationTimezone             = $organizationDetail->timezone;
                    $offlineRequesterDetail           = OfflineRequesterDetail::getOfflineQueryById($offlineRequesterDetailId);
                    // Checking if offline_requester_detail_id exist
                    if ($offlineRequesterDetail) {
                        $mailDataArray                    = [];
                        $mailDataArray['recipient']['to'] = $jsonDecodeMailData['emailId'];
                        $mailDataArray['recipient']['cc'] = [];
                        $mailDataArray['recipient']['bcc']= [];
                        $mailDataArray['subject']         = $jsonDecodeMailData['subject'];
                        $mailBody                         = nl2br($jsonDecodeMailData['emailBody']);
                        $formattedEmailData               = formatOfflineQueryEmailData($offlineRequesterDetail,$organizationTimezone);
                        $qcTable                          = view('chat.email.query-capture', compact('formattedEmailData', 'organizationId'))->render();
                        $mailBody                         = $mailBody."<br><br>".$qcTable;
                        $appendQueryInBody                = $mailBody . "<br>";
                        $mailDataArray['body']            = $appendQueryInBody;
                        
                        if ($jsonDecodeMailData['botTranscript'] == true) {
                            $transcript             = $offlineRequesterDetail;
                            $formatedTranscript     = self::formatTranscript($transcript);
                            $appendTranscriptInBody = $appendQueryInBody . "<b>Transcript:</b><br> " . $formatedTranscript;
                            $mailDataArray['body']  = $appendTranscriptInBody;
                        }

                        $fileData          = array();
                        $permissionData    = PermissionSetting::getPermissionSettingData($organizationId, config('constants.PERMISSION.EMAIL'));
                        $settings          = (isset($permissionData) && verifyEmailSetting(json_decode($permissionData->settings,true))) ? json_decode($permissionData->settings,true) : getApplicationEmailSetting();
                        if (!empty($settings)) {
                            $config   = ['host'     => $settings['host'],
                                'username' => $settings['username'],
                                'password' => $settings['password'],
                                'port'     => $settings['port'],
                                'encryption'=> $settings['encryption']?? null,
                                'from_email'=> $settings['from_email']?? null,
                            ];
                            Log::debug("OfflineFormController::Email Dispatch in the Queue.");
                            //send mail using queue
                            SendChatEmail::dispatch($mailDataArray, $organizationEmail, $fileData = [], $config);
                        } else {
                            Log::debug("OfflineFormController::Email setting is missing..!''.");
                        }
                    } else {
                        Log::debug("OfflineFormController::offline_requester_detail_id not found::".$offlineRequesterDetailId);
                    }

                }
                // LQS to update lead status for alba cars
                    if ($organizationId == config('tms.alba_car_organization_id')) {
                        $lqsKey = TicketField::getTmsKeyByOrganization($organizationId);
                        $headers    = [
                            'x-api-key' => $lqsKey->tms_unique_key,
                        ];
                        $format_query = CommonHelper::formatOfflineQuery($query, $identifier);

                        $leadData['channel'] = config('tms.channel');
                        $arrayData = ['mobile'=>$identifier,'offline_query'=>$format_query];
                        $leadData['data'] = $arrayData;
                        $leadData['application_id'] = config('constants.TICKET_APPLICATION.LQS');

                        $suffixUrl  = config('tms.lqs_update_lead');
                        $url = config('tms.ticket_integration_url') . $suffixUrl;

                        $response = TMSAPIFacade::request(TMSAPI::POST, $url, $leadData, $headers)->getResponse();

                        // info response from the surbo ace for lead update
                        Log::info("API:OfflineFormController::store==>Response for API request " . json_encode($response));
                    }
                // Greet message to End User when the Offline query is submitted
                $greet_msg = json_decode($mailData->thank_you_message) ?? 'Thank you for submitting your query. All our agents are offline at the moment, we will revert back to you once online.';
                return $this->successResponse($greet_msg);
            }
        } catch (\Exception $exception) {
            return $this->exceptionRespnse($exception);
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

    public static function formatTranscript($transcript){

      $jsonDecode = json_decode($transcript, true);
      $clientInfo = json_decode($jsonDecode['client_info'],true);
      $identifier = $clientInfo['identifier'] ??  'Customer';
      $jsonDecodeTranscript = json_decode($jsonDecode['transcript'],true);
      $transcriptBody = '';
      foreach($jsonDecodeTranscript as $trans){
        if($trans['recipient'] == 'BOT'){

            $transcriptBody .= "BOT: ".$trans['text']."<br>";
        }
        if($trans['recipient'] == 'VISITOR'){

            $transcriptBody .= $identifier.": ".$trans['text']."<br>";
        }

      }
      return $transcriptBody;

    }

}
