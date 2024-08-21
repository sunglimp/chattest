<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmailContent;
use Illuminate\Support\Facades\Auth;
use App\Models\EmailAttachment;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use App\User;

class SentItemsController extends BaseController
{
    private $firstEmailId = 0;
    
    public function __construct()
    {
        $this->middleware('can:not-admins');
    }
    
    /**
     * Function to get sent emails by loggedin user.
     * 
     */
    public function list()
    {
        try {
            $loggedInUserId = Auth::id();
            $email = EmailContent::getSentEmail($loggedInUserId);
            $user = User::find($loggedInUserId);
            
            $recipients = $this->changeRecipientData($email);

            $totalItems = $email->total();
            $email = $recipients;
           
            return view('sentItems.index', compact('email', 'totalItems'));
        } catch(\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }
    
    /**
     * Function to get email content by id.
     * 
     * @param EmailContent $eamilId
     */
    public function view($emailId)
    {
        try {
            $user = Auth::user();
            $email = EmailContent::getEmail($emailId, $user);
            if (!empty($email)) {
                return $this->successResponse(__('message.email_data_fetched'), $email);
            } else {
                return $this->failResponse(__('message.email_data_fetch_fail'));
            }
        } catch(\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }

    /**
     * Function for search email.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        try {
            $loggedInUserId = Auth::id();
            $requestParams = $request->all();

            $keyword = ! empty($requestParams['keyword']) ? $requestParams['keyword'] : '';
            $filter = ! empty($requestParams['parameter']) ? $requestParams['parameter'] : '';

            $emailSearchData = EmailContent::filterEmail($filter, $loggedInUserId, $keyword);
           
            $totalItems = $emailSearchData->total();
 
          
            $emailSearchData = $this->changeRecipientData($emailSearchData);
         
            $view = view('sentItems.recipient-listing', [
                'email' => $emailSearchData,
                'totalItems' => $totalItems
            ])
            ->render();
            return response()->json([
                'html' => $view,
                'totalItems' => $totalItems,
                'firstEmailId' => $this->firstEmailId
            ]);
        } catch(\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }
    
    /**
     * Function to download attachment.
     * 
     * @param integer $emailId
     */
    public function downloadAttachment($attachmentId)
    {
        try {
            $attachmentData = EmailAttachment::findAttachemnts($attachmentId);
            $attachmentPath = $attachmentData->attachment_path;
            $attachmentFileName = $attachmentData->attachment_file_name;
            return download_attachment($attachmentPath, $attachmentFileName);
        } catch(\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }
    
    /**
     * Function to get recipient listing.
     * 
     * @param Request $request
     * @return View
     */
    public function getRecipients(Request $request)
    {
        try {
            $email = EmailContent::getSentEmail(Auth::id());
            
            $totalItems = $email->total();
            if (empty($email->items())) {
                $status = false;
            } else {
                $status = true;
            }
            $email = $this->changeRecipientData($email);
           
            $view = view('sentItems.recipient-listing', compact('email', 'totalItems'))
            ->render();
            return response()->json([
                'html' => $view,
                'status' => $status
            ]);
        } catch(\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }
    
    private function changeRecipientData($email)
    {
        $colorCodes = config('config.COLOR_CODES');
        $loggedInUserId = Auth::id();
        $user = User::find($loggedInUserId);
        $emailIds =array();
        $recipients = $email->getCollection()->transform(function ($value) use($user, $colorCodes, &$emailIds){
             $value->sent_date = \Carbon\Carbon::createFromTimestamp($value->sent_date, $user->timezone)->format('h:i a');
             $value->body = substr($value->body,0,10).'...';
             $value->color_code = $colorCodes[substr(trim($value->initials, " "), 0, 1)];
             if(empty($emailIds)) {
                 $this->firstEmailId = $value->id;
             } 
             array_push($emailIds, $value->id);
             return $value;
        });
        return $recipients;
    }
}
