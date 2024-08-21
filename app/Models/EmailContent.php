<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Doctrine\DBAL\Query\QueryBuilder;

class EmailContent extends Model
{
    protected $dateFormat = 'U';
    public $timestamps = true;
    protected $guarded = []; 
    protected $dates = ['created_at'];
    
    /**
     * Function to add email data.
     * 
     * @param array $emailData
     * @throws \Exception
     */
    public static function addEmailContent($emailData, $chatChannel, $fileData)
    {
        try {
            DB::transaction(function () use($emailData, $chatChannel, $fileData) {
                $emailContent = self::create([
                    'subject' => $emailData['subject'],
                    'body'    => $emailData['body'],
                    'chat_channel_id' => $chatChannel->id,
                    'agent_id'  => $chatChannel->agent_id,
                    'organization_id' => $chatChannel->agent->organization_id ?? null,
                ]);
                
                EmailRecipient::addRecipients($emailData, $emailContent);
                
                if (!empty($fileData)) {
                    EmailAttachment::addAttachments($fileData, $emailContent);
                }
            });
        } catch(\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }
    
    /**
     * Function to get Send emails.
     * 
     */
    public static function getSentEmail($loggedInUserId)
    {
        try {
            $query = self::emailJoinCondition($loggedInUserId);
            return $query->latest()
                        ->paginate(config('config.SENT_ITEMS_PAGE_LENGTH'));
        } catch(\Exception $exception) {
            echo $exception->getMessage();die;
            throw $exception;
        }
    }
    
    /**
     * Function to get EmailBy id.
     * 
     * @param integer $emailId
     * @throws \Exception
     */
    public static function getEmail($emailId, $user)
    {
        try {
            $email = self::select('subject', 'body', 'recipient_type', 
                'attachment_path',
                'attachment_size',
                'attachment_unit',
                'attachment_file_name',
                'email_attachments.id as attachmentId',
                DB::raw('GROUP_CONCAT( DISTINCT email_address) as recipient'),
                DB::raw('(created_at) as sent_date'))
               ->leftjoin('email_attachments', 'email_attachments.email_content_id', '=', 'email_contents.id')
                ->join('email_recipients', 'email_recipients.email_content_id', '=', 'email_contents.id')
                ->where('email_contents.id', $emailId)
                ->groupBy('recipient_type', 'email_attachments.id')
                ->get();
               return self::formatEmailData($email, $user);
        } catch(\Exception $exception) {
            throw $exception;
        }
    }
    
    /**
     * Function to format email data.
     * 
     * @param string $email
     * @throws \Exception
     */
    private static function formatEmailData($email, $user)
    {
        try {
            $colorCodes = config('config.COLOR_CODES');
            $email = $email->toArray();
          
            $res['attachments'] = array();
            foreach ($email as $data) {
                foreach($data as $key=>$val) {
                    if (!in_array($key, ['recipient_type', 'recipient'])) {
                        $res[$key] = $val;
                    } else if ($key == 'recipient_type') {
                        $parts = explode(",", $data['recipient']);
                        $data['recipient'] = implode(', ', $parts);
                        $res[$val] = $data['recipient'];
                    } 
                    if ($key == 'recipient_type' && $val == 'to') {
                        $res['initails'] = strtoupper(trim(substr($res[$val], 0, 2), " "));
                        $res['color_code'] = $colorCodes[substr($res['initails'], 0, 1)];
                    } 
                    if ($key == 'attachment_path' && !empty($val)) {
                        if(array_search($val, array_column($res['attachments'], 'attachment_path')) === false) {
                            $extension = strrpos($data['attachment_file_name'], ".");
                            $extension = substr($data['attachment_file_name'], $extension+1);
                            
                            foreach (config('config.FILE_CLASS') as $key => $fileClass) {
                                if (in_array(strtolower($extension), $fileClass)) {
                                    $class = $key;
                                }
                            }
                            
                            $res['attachments'][] = array (
                                'attachment_path' => $val,
                                'attachment_size' => $data['attachment_size'],
                                'attachment_unit' => $data['attachment_unit'],
                                'attachment_file_name' => $data['attachment_file_name'],
                                'attachment_id' => $data['attachmentId'],
                                'attachment_class' => $class ?? 'far fa-file fileicon'
                             );
                            
                        }
                    }
                    if ($key == 'sent_date') {
                        $res['sent_date'] = \Carbon\Carbon::createFromTimestamp($val, $user->timezone)->format('h:i a, M d,Y');
                    }
                }
                
            }
           
            return $res;
        } catch(\Exception $exception) {
            throw $exception;
        }
    }
    
    /**
     * Function to create query to fetch email data for particular user.
     * 
     * @param integer $loggedInUserId
     * @return QueryBuilder
     */
    private static function emailJoinCondition($loggedInUserId)
    {
        $query = self::select('email_contents.id', 'subject', 'email_contents.body', 
                            DB::raw('GROUP_CONCAT(email_address ORDER BY email_address ASC) as senders'),
                            DB::raw("created_at as sent_date"),
                                    'attachment_size',
                            DB::raw('UCASE(SUBSTRING(GROUP_CONCAT(email_address ORDER BY email_address ASC),1,2)) as initials'))
                     ->join('email_recipients', function ($query) {
                            $query->on('email_recipients.email_content_id', '=', 'email_contents.id')
                                  ->where('email_recipients.recipient_type', '=', 'to');
                            })
                     ->leftjoin('email_attachments', 'email_attachments.email_content_id', '=', 'email_contents.id')
                     ->where('agent_id', $loggedInUserId)
                     ->groupBy('email_contents.id');
        return $query;
    }
    
    
    /**
     * Function to filter email.
     * 
     * @param string $filterParameter
     * @throws \Exception
     */
    public static function filterEmail($filterParameter, $loggedInUserId, $keyword)
    {
        try {
            $query = self::emailJoinCondition($loggedInUserId);
            if(!empty($keyword)) {
                $query->where(function($q) use($keyword){
                    $q->where('subject', 'LIKE', $keyword.'%')
                    ->orWhere('body', 'LIKE', $keyword.'%')
                    ->orWhere('email_address', 'LIKE', $keyword.'%');
                });
            }
            if(!empty($filterParameter)) {
              if ($filterParameter == 'attachment') {
                     $query->where('email_attachments.id', '<>', NULL);
                }
            }
            return $query->latest()
            ->paginate(config('config.SENT_ITEMS_PAGE_LENGTH'));
        } catch(\Exception $exception) {
            throw $exception;
        }
    }
    
    
    /**
     * Function to get email sent by agents.
     * 
     * @param string $now
     * @param array $agents
     * @throws Exception
     */
    public static function getCountEmailSent($now, $agents=[])
    {
        try {
            self::select('users.organization_id', 'email_contents.agent_id', DB::raw('COUNT(email_contents.id) as count_email_sent'))
            ->join('users', 'users.id', '=', 'email_contents.agent_id')
            ->where(\DB::raw('DATE(FROM_UNIXTIME(email_contents.created_at))'), $now)
            ->whereIn('email_contents.agent_id', $agents)
            ->groupBy('users.organization_id', 'email_contents.agent_id')
            ->get()
            ->summarize($now, 'count_email_sent');
        } catch(\Exception $exception) {
            throw $exception;
        }
    }
}