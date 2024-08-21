<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use App\Jobs\SendChatEmail;
use App\Models\EmailContent;
use App\Http\Requests\APIRequest\EmailRequest;
use App\Models\ChatChannel;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use App\Models\Permission;
use App\Models\PermissionSetting;
use Illuminate\Support\Facades\Mail;
use App\Mail\ChatEmailMail;

class EmailController extends BaseController
{

     /**
     * @api {post} /email/send Send Email
     * @apiVersion 1.0.0
     * @apiName Send Email
     * @apiGroup Email
     *
     * @apiParam {Integer} group_id Chat is intended to transfer to this group
     * @apiParam {Object[]} request Request required to send email.
     * @apiParam {File} File uploaded as attachment with email
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
     * @apiSuccessExample Email Sent:
     *     HTTP/1.1 200 OK
     *     {
     *          "message":"Mail sent successfully",
     *          "status":true,
     *          "data": []
     *     }
     * @apiError subjectRequired Email Subject is required
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 422 Unproccesable Entity
     *     {
     *       "errors": "Email Subject is required"
     *     }
     *
     */
    /**
     * Function to send email.
     *
     * @param Request $request
     */

    public function send(EmailRequest $request)
    {
        try {
            $requestParams = $request->all();

            $jsonRequest = json_decode($requestParams['request'], true);
            $file = '';
            $fileData = array();
            $chatChannel = ChatChannel::find($jsonRequest['chatChannelId']);

            $isAllowed = $this->checkEmailPermissions($chatChannel, config('constants.PERMISSION.EMAIL'));

            if ($isAllowed === true) {
                if(!empty($requestParams['file'])) {
                    $file = $requestParams['file'];
                    $fileData = $this->uploadMultipleFiles($chatChannel, $file);
                }

                $agentEmail = $chatChannel->agent->email;
                $organizationId = $chatChannel->agent->organization_id;

                //get the organization mail settings
                $permissionData = PermissionSetting::getPermissionSettingData($organizationId,config('constants.PERMISSION.EMAIL'));
                $settings = json_decode($permissionData->settings,true);
                $config = [ 'host'=> $settings['host'],
                            'username'=>$settings['username'],
                            'password'=>$settings['password'],
                            'port'=> $settings['port'],
                            'encryption'=> $settings['encryption']?? null,
                            'from_email'=> $settings['from_email']?? null,
                          ];

                if (!verifyEmailSetting($config)) {
                    return $this->failResponse('Email setting is missing..!');
                }


                //save data in db
                EmailContent::addEmailContent($jsonRequest, $chatChannel, $fileData);
                //send mail using queue
                SendChatEmail::dispatch($jsonRequest, $agentEmail, $fileData, $config);
                return $this->successResponse('Mail sent successfully');
            } else {
                return $this->failResponse(__('message.access_not_allowed'));
            }

        } catch(\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }

    /**
     * Function to check email send permission allowed.
     *
     * @param ChatChannel $chatChannel
     * @throws \Exception
     */
    private function checkEmailPermissions($chatChannel, $permissionId)
    {
        try {
            $agent = $chatChannel->agent;
            $permission = Permission::find($permissionId);
            return $agent->can('check', $permission);
        } catch(\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Function to upload multiple files.
     *
     * @param ChatChannel $chatChannel
     * @throws \Exception
     */
    private function uploadMultipleFiles(ChatChannel $chatChannel, array $files)
    {
        try {

            $organizationId = $chatChannel->agent->organization_id;
            $chatId = $chatChannel->id;
            $fileData = [];
            foreach ($files as $file) {
                $fileName = get_file_name($file, $organizationId, $chatId);
                $fileSizeUnit = calculateFileSizeUnit($file);
                $fileData[] = array (
                    'attachment_file_name' => $file->getClientOriginalName(),
                    'attachment_path'       => upload_file($file, $fileName, $organizationId, 'email'),
                    'attachment_size'       => $fileSizeUnit['size'],
                    'attachment_unit'      =>  $fileSizeUnit['unit']
                );
            }

            return $fileData;
        } catch(\Exception $exception) {
            throw $exception;
        }
    }
}