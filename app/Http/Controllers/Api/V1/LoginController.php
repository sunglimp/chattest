<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\BaseController;
use App\Http\Requests\APIRequest\UserLoginRequest;
use App\Http\Resources\UserLogin;
use App\User;
use Illuminate\Auth\Events\{Login, Logout};
use Illuminate\Http\Request;

use Illuminate\Support\Facades\{Auth, Redis};
use Jenssegers\Agent\Facades\Agent;
use App\Events\UserOffline;

class LoginController extends BaseController
{
    private $isUserLogin = 0;
    private $isUserActive = 1;
    private $rolesAllowed = [];

    public function __construct() {
        $this->rolesAllowed = [
            config('constants.user.role.manager'),
            config('constants.user.role.team_lead'),
            config('constants.user.role.associate'),
        ];
    }

    /**
     * @api {post} /login Agent Login
     * @apiVersion 1.0.0
     * @apiName Login Agent
     * @apiGroup Agent
     *
     * @apiParam {String} email Agent Email
     * @apiParam {String} password Agent Password
     *
     * @apiHeader {String} Content-Type Content type of the payload
     * @apiHeader {String} [deviceId] deviceId is mandatory in case of mobile
     * @apiHeader {Integer} [deviceType] deviceType is mandatory in case of mobile , Note:- 1=>Andriod, 2=> IOS
     * @apiHeader {String} [deviceToken] deviceToken is mandatory in case of mobile
     *
     * @apiHeaderExample {json} Content-Type:
     *     {
     *         "Content-Type" : "application/json"
     *     }
     * @apiHeaderExample {json} Authorization:
     *     {
     *         "Authorization" : "Bearer  l39I1Pyerw0DhDUTviio"
     *     }
     * @apiHeaderExample {string} deviceId:
     *     {
     *         "deviceId" : "l39I1Pyerw0DhDUTviio"
     *     }
     * @apiHeaderExample {integer} deviceType:
     *     {
     *         "deviceType" : 1
     *     }
     * @apiHeaderExample {string} deviceToken:
     *     {
     *         "deviceToken" : "l39I1Pyerw0DhDUTviio"
     *     }
     * @apiSuccessExample Successfully Login:
     *     HTTP/1.1 200 OK
     *     {
     *        "data": {
     *            "id": 5,
     *            "name": "Associate One",
     *            "email": "associate_one@mailinator.com",
     *            "mobile_number": "9999999999",
     *            "organization_id": 1,
     *            "gender": "male",
     *            "role":5,
     *            "image": "http://surbo-chat.test/storage/user/1588868885.png",
     *            "timezone": "Asia/Kolkata",
     *            "api_token": "jAp0cm4L0gmHWn80yjS0tGjk9ral87OtesaRW9nZNPGCaba215mpORJCKdXU"
     *        },
     *        "status": true,
     *        "message": "Login Successfully"
     *      }
     * @apiSuccessExample Duplicate Login:
     *   HTTP/1.1 200 OK
     *   {
     *       "message": "Credentials do not match our records or account inactive or already logged in or Account validity is expired. Please contact administrator",
     *       "status": false,
     *       "data": []
     *   }
     * @apiErrorExample Error-Response:
     *   HTTP/1.1 422 Unprocessable Entity
     *   {
     *       "errors": [
     *               "Email ID is required.",
     *               "The password field is required."
     *         ],
     *       "status": false
     *   }
     * @apiErrorExample Error-Response-Mobile-Login:
     *   HTTP/1.1 422 Unprocessable Entity
     *   {
     *       "message":"deviceId, deviceType, deviceToken field is required in header."
     *       "status":false,
     *       "data":[]
     *   }
     *
     */
    public function login(UserLoginRequest $request)
    {
        try {
            // Request header validation for Mobile or Tablet
            if((Agent::isMobile() || Agent::isTablet()) && (!$request->hasHeader('deviceId') || !$request->hasHeader('deviceType') || !$request->hasHeader('deviceToken')))
            {
               return $this->failResponse(__('message.msg_device_headers'),[], 422);
            }

            $email     = $request->email;
            $password  = $request->password;
            // User Authentication

            if(Auth::once(['email'=>$email, 'password'=>$password, 'status'=>$this->isUserActive, 'is_login'=> $this->isUserLogin, 'role_id'=>$this->rolesAllowed]))
            {
                info("Login via API");
                info(json_encode($request->header()));
                $user               = Auth()->user();
                $user->device_id    = $request->header('deviceId');
                $user->device_type   = $request->header('deviceType');
                $user->device_token  = $request->header('deviceToken');
                event (new Login(null, $user, false));
                return (new UserLogin($user))->additional(['status'=> true, 'message' => __('message.msg_login')]);
            }
            return $this->failResponse(__('auth.failed'));
        } catch (\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }

    /**
     * @api {post} /agents/logout Agent Logout
     * @apiVersion 1.0.0
     * @apiName Logout Agent
     * @apiGroup Agent
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
     * @apiSuccessExample Successfully Logout:
     *     HTTP/1.1 200 OK
     *     {
     *        "message": "Logout Successfully",
     *        "status": true,
     *        "data": []
     *      }
     * @apiErrorExample Unauthorized access:
     *   HTTP/1.1 401 Unauthorized
     *   {
     *       "messagee":"Unauthorized",
     *       "status": false,
     *       "data":[]
     *   }
     * @apiErrorExample Authorization Token Not Found:
     *   HTTP/1.1 401 Unauthorized
     *   {
     *       "message": "Unauthenticated."
     *   }
     *
     */
    public function logout(Request $request)
    {
        try{
            $token = $request->bearerToken();
            $user  = User::where('api_token',$token)->whereIn('role_id',$this->rolesAllowed)->first();
            if (!$user) {
                return $this->failResponse(__('Unauthorized'),[],401);
            }
            Redis::del("last_activity_" . $user->id);
            $agent = new AgentController();
            $agent->offline($user->id, User::CHECK_CHAT_AVAILABLE);
            $agent->offline($user->id, User::CHECK_CHAT_NOT_AVAILABLE, User::MAKE_CHAT_TERMINATED);
            event(new UserOffline($user->id, User::MAKE_CHAT_TERMINATED));
            event (new Logout(null, $user));
            return $this->successResponse(__('message.msg_logout'));
        } catch (\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }
    
    /**
     * @api {get} /agents/side-bar User Sidebar
     * @apiVersion 1.0.0
     * @apiName User Sidebar 
     * @apiGroup Agent
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
     * @apiSuccessExample Sidebar Data:
     *     HTTP/1.1 200 OK
     *    {
    "message": "Sidebar Data",
    "status": true,
    "data": {
        "18": "Chat",
        "28": "Archive",
        "2": "Canned Response",
        "3": "Dashboard",
        "14": "Sent Emails",
        "20": "Offline Queries",
        "205": "Lead Enquire",
        "21": "Ticket Enquire",
        "22": "Classified Chat",
        "24": "User Logging",
        "30": "Missed Chats"
    }
}
     * @apiErrorExample Authorization Token Not Found:
     *   HTTP/1.1 401 Unauthorized
     *   {
     *       "message": "Unauthenticated."
     *   }
     *
     */
    public function getSidebar(Request $request)
    {
        try{
            $user = Auth::user();
            $organizationId = Auth::user()->organization_id ?? 0;
            $sidebar = User::getSidebar($user, $organizationId);
            return $this->successResponse(__('message.side_bar_data'), $sidebar);
        } catch (\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }
}
