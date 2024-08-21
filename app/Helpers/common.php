<?php

/**
 * Comman Helpers
 */

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\ChatChannel;
use App\Models\PermissionSetting;
use Symfony\Component\Translation\Exception\InvalidArgumentException;
use DatePeriod as Period;
use DateTime as DateTimeAlias;
use DateInterval as Interval;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use App\User;
use Carbon\Carbon;

if (!function_exists('get_js_variables')) {
    function get_js_variables()
    {
        $javaScriptVars = [
            'CANNED_RESPONSE_ADD_SUCCESS' => default_trans((Session::get('userOrganizationId').'/canned_response.success_messages.canned_response_sucess'), __('default/canned_response.success_messages.canned_response_sucess')),
            'SOMETHING_WENT_WRONG' =>  default_trans((Session::get('userOrganizationId').'/canned_response.fail_messages.something_went_wrong'), __('default/canned_response.fail_messages.something_went_wrong')),
            'CANNED_RESPONSE_DELETE_SUCCESS' =>  default_trans((Session::get('userOrganizationId').'/canned_response.success_messages.canned_response_delete_sucess'), __('default/canned_response.success_messages.canned_response_delete_sucess')),
            'CANNED_RESPONSE_EDIT_SUCCESS' => default_trans((Session::get('userOrganizationId').'/canned_response.success_messages.canned_response_edit_sucess'), __('default/canned_response.success_messages.canned_response_edit_sucess')),
            'CANNED_RESPONSE_DELETE_CONFIRM' => default_trans((Session::get('userOrganizationId').'/canned_response.success_messages.canned_response_delete_confirm'), __('default/canned_response.ui_elements_messages.canned_response_delete_confirm')),
            'TAG_DELETE_CONFIRM' => default_trans((Session::get('userOrganizationId').'/chat.tag_delete_confirm.canned_response_sucess'), __('default/chat.ui_elements_messages.tag_delete_confirm')),
            'TAG_MAXLENGTH_REACHED' => default_trans((Session::get('userOrganizationId').'/chat.validation_messages.tag_maxlength_reached'), __('default/chat.validation_messages.tag_maxlength_reached')),
            'SETTING_UPDATED'    => default_trans((Session::get('userOrganizationId').'/permission.validation_messages.settings_updated'), __('default/permission.validation_messages.settings_updated')),
            'CANNED_RESPONSE_YES' => default_trans((Session::get('userOrganizationId').'/canned_response.ui_elements_messages.yes'), __('default/canned_response.ui_elements_messages.yes')),
            'CANNED_RESPONSE_NO' => default_trans((Session::get('userOrganizationId').'/canned_response.ui_elements_messages.no'), __('default/canned_response.ui_elements_messages.no')),
        ];

        $dashbordScriptVars = [
            'chats_in_queue' =>  default_trans((Session::get('userOrganizationId').'/dashboard.ui_elements_messages.chats_in_queue'), __('default/dashboard.ui_elements_messages.chats_in_queue')),
            'left_the_queue' => default_trans((Session::get('userOrganizationId').'/dashboard.ui_elements_messages.left_the_queue'), __('default/dashboard.ui_elements_messages.left_the_queue')),
            'entered_chat' =>  default_trans((Session::get('userOrganizationId').'/dashboard.ui_elements_messages.entered_chat'), __('default/dashboard.ui_elements_messages.entered_chat')),
            'queued_visitor' =>  default_trans((Session::get('userOrganizationId').'/dashboard.ui_elements_messages.queued_visitor'), __('default/dashboard.ui_elements_messages.queued_visitor')),
            'by_agent' =>  default_trans((Session::get('userOrganizationId').'/dashboard.ui_elements_messages.by_agent'), __('default/dashboard.ui_elements_messages.by_agent')),
            'by_visitor' => default_trans((Session::get('userOrganizationId').'/dashboard.ui_elements_messages.by_visitor'), __('default/dashboard.ui_elements_messages.by_visitor')),
            'chat_report' =>  default_trans((Session::get('userOrganizationId').'/dashboard.ui_elements_messages.chat_report'), __('default/dashboard.ui_elements_messages.chat_report')),
            'no_of_chats' =>  default_trans((Session::get('userOrganizationId').'/dashboard.ui_elements_messages.no_of_chats'), __('default/dashboard.ui_elements_messages.no_of_chats')),
            'chat_termination' => default_trans((Session::get('userOrganizationId').'/dashboard.ui_elements_messages.chat_termination'), __('default/dashboard.ui_elements_messages.chat_termination'))

        ];

        $organizationScriptVars = [
            'no_file_selected' => 'No file seleted to upload/import.',
            'added_successfully' => 'Added successfully',
            'update_successfully' => 'Update successfully',
            'status_changed' => 'Status Changed',
            'something_wrong' => 'Something wrong',
            'delete_successfully' => 'Delete successfully',
            'no_data_available' => 'No data available',
            'no_matching_records_found' => 'No matching records found',
            'first_page' => 'First',
            'previous_page' => 'Previous',
            'next_page' => 'Next',
            'last_page' => 'Last'
        ];

        $permissionScriptVars = [
            'successfully_updated' =>  default_trans((Session::get('userOrganizationId').'/permission.success_messages.msg_success_updated'), __('default/permission.success_messages.msg_success_updated')),
            'setting_updated' => default_trans((Session::get('userOrganizationId').'/permission.success_messages.setting_updated'), __('default/permission.success_messages.setting_updated'))
        ];

        $userScriptVars = [
            'admin_already_exist' => default_trans((Session::get('userOrganizationId').'/user_list.validation_messages.admin_exist'), __('default/user_list.validation_messages.admin_exist')),
            'no_seats_exceeded' => default_trans((Session::get('userOrganizationId').'/user_list.validation_messages.org_seat_limit'), __('default/user_list.validation_messages.org_seat_limit')),
            'extra_permission_found' => default_trans((Session::get('userOrganizationId').'/user_list.validation_messages.extra_permission_found'), __('default/user_list.validation_messages.extra_permission_found')),
            'no_data_available' => default_trans((Session::get('userOrganizationId').'/user_list.validation_messages.no_data_available'), __('default/user_list.validation_messages.no_data_available')),
            'no_matching_records_found' => default_trans((Session::get('userOrganizationId').'/user_list.validation_messages.no_matching_records_found'), __('default/user_list.validation_messages.no_matching_records_found')),
            'first_page' => default_trans((Session::get('userOrganizationId').'/user_list.ui_elements_messages.first_page'), __('default/user_list.ui_elements_messages.first_page')),
            'previous_page' => default_trans((Session::get('userOrganizationId').'/user_list.ui_elements_messages.previous_page'), __('default/user_list.ui_elements_messages.previous_page')),
            'next_page' => default_trans((Session::get('userOrganizationId').'/user_list.ui_elements_messages.next_page'), __('default/user_list.ui_elements_messages.next_page')),
            'last_page' => default_trans((Session::get('userOrganizationId').'/user_list.ui_elements_messages.last_page'), __('default/user_list.ui_elements_messages.last_page'))
        ];

        $historyScriptVars = [
            'no_data_available' => default_trans((Session::get('userOrganizationId').'/user_logging.validation_messages.no_data_available'), __('default/user_logging.validation_messages.no_data_available')),
            'no_matching_records_found' => default_trans((Session::get('userOrganizationId').'/user_logging.validation_messages.no_matching_records_found'), __('default/user_logging.validation_messages.no_matching_records_found')),
            'first_page' => default_trans((Session::get('userOrganizationId').'/user_logging.ui_elements_messages.first_page'), __('default/user_logging.ui_elements_messages.first_page')),
            'previous_page' => default_trans((Session::get('userOrganizationId').'/user_logging.ui_elements_messages.previous_page'), __('default/user_logging.ui_elements_messages.previous_page')),
            'next_page' => default_trans((Session::get('userOrganizationId').'/user_logging.ui_elements_messages.next_page'), __('default/user_logging.ui_elements_messages.next_page')),
            'last_page' => default_trans((Session::get('userOrganizationId').'/user_logging.ui_elements_messages.last_page'), __('default/user_logging.ui_elements_messages.last_page'))
        ];

        $cannedScriptVars = [
            'no_data_available' => default_trans((Session::get('userOrganizationId').'/canned_response.validation_messages.no_data_available'), __('default/canned_response.validation_messages.no_data_available')),
            'no_matching_records_found' => default_trans((Session::get('userOrganizationId').'/canned_response.validation_messages.no_matching_records_found'), __('default/canned_response.validation_messages.no_matching_records_found')),
            'first_page' => default_trans((Session::get('userOrganizationId').'/canned_response.ui_elements_messages.first_page'), __('default/canned_response.ui_elements_messages.first_page')),
            'previous_page' => default_trans((Session::get('userOrganizationId').'/canned_response.ui_elements_messages.previous_page'), __('default/canned_response.ui_elements_messages.previous_page')),
            'next_page' => default_trans((Session::get('userOrganizationId').'/canned_response.ui_elements_messages.next_page'), __('default/canned_response.ui_elements_messages.next_page')),
            'last_page' => default_trans((Session::get('userOrganizationId').'/canned_response.ui_elements_messages.last_page'), __('default/canned_response.ui_elements_messages.last_page')),
            'edit' => default_trans((Session::get('userOrganizationId').'/canned_response.ui_elements_messages.edit'), __('default/canned_response.ui_elements_messages.edit')),
            'delete' => default_trans((Session::get('userOrganizationId').'/canned_response.ui_elements_messages.delete'), __('default/canned_response.ui_elements_messages.delete'))

        ];

        $offlineQueries = [
            'no_data_available' => default_trans((Session::get('userOrganizationId').'/offline_queries.validation_messages.no_data_available'), __('default/offline_queries.validation_messages.no_data_available')),
            'no_matching_records_found' => default_trans((Session::get('userOrganizationId').'/offline_queries.validation_messages.no_matching_records_found'), __('default/offline_queries.validation_messages.no_matching_records_found')),
            'first_page' => default_trans((Session::get('userOrganizationId').'/offline_queries.ui_elements_messages.first_page'), __('default/offline_queries.ui_elements_messages.first_page')),
            'previous_page' => default_trans((Session::get('userOrganizationId').'/offline_queries.ui_elements_messages.previous_page'), __('default/offline_queries.ui_elements_messages.previous_page')),
            'next_page' => default_trans((Session::get('userOrganizationId').'/offline_queries.ui_elements_messages.next_page'), __('default/offline_queries.ui_elements_messages.next_page')),
            'last_page' => default_trans((Session::get('userOrganizationId').'/offline_queries.ui_elements_messages.last_page'), __('default/offline_queries.ui_elements_messages.last_page'))
        ];

        JavaScript::put([
            'messages' => $javaScriptVars,
            'dashbord_js_var' => $dashbordScriptVars,
            'organization_js_var' => $organizationScriptVars,
            'permission_js_var' => $permissionScriptVars,
            'user_js_var' => $userScriptVars,
            'history_js_var' => $historyScriptVars,
            'canned_js_var' => $cannedScriptVars,
            'offline_queries_js_var' => $offlineQueries
        ]);
    }
}

if (!function_exists('upload_file')) {

    /**
     * Function to upload file.
     *
     * @param UploadedFile $file
     * @param string $fileName
     * @param  $fileData
     * @param string $fileSource
     * @param bool $isS3
     * @throws Exception
     * @return $filePath
     */
    function upload_file($file, $fileName, $fileData = null, $fileSource, $isS3=false)
    {
        try {
            if (!empty($fileData) && $fileSource == 'email') {
                $orgId  = $fileData;
                $folder = str_replace('__ORG_ID__', $orgId, config('config.attachment_folder'));
            } elseif (!empty($fileData) && $fileSource == 'chat') {
                $orgId = $fileData;
                $folder = str_replace('__ORG_ID__', $orgId, config('config.chat_attachment_folder'));
            } elseif (!empty($fileData) && $fileSource == 'ticket') {
                $orgId = $fileData;
                $folder = str_replace('__ORG_ID__', $orgId, config('tms.ticket_attachment_folder'));
            }

            if ($isS3) {
                $folder = config('config.attachment_root_folder').$folder;
                $file = Storage::disk('s3')->put($folder, $file);

                info("=================S3 URL FOR THE FILE UPLOAD======================");
                info($file);
                info("=================END======================");
            } else {
                Storage::putFileAs($folder, $file, $fileName);
            }
            $filePath = $folder . DIRECTORY_SEPARATOR . $fileName;
            return $filePath;
        } catch (Exception $exception) {
            throw $exception;
        }
    }
}


if (!function_exists('log_exception')) {

    /**
     * Function to log excpetions.
     *
     * @param Exception $exception
     */
    function log_exception(Exception $exception)
    {
        Log::error($exception->getMessage() . "------" . $exception->getFile() . "--------" . $exception->getLine());
    }
}

/**
 * Function check fiule size.
 *
 * @param integer $chatChannelId
 * @param UploadedFile $value
 * @throws \Exception
 * @return boolean
 */
function check_file_size($chatChannelId, $value)
{
    try {
        $chatChannel = ChatChannel::find($chatChannelId);

        $fileSize = $value->getSize()* config('config.FILE_CONVERSION.FACTOR');
        $organizationId = $chatChannel->agent->organization_id;
        $fileSetting = PermissionSetting::getPermissionSettingData($organizationId, config('constants.PERMISSION.SEND-ATTACHMENT'));

        $fileSetting = json_decode($fileSetting->settings)->size;
        if ($fileSetting < $fileSize) {
            return false;
        } else {
            return true;
        }
    } catch (\Exception $exception) {
        throw $exception;
    }
}

/**
 * Function to get file name.
 *
 * @param UploadedFile $file
 * @param integer $organizationId
 * @param integer $chatId
 * @throws \Exception
 * @return string formatted filename
 *
 */
function get_file_name($file, $organizationId = 0, $chatId = 0)
{
    try {
        $fileName = $file->getClientOriginalName();
        $fileName = substr($fileName, 0, strrpos($fileName, '.'));
        $fileExtension = $file->getClientOriginalExtension();
        $fileName = md5($fileName.time().uniqid(rand())). '.'.$fileExtension;
        return $fileName;
    } catch (\Exception $exception) {
        throw $exception;
    }
}

/**
 * Convert seconds to hrs:min:seconds
 *
 * @param integer $secondsData
 * @return string
 */
function convert_average_time($secondsData, $collonNotation = false, $seconds = false)
{
    $hoursDiff = floor($secondsData / 3600);
    $minutesDiff = ($secondsData / 60) % 60;
    $secondsDiff = $secondsData % 60 ;

    $hoursDiff = formatTime($hoursDiff);
    $minutesDiff = formatTime($minutesDiff);
    $secondsDiff = formatTime($secondsDiff);

    if ($collonNotation === true) {
        if ($seconds === true) {
            return $hoursDiff.":"." ".$minutesDiff.":"." ".$secondsDiff;
        }
        return $hoursDiff.":"." ".$minutesDiff;
    } else {
        return $hoursDiff."hrs"." ".$minutesDiff."m"." ".$secondsDiff."s";
    }
}


if (!function_exists('is_valid_date')) {
    function is_valid_date($str)
    {
        $timeStampDate = strtotime($str);
        if (!$timeStampDate) {
            throw new InvalidArgumentException();
        }
    }
}

/**
 * Function to download attachment.
 *
 * @param string $attachmentPath
 * @param string $attachmentFileName
 *
 */
function download_attachment($attachmentPath, $attachmentFileName)
{
    return Storage::download($attachmentPath, $attachmentFileName);
}

/**
 * Function to get dates between two dates.
 *
 */
function get_dates_between_range($startDate, $endDate, $format = 0)
{
    if ($format === 0) {
        $format = config('settings.mysql_date_format');
    }


    $period = new Period(
        new DateTimeAlias($startDate),
        new Interval('P1D'),
        new DateTimeAlias(date('Y-m-d', strtotime($endDate . "+1 days")))
    );
    $categories = array();
    foreach ($period as $value) {
        array_push($categories, $value->format($format));
    }
    return $categories;
}

/**
 * Function to format time. if time is single digit prepend with 0.
 *
 * @param integer $timeDiff
 * @return string
 */
function formatTime($timeDiff)
{
    if ($timeDiff != 0) {
        if (strlen($timeDiff) == 1) {
            $timeDiff = '0'.$timeDiff;
        }
    } else {
        $timeDiff = '00';
    }
    return $timeDiff;
}

/**
 * Function to calculate file size unit.
 *
 * @param UploadedFile $file
 */
function calculateFileSizeUnit($file)
{
    $fileSize = $file->getSize()/1000;//converting in KB

    if (intval($fileSize/1000) > 0) {//checking filesize is in MB
        $fileSize = round($fileSize/1000, 2);
        $fileUnit = config('config.MB_FILE_SIZE_UNIT');
    } else { //checking filesize is in KB
        $fileSize = round($fileSize, 2);
        $fileUnit = config('config.KB_FILE_SIZE_UNIT');
    }
    return array(
        'size' => $fileSize,
        'unit' => $fileUnit
    );
}

/**
 * Function to set default message for label.
 *
 * @param string $id path to messgage file
 * @param string $fallback fallback message
 * @param array $parameters
 * @param string $domain
 * @param string $locale
 *
 * @return string message
 */
function default_trans($id, $fallback, $parameters = [], $domain = 'messages', $locale = null)
{
    return ($id === ($translation = __($id, $parameters)) || empty(__($id, $parameters))) ? $fallback : $translation;
}

if (!function_exists('mask')) {
    function mask($identifier, $maskingSymbol='*') {
        $identifierLength  = strlen($identifier);
        $identifier        = str_split($identifier);
        switch ($identifierLength)
        {
          case (1<=$identifierLength) && ($identifierLength<=2):
                  array_walk($identifier,function(&$val, $key) use ($identifierLength,$maskingSymbol) {
                     $val =  $key == ($identifierLength-1) ? $maskingSymbol : $val;
                  });
                  break;
          case (3<=$identifierLength) && ($identifierLength<=4):
                  array_walk($identifier,function(&$val, $key) use ($identifierLength,$maskingSymbol) {
                     $val =  ($key == ($identifierLength-1) || ($key==0)) ? $val : $maskingSymbol;
                  });
                  break;
          default:
                  array_walk($identifier,function(&$val, $key) use ($identifierLength,$maskingSymbol) {
                     $val =  ($key==0 || $key==1 || $key == ($identifierLength-1) || $key == ($identifierLength-2)) ? $val : $maskingSymbol;
                  });
                  break;
        }
        $identifier = implode('',$identifier);
        return $identifier;
    }
}

if (!function_exists('checkIndentifierMaskPermission')) {
    function checkIndentifierMaskPermission($agentId) {
        $user = User::find($agentId);
        if($user->checkPermissionBySlug('identifier_masking')) {
            return true;
        }
        return false;
    }
}

if (!function_exists('get_start_end_timestamps')) {
    function get_start_end_timestamps($now) {
        $startTimeStamp = Carbon::parse($now)->timestamp;
        $endTimeStamp = Carbon::parse($now)->addDay()->timestamp;
        return [$startTimeStamp, $endTimeStamp];
    }
}

if (!function_exists('hide_service_credential')) {
    function hide_service_credential($string, int $hide = 0) {
        return $hide ? str_repeat('*', strlen($string)) : $string;
    }
}

if (!function_exists('verifyEmailSetting')) {
    function verifyEmailSetting($settings = array()) {
        if (!empty($settings['host'])
            && !empty($settings['username'])
            && !empty($settings['password'])
            && !empty($settings['port'])
            && !empty($settings['from_email']))
        {
           return true;
        }
        return false;
    }
}

if (!function_exists('getApplicationEmailSetting')) {
    function getApplicationEmailSetting() {
        return [
            'host' => env('MAIL_HOST'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'port' => env('MAIL_PORT'),
            'encryption' => env('MAIL_ENCRYPTION'),
            'from_email' => env('MAIL_FROM_ADDRESS'),
        ];
    }
}
if (!function_exists('checkOrganizationChatLabel')) {
    function checkOrganizationChatLabel($agentId) {
        $user = User::find($agentId);
        $setting = $user->getPermissionSetting('customer_information');
        return isset($setting['whatsapp']) ? $setting['whatsapp']['client_display_attribute'] : config('constants.CUSTOMER_CHAT_LABEL.WHATSAPP.NUMBER');
    }
}

if (!function_exists('client_display_name')) {
    function client_display_name($permissionLabel, $mask=false, $identifier, $name=null) {
        $display_name = '';
        switch ($permissionLabel) {

            case config('constants.CUSTOMER_CHAT_LABEL.WHATSAPP.NUMBER'):
                $display_name = $mask ? mask($identifier) : $identifier;
                break;

            case config('constants.CUSTOMER_CHAT_LABEL.WHATSAPP.NAME'):
                if (empty($name)) {
                    $display_name = $mask ? mask($identifier) : $identifier;
                } else {
                    $display_name = $mask ? mask($name) : $name;
                }
                break;

            case config('constants.CUSTOMER_CHAT_LABEL.WHATSAPP.NUMBER_NAME'):
                $display_name = $mask ? mask($identifier) : $identifier;
                if (!empty($name)) {
                    if ($mask) {
                        $display_name = $identifier!=$name ? mask($identifier).'||'.mask($name) : mask($identifier);
                    }else {
                        $display_name = $identifier!=$name ? $identifier.'||'.$name : $identifier;
                    }
                }
                break;

            default:
                $display_name = $mask ? mask($identifier) : $identifier;
                break;
        }

        return $display_name;
    }
}


