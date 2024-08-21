<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Doctrine\DBAL\Query\QueryBuilder;

class CannedResponse extends Model
{
    use SoftDeletes;
    protected $table = 'canned_responses';
    
    
    protected $dateFormat = 'U';
    public $timestamps = true;
    const cannedResponsePageLimit = 10;
    const isAdminResponse = 1;
    
    protected $dates = ['deleted_at'];
    protected $fillable = ['shortcut', 'response', 'is_admin_response', 'user_id', 'organization_id'];

    /**
     * Function to get canned response of particular agentId.
     *
     * @param integer $agentId
     * @return Collection canned responses for particular agent
     */
    public static function getCannedResponse($userToken, $isApi = true, $search = '')
    {
        try {
            if ($isApi === false) {
                return self::getCannedResponseForTable($userToken, $search);
            } else {
                return self::getApiCannedResponse($userToken);
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
    
    /**
     * Function to add canned responses.
     *
     * @throws \Exception
     */
    public static function add($requestParams)
    {
        try {
            $shortcut = $requestParams['shortcut'] ?? '';
            self::formatShortcut($shortcut);
            $userId = Auth::id();
            $isAdmin = Gate::allows(['superadmin']) || Gate::allows(['admin']);
            $organizationId = Auth::user()->organization_id ?? null;
            
            $isInserted = CannedResponse::create([
                'shortcut' => $shortcut,
                'response' => $requestParams['response'],
                'user_id' => $userId,
                'organization_id' => $organizationId,
                'is_admin_response' =>$isAdmin
            ]);
            
            return $isInserted;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
    
    /**
     * Function to deleted canned response.
     *
     * @param array $requestParams
     * @throws \Exception
     */
    public static function deleteCannedResponse($encryptCannedResponseId, $loggedInUserId)
    {
        try {
            $cannedResponseId = decrypt($encryptCannedResponseId);
            $cannedResponse = self::find($cannedResponseId);
            $canEdit = self::checkEditCannedResponse($cannedResponse, $loggedInUserId);
            if ($canEdit === true) {
                return CannedResponse::where('id', '=', $cannedResponseId)->delete($cannedResponseId);
            } else {
                return false;
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Function to get canned response for table.
     *
     * @param string $userToken
     *
     * @throws \Exception
     */
    private static function getCannedResponseForTable($userToken, $search)
    {
        try {
            $query1 = CannedResponse::select('shortcut', 'response', 'canned_responses.created_at as created_date', "canned_responses.id", DB::raw("IF(canned_responses.user_id = $userToken,1,0) as can_update"));
            self::joinConditionCannedResponse($query1, $userToken, false, $search);
            $cannedResponse = $query1->orderBy('canned_responses.created_at', 'DESC')->paginate(self::cannedResponsePageLimit);
            self::formatCannedResponseData($cannedResponse);
            return $cannedResponse;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Function for join conditions to show canned response.
     *
     * @param
     *            $query1
     * @param
     *            $userToken
     */
    private static function joinConditionCannedResponse(&$query1, $userToken, $isAPI, $search='')
    {
        try {
            $organizationId = User::find($userToken)->organization_id;
            $query1->join('users', 'users.id', '=', 'canned_responses.user_id');
                if($search != ''){
                 $query1->Where(function ($q) use ($search) {
                    $q->where('shortcut', 'LIKE', "%$search%")
                    ->orWhere('response', 'LIKE', "%$search%")
                    ->orWhere(DB::raw("date_format(FROM_UNIXTIME(canned_responses.created_at),'%b %d, %Y')"), 'LIKE' , "%$search%");
                });
        }    
                $query1->Where(function ($nestedQuery) use ($userToken, $organizationId) {
                $nestedQuery->where('canned_responses.user_id', '=', $userToken)   
                ->orWhere('users.role_id', config('constants.user.role.super_admin'))         
                ->orWhere(function ($nestedQuery) use ($organizationId) {
                    $nestedQuery->where('is_admin_response', '=', self::isAdminResponse)
                    ->where('canned_responses.organization_id', '=', $organizationId);
                });
                });
                
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
    
    /**
     * Function to get Canned response for API.
     *
     * @param string $userToken
     */
    private static function getApiCannedResponse($userToken)
    {
        try {
            $query1 = CannedResponse::select('shortcut', 'response');
            self::joinConditionCannedResponse($query1, $userToken, true);
            $cannedResponse = $query1->get();
            return self::formatAPICannedResponse($cannedResponse);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
    
    /**
     * Function to encrypt canned response ids.
     *
     * @param Collection $cannedResponse
     */
    private static function formatCannedResponseData(&$cannedResponse)
    {
        $cannedResponse->map(function ($response, $key) {
            $response->cannedResponseId = encrypt($response->id);
            $response->id = 0;
            $response->created_date = \Carbon\Carbon::createFromTimestamp($response->created_date, Auth::user()->timezone)->format('M d, Y');
        });
    }
    
    /**
     * Function to get Canned response by id.
     *
     * @param array $requestParams
     */
    public static function getCannedResponseById($encryptCannedResponseId)
    {
        try {
            $cannedResponseId = decrypt($encryptCannedResponseId);
            $cannedResponse = CannedResponse::select(\DB::raw('TRIM(LEADING "#" FROM shortcut) as shortcut'), 'response')
                                            ->where('id', '=', $cannedResponseId)
                                            ->first();
            return $cannedResponse;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
    
    /**
     * Function to edit canned response.
     *
     * @param array $requestParams
     * @throws \Exception
     */
    public static function edit($requestParams, $loggedInUserId)
    {
        try {
            $cannedResponseId = $requestParams['cannedResponseId'] ?? 0;
            $cannedResponseId = decrypt($cannedResponseId);
            $cannedResponse = CannedResponse::find($cannedResponseId);
            
            $canEdit = self::checkEditCannedResponse($cannedResponse, $loggedInUserId);
            if ($canEdit === true) {
                $response = $requestParams['response'] ?? '';
                $shortcut = $requestParams['shortcut'] ?? '';
                self::formatShortcut($shortcut);
                
                
                $cannedResponse->response = $response;
                $cannedResponse->shortcut = $shortcut;
                return $cannedResponse->save();
            } else {
                return false;
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
    
    /**
     * Function to format shortcut.
     *
     * @param string $shortcut
     */
    private static function formatShortcut(&$shortcut)
    {
        $shortcut = ltrim($shortcut, '#');
        $shortcut = '#'.$shortcut;
    }
    
    /**
     * Function to check uniqueness for canned response.
     *
     * @param string $shortcut
     * @param string $response
     * @param integer $loggedInUserId
     * @param number $cannedResponseId
     * @throws \Exception
     * @return boolean
     */
    public static function checkUnique($shortcut, $response, $loggedInUserId, $cannedResponseId = 0)
    {
        try {
            $trimShortcut = ltrim($shortcut , '#');
            $shortcut = '#'.$trimShortcut;
            $user = Auth::user();
            $organizationId = $user->organization_id;
           
            $query = self::uniqueResponseCheck($shortcut, $response)
                          ->where('user_id', $loggedInUserId)
                          ->editResponseCheck($cannedResponseId);
            
            $dupilcateCheck = $query->first();
            if (!empty($dupilcateCheck)) {
                return false;
            } else {
                if (Gate::allows('superadmin')) {
                    self::superAdminDuplicationCheck($shortcut, $response, $cannedResponseId);
                } elseif (Gate::allows('admin')) {
                    return self::adminDuplicationCheck($shortcut, $response, $organizationId, $cannedResponseId);
                } elseif (Gate::allows('not-admins')) {
                    return self::agentDuplicationCheck($shortcut, $response, $organizationId, $cannedResponseId);
                }
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
    
    /**
     * Function to check whether edit canned response is allowed or not,
     *
     * @param CannedResponse $cannedResponse
     * @param integer $loggedInUserId
     * @throws \Exception
     * @return boolean
     */
    private static function checkEditCannedResponse($cannedResponse, $loggedInUserId)
    {
        try {
            $user = User::find($loggedInUserId);
            
            if ($user->can('update', $cannedResponse)) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
    
    /**
     * Function to format API canned response.
     *
     * @param Collection $cannedResponse
     * @throws \Exception
     */
    private static function formatAPICannedResponse($cannedResponse)
    {
        try {
            $cannedResponse = $cannedResponse->toArray();
            $result = array();
            $finalResult = array();
            $duplicateShortcuts = array();
            foreach ($cannedResponse as $key => $response) {
                if (!in_array($response['shortcut'], $duplicateShortcuts)) {
                    $duplicateShortcuts[] = $response['shortcut'];
                    $result[$response['shortcut']][] = $response['response'];
                } else {
                    $result[$response['shortcut']][] = $response['response'];
                }
            }
            
            foreach ($result as $key => $val) {
                $finalResult[][$key] = $val;
            }
            return $finalResult;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
    
    /**
     * Function to delete canned response.
     *
     * @param integer $cannedResponseId
     * @throws \Exception
     */
    private static function deleteResponse($cannedResponseId)
    {
        try {
            CannedResponse::where('id', '=', $cannedResponseId)->delete();
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
    
    /**
     * ScopeQuery for uniqueness check of canned response.
     *
     * @param QueryBuilder $query
     * @param string $shortcut
     * @param string $response
     * @return QueryBuilder
     */
    public function scopeUniqueResponseCheck($query, $shortcut, $response)
    {
        return $query->where('shortcut', 'LIKE', "$shortcut")
                     ->where('response', 'LIKE', "$response");
    }
    
    /**
     * DFuplication check for super admin.
     *
     * @param string $shortcut
     * @param string $response
     * @param integer $cannedResponseId
     */
    private static function superAdminDuplicationCheck($shortcut, $response, $cannedResponseId)
    {
            //take ownership if any same canned response exist
            $dupilcateCheck = self::uniqueResponseCheck($shortcut, $response)
                                    ->editResponseCheck($cannedResponseId)
                                    ->get();
            
            $dupilcateCheck->map(function ($value) {
                self::deleteResponse($value->id);
            });
    }
    
    /**
     * Duplication check for admin.
     *
     * @param string $shortcut
     * @param string $response
     * @param integer $organizationId
     * @param integer $cannedResponseId
     * @return boolean
     */
    private static function adminDuplicationCheck($shortcut, $response, $organizationId, $cannedResponseId)
    {
        //error if same canned response added by super admin
        $dupilcateCheck = self::uniqueResponseCheck($shortcut, $response)
        ->where('user_id', config('constants.user.role.super_admin'))
        ->editResponseCheck($cannedResponseId)
        ->first();
     
        if (!empty($dupilcateCheck)) {
            return false;
        } else {
            //take oiwner ship if same canned response added by agent
            $dupilcateCheck = self::uniqueResponseCheck($shortcut, $response)
            ->where('user_id', '<>', config('constants.user.role.super_admin'))
            ->where('organization_id', $organizationId)
            ->editResponseCheck($cannedResponseId)
            ->get();
            $dupilcateCheck->map(function ($value) {
                self::deleteResponse($value->id);
            });
        }
    }
    
    /**
     * Duplication check for agent.
     *
     * @param string $shortcut
     * @param string $response
     * @param integer $organizationId
     * @param integer $cannedResponseId
     * @return boolean
     */
    private static function agentDuplicationCheck($shortcut, $response, $organizationId, $cannedResponseId)
    {
        //error if same canned response added by admin/superadmin
        $duplicateCheck = self::uniqueResponseCheck($shortcut, $response)
        ->where('user_id', config('constants.user.role.super_admin'))
        ->orWhere(function ($query) use ($organizationId) {
            $query->where('organization_id', $organizationId)
            ->where('user_id', config('constants.uer.role.admin'));
        })
        ->editResponseCheck($cannedResponseId)
        ->first();
        if (!empty($duplicateCheck)) {
            return false;
        }
    }
    
    /**
     * Query Scope in case for edit canned response.
     *
     * @param QueryBuilder $query
     * @param integer $cannedResponseId
     * @return QueryBuilder
     */
    public function scopeEditResponseCheck($query, $cannedResponseId)
    {
        if ($cannedResponseId !== 0) {
            $cannedResponseId = decrypt($cannedResponseId);
            return $query->where('id', '<>', $cannedResponseId);
        }
    }
}
