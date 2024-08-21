<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Models\ChatMessage;
use App\Http\Resources\ChatMessage as ChatMessageResource;
use App\Models\ChatChannel;
use Illuminate\Http\UploadedFile;
use App\Http\Requests\APIRequest\AttachmentRequest;
use App\Models\Permission;
use App\Models\ChatAttachment;
use App\Http\Requests\Preferences\UploadAttachmentRequest;
use Illuminate\Support\Facades\Storage;

class MessageController extends BaseController
{

    const MESSAGE_TYPE_INTERNAL = 'internal';
    const EVENT_NEW_CHAT = 'new_chat';
    const EVENT_NEW_INTERNAL_COMMENT = 'new_internal_comment';
    const TYPE_INTERNAL_COMMENT = 'internal_comment';


    /**
     * @api {post} /messages Store Message
     * @apiVersion 1.0.0
     * @apiName Store Message
     * @apiGroup Message
     *
     * @apiParam {Integer} agent_id Chat is intended to transfer to this group
     * @apiParam {String} channel_name  Channel Name
     * @apiParam {Integer} chat_channel_id Chat Channel id
     * @apiParam {String} message_type Type Of message ex:"public"
     * @apiParam {String} recipient Recipient Type ex: "AGENT"
     * @apiParam {Json} message Client Information payload
     *
     *
     * @apiParamExample {json} Request-Example:
     *  {
     *          "text":"type message here"
     *  }
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
     * @apiSuccessExample Message Added:
     *     HTTP/1.1 200 OK
     *     {
     *          "message":"Messages added successfully",
     *          "status":true,
     *          "data":[]
     *     }
     *
     * @apiError  Access Token Not found
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "Something is wrong"
     *     }
     *
     */


    /**
     * @api {post} /message Store Message
     * @apiVersion 1.0.0
     * @apiName Store Message
     * @apiGroup Surbo Api
     *
     * @apiParam {Integer} agent_id Chat is intended to transfer to this group
     * @apiParam {String} channel_name  Channel Name
     * @apiParam {Integer} chat_channel_id Chat Channel id
     * @apiParam {String} message_type Type Of message ex:"public"
     * @apiParam {String} recipient Recipient Type ex: "AGENT"
     * @apiParam {Json} message Client Information payload
     *
     *
     * @apiParamExample {json} Request-Example:
     *    {
     *        "channel_name" :"visitor-7057bc75-de8a-466d-807f-1f4d50e67b93",
     *        "chat_channel_id":15,
     *        "message" : {
     *          "text" : "hi"
     *        },
     *        "message_type": "public",
     *        "recipient": "AGENT"
     *     }
     *
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
     * @apiSuccessExample Message Added:
     *     HTTP/1.1 200 OK
     *     {
     *          "message":"Messages added successfully",
     *          "status":true,
     *          "data":[]
     *     }
     *
     * @apiError  Access Token Not found
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "Something is wrong"
     *     }
     *
     */

    public function store(Request $request)
    {
        ChatMessage::saveMessage($request);
        return $this->successResponse(__('message.msg_add_success'));
    }


     /**
     * @api {get} /messages/channels/{channelId}/agents/{userId}  Get Message
     * @apiVersion 1.0.0
     * @apiName Get Message
     * @apiGroup Message
     *
     *
     * @apiHeader {String} Authorization API token
     * @apiHeader {String} Content-Type Content type of the payload
     *
     * @apiParam {Integer} channelId Channel id
     * @apiParam {Integer} userId Agent id
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
     * @apiSuccessExample Chat Messages:
     *     HTTP/1.1 200 OK
     *    {
     *        "data": [
     *            {
     *                "message": {
     *                    "text": "hi"
     *                },
     *                "recipient": "AGENT",
     *                "message_type": "public",
     *                "created_at": {
     *                    "date": "2020-05-19 15:17:14.000000",
     *                    "timezone_type": 3,
     *                    "timezone": "Asia/Kolkata"
     *                },
     *                "read_at": null,
     *                "source_type": "whatsapp",
     *                "agent_display_name": "Associate One"
     *            },
     *            {
     *                "message": {
     *                    "text": "hi"
     *                },
     *                "recipient": "AGENT",
     *                "message_type": "public",
     *                "created_at": {
     *                    "date": "2020-05-19 15:18:19.000000",
     *                    "timezone_type": 3,
     *                    "timezone": "Asia/Kolkata"
     *                },
     *                "read_at": null,
     *                "source_type": "whatsapp",
     *                "agent_display_name": "Associate One"
     *            }
     *        ],
     *        "status": true
     *    }
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "status":404
     *       "error": "Not Found"
     *     }
     */



    public function index($channelId, $userId, $messageOffset = 0)
    {

        date_default_timezone_set(Auth()->user()->timezone);
        $messages = ChatMessage::getMessages($channelId, $messageOffset, $userId);

        return ChatMessageResource::collection($messages)->additional(['status' => true]);
        //@TODO Handle internal agent name using some cache or direct query
    }



     /**
     * @api {get} /messages/history/channels/{channelId}/agents/{userId}  History  Message Api
     * @apiVersion 1.0.0
     * @apiName Get Old Message Message
     * @apiGroup Message
     *
     *
     * @apiHeader {String} Authorization API token
     * @apiHeader {String} Content-Type Content type of the payload
     *
     * @apiParam {Integer} channelId Channel id
     * @apiParam {Integer} userId Agent id
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
     * @apiSuccessExample History Messages:
     *     HTTP/1.1 200 OK
     *    {
    *        "data": [
    *            {
    *                "message": {
    *                    "text": "Please help me regarding a issue"
    *                },
    *                "recipient": "BOT",
    *                "message_type": "BOT",
    *                "created_at": {
    *                    "date": "2020-05-07 22:16:17.000000",
    *                    "timezone_type": 3,
    *                    "timezone": "Asia/Kolkata"
    *                },
    *                "read_at": null,
    *                "source_type": null,
    *                "agent_display_name": "Surbo"
    *            },
    *            {
    *                "message": {
    *                    "text": "Okay, Yes tell me"
    *                },
    *                "recipient": "VISITOR",
    *                "message_type": "BOT",
    *                "created_at": {
    *                    "date": "2020-05-07 22:16:17.000000",
    *                    "timezone_type": 3,
    *                    "timezone": "Asia/Kolkata"
    *                },
    *                "read_at": null,
    *                "source_type": null,
    *                "agent_display_name": "Surbo"
    *            },
    *            {
    *                "message": {
    *                    "text": "How can I start PHP coding"
    *                },
    *                "recipient": "BOT",
    *                "message_type": "BOT",
    *                "created_at": {
    *                    "date": "2020-05-07 22:16:17.000000",
    *                    "timezone_type": 3,
    *                    "timezone": "Asia/Kolkata"
    *                },
    *                "read_at": null,
    *                "source_type": null,
    *                "agent_display_name": "Surbo"
    *            },
    *            {
    *                "message": {
    *                    "text": "okay, sending you to live chat. They will suggest you better"
    *                },
    *                "recipient": "VISITOR",
    *                "message_type": "BOT",
    *                "created_at": {
    *                    "date": "2020-05-07 22:16:17.000000",
    *                    "timezone_type": 3,
    *                    "timezone": "Asia/Kolkata"
    *                },
    *                "read_at": null,
    *                "source_type": null,
    *                "agent_display_name": "Surbo"
    *            },
    *            {
    *                "message": {
    *                    "text": "Please help me regarding a issue"
    *                },
    *                "recipient": "BOT",
    *                "message_type": "BOT",
    *                "created_at": {
    *                    "date": "2020-05-07 22:55:26.000000",
    *                    "timezone_type": 3,
    *                    "timezone": "Asia/Kolkata"
    *                },
    *                "read_at": null,
    *                "source_type": null,
    *                "agent_display_name": "Surbo"
    *            },
    *            {
    *                "message": {
    *                    "text": "Okay, Yes tell me"
    *                },
    *                "recipient": "VISITOR",
    *                "message_type": "BOT",
    *                "created_at": {
    *                    "date": "2020-05-07 22:55:26.000000",
    *                    "timezone_type": 3,
    *                    "timezone": "Asia/Kolkata"
    *                },
    *                "read_at": null,
    *                "source_type": null,
    *                "agent_display_name": "Surbo"
    *            },
    *            {
    *                "message": {
    *                    "text": "How can I start PHP coding"
    *                },
    *                "recipient": "BOT",
    *                "message_type": "BOT",
    *                "created_at": {
    *                    "date": "2020-05-07 22:55:26.000000",
    *                    "timezone_type": 3,
    *                    "timezone": "Asia/Kolkata"
    *                },
    *                "read_at": null,
    *                "source_type": null,
    *                "agent_display_name": "Surbo"
    *            },
    *            {
    *                "message": {
    *                    "text": "okay, sending you to live chat. They will suggest you better"
    *                },
    *                "recipient": "VISITOR",
    *                "message_type": "BOT",
    *                "created_at": {
    *                    "date": "2020-05-07 22:55:26.000000",
    *                    "timezone_type": 3,
    *                    "timezone": "Asia/Kolkata"
    *                },
    *                "read_at": null,
    *                "source_type": null,
    *                "agent_display_name": "Surbo"
    *            },
    *            {
    *                "message": {
    *                    "text": "Please help me regarding a issue",
    *                    "recipient": "BOT"
    *                },
    *                "recipient": "BOT",
    *                "message_type": "BOT",
    *                "created_at": {
    *                    "date": "2020-05-08 11:29:28.000000",
    *                    "timezone_type": 3,
    *                    "timezone": "Asia/Kolkata"
    *                },
    *                "read_at": null,
    *                "source_type": "whatsapp",
    *                "agent_display_name": "Surbo"
    *            },
    *            {
    *                "message": {
    *                    "text": "Okay, Yes tell me",
    *                    "recipient": "VISITOR"
    *                },
    *                "recipient": "VISITOR",
    *                "message_type": "BOT",
    *                "created_at": {
    *                    "date": "2020-05-08 11:29:28.000000",
    *                    "timezone_type": 3,
    *                    "timezone": "Asia/Kolkata"
    *                },
    *                "read_at": null,
    *                "source_type": "whatsapp",
    *                "agent_display_name": "Surbo"
    *            }
    *        ],
    *        "links": {
    *            "first": "http://surbo-chat.test/api/v1/messages/history/channels/12/agents/5?page=1",
    *            "last": "http://surbo-chat.test/api/v1/messages/history/channels/12/agents/5?page=1",
    *            "prev": null,
    *            "next": null
    *        },
    *        "meta": {
    *            "current_page": 1,
    *            "from": 1,
    *            "last_page": 1,
    *            "path": "http://surbo-chat.test/api/v1/messages/history/channels/12/agents/5",
    *            "per_page": 100,
    *            "to": 52,
    *            "total": 52
    *        },
    *        "status": true
    *    }
    */

    public function historyMessages($channelId, $userId)
    {

        date_default_timezone_set(Auth()->user()->timezone);
        $messages = ChatMessage::getHistoryMessages($channelId, $userId);

        return ChatMessageResource::collection($messages)->additional(['status' => true]);
        //@TODO Handle internal agent name using some cache or direct query
    }

    /**
     * Function to upload attachment.
     *
     * @param integer $chatChannelId
     */

     /**
     * @api {post} /messages/attachments Upload Attachment
     * @apiVersion 1.0.0
     * @apiName Upload Attachment
     * @apiGroup Message
     *
     * @apiParam {File} File to be uploaded
     * @apiParam {Integer} chat_channel_id Chat id of that chat file will be uploaded.
     * @apiParam {string} recipient recipient type
     * @apiParam {String} message_type message type
     * @apiParam {String} sender_display_name Sender display name
     * @apiParam {String} channel_name Channel Name
     * @apiParam {String} index
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
     * @apiSuccessExample File Uploaded:
     *     HTTP/1.1 200 OK
     *     {
     *          "message":"Messages added successfully",
     *          "status":true,
     *          "data":{
     *             "hash_name":"2f425903e83d379ba2e6d6ebdc3062ff.m4v",
     *             "chat_channel_id":2,
     *             "index":2
     *          }
     *     }
     *
     * @apiError FileRequired The file field is required
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "The file field is required"
     *     }
     *
     */
    public function uploadAttachment(AttachmentRequest $request)
    {
        try {
            $file = $request->file;
            $chatChannelId = $request->chat_channel_id;
            $index = $request->index;

            $chatChannel = ChatChannel::find($chatChannelId);
            $isAllowed = $this->checkAttachmentPermissions($chatChannel, config('constants.PERMISSION.SEND-ATTACHMENT'));

            if ($isAllowed === true) {
                $organizationId = $chatChannel->agent->organization_id;
                $fileName = get_file_name($file, $organizationId, $chatChannel->id);
                $filePath = upload_file($file, $fileName, $organizationId, 'chat');
                $request = $this->createMessageObject($chatChannel, $fileName, $file, $request, $filePath);
                $chatMessage = ChatMessage::saveMessage($request);

                ChatAttachment::saveData($file, $filePath, $chatMessage);

                $data = array(
                    'hash_name' => $fileName,
                    'chat_channel_id' => $chatChannelId,
                    'index' => $index
                );
                return $this->successResponse(__('message.msg_add_success'), $data);
            } else {
                return $this->failResponse(__('message.access_not_allowed'));
            }
        } catch (\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }

    /**
     * Function to create request object for record attachment message.
     *
     * @param integer $chatChannel
     * @param string $fileName
     * @param UploadedFile $file
     * @param Request $chatRequest
     * @throws Exception
     * @return \Illuminate\Http\JsonResponse
     */
    private function createMessageObject($chatChannel, $fileName, $file, $chatRequest, $filePath)
    {
        try {
            $request = new \Illuminate\Http\Request();
            $filePath = Storage::url($filePath);
            $message = array(
                'chat_channel_id' => $chatChannel->id,
                'message' => array('text' => null, 'file_name'=> $file->getClientOriginalName(), 'extension' => $file->getClientOriginalExtension(), 'size' => $file->getSize()* config('config.FILE_CONVERSION.FACTOR'), 'hash_name' => $fileName, 'path' => $filePath),
                'recipient' => $chatRequest->recipient,
                'message_type' =>  $chatRequest->message_type,
                'channel_name' => $chatChannel->channel_name
            );

            $request->replace($message);
            return $request;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Function to check email send permission allowed.
     *
     * @param ChatChannel $chatChannel
     * @throws \Exception
     */
    private function checkAttachmentPermissions($chatChannel, $permissionId)
    {
        try {
            $agent = $chatChannel->agent;
            $permission = Permission::find($permissionId);
            return $agent->can('check', $permission);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Function to download attachment.
     *
     * @param string $attachmentPath
     * @return \Illuminate\Http\JsonResponse
     */

         /**
     * @api {get} /messages/attachments/download/{fileHash} Download Attachment
     * @apiVersion 1.0.0
     * @apiName Download Attachment
     * @apiGroup Message
     *
     * @apiSuccess {string} Filestream download.
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
     */

    public function downloadAttachment($attachmentPath)
    {
            $attachmentData = ChatAttachment::getAttachmentData($attachmentPath);
            if (!empty($attachmentData)) {
                $attachmentName = $attachmentData->original_name;
                $attachmentPath = $attachmentData->path;
                return download_attachment($attachmentPath, $attachmentName);
            }
    }


      /**
     * @api {post} /attachments Upload Bots Attachments
     * @apiVersion 1.0.0
     * @apiName Upload Bots Attachments
     * @apiGroup Surbo Api
     *
     * @apiParam {File} file File to be uploaded
     * @apiParam {channel_name} Name of chat channel for which file is getting uploaded.

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
     * @apiSuccessExample File uploaded success:
     *     HTTP/1.1 200 OK
     *     {
     *          "message":"Messages added successfully",
     *          "status":true,
     *          "data":{
     *                "url": "http://127.0.0.1/storage/61/chat_attachments/23b938e929b4dd6139eb8de0844c2f53.pdf"
     *          }
     *     }
     *
     * @apiError FIileNotFound Please check required paarmeters are given
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "Please check required paarmeters are given"
     *     }
       *  @apiErrorExample Error-Response:
       *     HTTP/1.1 404 Not Found
       *     {
       *       "message": "Message not added successfully",
       *        "status": false,
       *         "data": []
       *     }
       *
     */


    public function uploadBotAttachments(Request $request)
    {
        try {

            $requestParams = $request->all();
            if (!empty($requestParams['file']) && !empty($requestParams['channel_name'])) {
                $isAttachementUploaded = ChatMessage::uploadBotAttachments($requestParams);
                if ($isAttachementUploaded instanceof ChatAttachment) {
                    $filePath = Storage::url($isAttachementUploaded->path);
                    $url = array(
                        'url' => config('config.APP_URL').$filePath
                    );
                    return $this->successResponse(__('message.msg_add_success'), $url);
                } else {
                    return $this->failResponse(__('message.message_not_added'));
                }
            } else {
                return $this->failResponse(__('message.parameters_check'));
            }

        } catch (\Exception $exception) {
            throw $exception;
        }
    }
}
