<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\BaseController;
use App\Http\Resources\GroupCollection;
use App\Http\Resources\UserCollection;
use App\Models\Group;
use App\Models\Organization;
use App\Repositories\GroupRepository;
use App\Repositories\OrganizationRepository;
use App\User;
use Illuminate\Http\Request;

class GroupController extends BaseController
{

    protected $organization;
    protected $group;

    public function __construct(Organization $organization, Group $group)
    {
        $this->organization = new OrganizationRepository($organization);
        $this->group        = new GroupRepository($group);
    }


    /**
     * @api {get} /groups  Fetch Group
     * @apiVersion 1.0.0
     * @apiName Fetch Group
     * @apiGroup Surbo Api
     *
     * @apiSuccess {Boolean} status Status of the request.
     * @apiSuccess {Object[]} data Response data payload
     * @apiSuccess {Integer} data.id Group Id
     * @apiSuccess {String} data.name Group Name
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
     *
     * @apiSuccessExample Groups Available:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": [
     *               {
     *                   "id": 22,
     *                   "name": "default"

     *               },
     *               {
     *                   "id": 21,
     *                   "name": "test"

     *               },
     *
     *           ],
     *      "status":true
     *
     *       }
     *
     * @apiError     Access Token Not found
     *
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "status": false,
     *        "message":"Unauthorized"
     *     }
     */

    public function withHash(Request $request)
    {
        if ($organization = $this->organization->findBySurboUniqueKey($request->get('access_token'))) {
            $collection = $this->group->fetchByOrganizationId($organization->id);
            return (new GroupCollection($collection))->additional(['status' => true]);
        } else {
            return $this->failResponse('Organization not found');
        }
    }


     /**
     * @api {get} /groups/organizations/{organizationId} Get Organization Groups
     * @apiVersion 1.0.0
     * @apiName Get Groups By Organization
     * @apiGroup Group
     *
     * @apiSuccess {Boolean} status Status of the request.
     * @apiSuccess {Object[]} data Response data payload
     * @apiSuccess {Integer} data.id Group Id
     * @apiSuccess {String} data.name Group name
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
     * @apiSuccessExample Groups Available:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": [{
     *                   "id": 1,
     *                   "name": "Default"
     *               }, {
     *                   "id": 22,
     *                   "name": "group1"
     *               }, {
     *                   "id": 23,
     *                    "name": "group2"
     *               }, {
     *                   "id": 24,
     *                   "name": "group3"
     *               }
     *           ],
     *          "status": true
     *       }
     *
     */

    public function index(Request $request, $organizationId)
    {

        if ($this->organization->findById($organizationId)) {
            $collection = $this->group->fetchByOrganizationId($organizationId, true)->filterOnlineAgent(Auth()->id());
            return (new GroupCollection($collection))->additional(['status' => true]);
        } else {
            return $this->failResponse('Organization not found');
        }
    }

     /**
     * @api {get} /groups/{groupId}/agents Get Agent Groups
     * @apiVersion 1.0.0
     * @apiName Get Groups By Agent
     * @apiGroup Group
     *
     * @apiSuccess {String} status Status of the request i.e online.
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
     * @apiSuccessExample Agents Available:
     *     HTTP/1.1 200 OK
     *     {
	"data": [{
		"id": 15,
		"name": "lmanager",
		"email": "lmanager@vfirst.com",
		"mobile_number": "9752339909",
		"online_status": 1,
		"image": "http:\/\/livechat.local\/images\/user.jpeg"
	}, {
		"id": 17,
		"name": "lassociate",
		"email": "lassociate@gmail.com",
		"mobile_number": "9752339909",
		"online_status": 1,
		"image": "http:\/\/livechat.local\/images\/user.jpeg"
	}],
	"status": true
         }
     *
     */


    public function agents(Request $request, $groupId)
    {

        try {
            $allAgents = User::whereHas('groups', function ($query) use ($groupId) {
                        $query->where('group_id', $groupId);
            });

            if (isset($request->status)) {
                $allAgents->where('online_status', User::STATUS_ONLINE)
                           ->where('user_permission->'.config('constants.PERMISSION.CHAT'),true);
            }
            $data = $allAgents->get();
            return (new UserCollection($data))->additional(['status' => true]);

        } catch (Exception $exception) {
            info($exception->getMessage());
            return $this->failResponse('Agents not found', [], 404);
        }
        $data = $allAgents->get();
        return (new UserCollection($data))->additional(['status' => true]);
    }
    
    /**
     * @api {get} /status/online/group/{id?} Get Online Status
     * @apiVersion 1.0.0
     * @apiName Get Online Status
     * @apiGroup Surbo Api
     *
     * @apiSuccess {String} status Status of the request i.e online.
     *
     *
     * @apiHeader {String} Authorization Live chat Integeration key
     * @apiHeader {String} Content-Type Content type of the payload
     *
     * @apiParam {Integer} id This is group id which is optional parameter. 
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
     * @apiSuccessExample Agents Online:
     *     HTTP/1.1 200 OK
     * {
     * "message": "Agents Online",
     * "status": true,
     * "data": []
     * }
     *
     *@apiSuccessExample Agents Offline:
     *     HTTP/1.1 200 OK
     * {
     * "message": "Agents Offline",
     * "status": false,
     * "data": []
     * }
     * 

     */
    
    public function onlineStatus(Request $request, $id = '')
    {
        try {
                $organization = $this->organization->findBySurboUniqueKey($request->get('access_token'));
                $status = Group::getOnlineStatus($organization->id, $id);
                if ($status) {
                    return $this->SuccessResponse('Agents Online');
                    }               
                return $this->failResponse('Agents Offline');
        } catch (Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }

}
