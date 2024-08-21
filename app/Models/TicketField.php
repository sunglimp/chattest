<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Facades\TMSAPIFacade;
use App\Libraries\TMSAPI;
use Carbon\Carbon;
use Illuminate\Http\Response;

class TicketField extends Model
{
    protected $dateFormat = 'U';
    public $timestamps = true;
    
    protected $table = 'ticket_fields';
    
    protected $fillable = ['application_id', 'application_name', 'organization_id', 'fields_data', 'created_at', 'updated_at'];
    
    public static function getFields($applicationId, $organizationId)
    {
        $fields = TicketField::where('application_id', $applicationId)
                             ->where('organization_id', $organizationId)
                             ->first();
        
        return $fields;
    }
    
    /**
     * Function to update ticket details.
     *
     * @param array $requestParams
     * @throws \Exception
     */
    public static function updateTicketFields($requestParams, $tmsKey)
    {
        try {
            $organization = self::getOrganizationByTmsKey($tmsKey);
            $organizationId = $organization->id;
            $fieldsData = json_encode($requestParams['form_fields']);
          
            $isUpdated = self::where('application_id', $requestParams['application_id'])
                ->where('organization_id', $organizationId)
                ->update(['fields_data' => $fieldsData]);
            
            return $isUpdated;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
    
    /**
     * Function to get organization by tms unique key.
     *
     * @param string $tmsKey
     * @return Organization
     */
    private static function getOrganizationByTmsKey($tmsKey)
    {
        return Organization::where('tms_unique_key', $tmsKey)->first();
    }
    
    /**
     *
     * @param unknown $tmsKey
     * @param unknown $organizationId
     * @throws Exception
     * @return boolean
     */
    public static function saveFieldData($tmsKey, $organizationId)
    {
        try {
            $tmsDetails = self::fetchTMSDetails($tmsKey);
            $tmsArr = $tmsDetails['data'] ?? array();
            $tmsStatus = $tmsDetails['status'] ?? false;
            if ($tmsStatus == false) {
                $tmsErrorCode = $tmsDetails['errorCode'] ?? Response::HTTP_BAD_REQUEST;
                return $tmsErrorCode;
            } else {
                $tmsData = self::formatTicketFieldsData($tmsArr, $organizationId);
                self::saveTicketFieldData($tmsData, $organizationId);
                return true;
            }
        } catch (\Exception $exception) {
            echo $exception->getMessage();
            die;
            throw $exception;
        }
    }
    
    /**
     *
     * @param unknown $tmsKey
     * @throws Exception
     * @return boolean|unknown
     */
    private static function fetchTMSDetails($tmsKey)
    {
        try {
            $url = config('tms.ticket_integration_url').config('tms.fetch_fields');
            $headers = [
                'x-api-key' => $tmsKey
            ];
            $tmsDetail = TMSAPIFacade::request(TMSAPI::GET, $url, array(), $headers)->getResponse();
            return $tmsDetail;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
    
    /**
     *
     * @param unknown $tmsDetails
     * @param unknown $organizationId
     * @return unknown[][]|string[][]|array[][]|number[][]
     */
    private static function formatTicketFieldsData($tmsDetails, $organizationId)
    {
        $tmsData = array();
        foreach ($tmsDetails as $data) {
            $tmsData[] = [
                'application_id' => $data['application_id'] ?? 0,
                'application_name' => $data['application_name'] ?? '',
                'organization_id' => $organizationId,
                'fields_data' => json_encode($data['form_fields']) ?? array(),
                'created_at' => Carbon::now()->timestamp,
                'updated_at' => Carbon::now()->timestamp
            ];
        }
        return $tmsData;
    }
    
    /**
     *
     * @param unknown $tmsData
     * @param unknown $organizationId
     */
    private static function saveTicketFieldData($tmsData, $organizationId)
    {
        $isDataExist = TicketField::where('organization_id', $organizationId)->get();
        if (!$isDataExist->isEmpty()) {
            self::where('organization_id', $organizationId)->delete();
        }
        self::insert($tmsData);
    }
    
    /**
     * Function to get  tms unique key by organization.
     *
     * @param string $tmsKey
     * @return Organization
     */
    public static function getTmsKeyByOrganization($organizationId)
    {
        return Organization::where('id', $organizationId)->first();
    }
    
    /**
     *
     * @param unknown $ticketId
     * @throws Exception
     */
    public static function getTicketDetails(string $ticketId, string $tmsKey)
    {
        try {
            $body = [
                'ticket_id' => $ticketId
            ];
            
            $headers = [
                'x-api-key' => $tmsKey
            ];
            
            $url = config('tms.ticket_integration_url').config('tms.ticket_details').'?ticket_id='.$ticketId;
            $ticketDetails = TMSAPIFacade::request(TMSAPI::GET, $url, $body, $headers)->getResponse();
            if ($ticketDetails['status'] == true) {
                $ticketData = $ticketDetails['data'] ?? array();
                return $ticketData;
            } else {
                return false;
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
    
    /**
     *
     * @param unknown $leadId
     * @throws Exception
     */
    public static function getLeadDetails(string $leadId, string $tmsKey)
    {
        try {
            $body = [
                'lead_id' => $leadId
            ];
            
            $headers = [
                'x-api-key' => $tmsKey
            ];
            
            $url = config('tms.ticket_integration_url').config('tms.lead_details').'?lead_id='.$leadId.'& application_id='.config('constants.TICKET_APPLICATION.LQS');
            $leadDetails = TMSAPIFacade::request(TMSAPI::GET, $url, $body, $headers)->getResponse();
            if ($leadDetails['status'] == true) {
                $leadData = $leadDetails['data'] ?? array();
                return $leadData;
            } else {
                return false;
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
}
