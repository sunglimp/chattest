<?php

namespace App\Http\Controllers;


use Illuminate\Support\Facades\Gate;

class BaseController extends Controller
{
    /**
     * Function to handle exception responses.
     *
     * @param \Exception $exception
     * @return \Illuminate\Http\JsonResponse
     */
    public function exceptionRespnse(\Exception $exception)
    {
        $exceptionResponse = array(
            'status' => false,
            'message' => __('message.something_went_wrong')
        );
        log_exception($exception);
        return response()->json($exceptionResponse);
    }
    
    /**
     * Function to handle success responses.
     *
     * @param string $successMessage
     * @return \Illuminate\Http\JsonResponse
     */
    public function successResponse($successMessage, $data = array())
    {
        $successResponse = array(
            'message' => $successMessage,
            'status'  => true,
            'data' => $data
        );
        
        return response()->json($successResponse);
    }
    
    /**
     * Function to handle fail responses.
     *
     * @param string $failMessage
     * @return \Illuminate\Http\JsonResponse
     */
    public function failResponse($failMessage, $data = array(), $httpCode = '')
    {
        $failResponse = array(
            'message' => $failMessage,
            'status'  => false,
            'data' => $data
        );
        if(!empty($httpCode)) {
            return response()->json($failResponse, $httpCode);
        } else {
            return response()->json($failResponse);
        }
    }
    
    /**
    *
    * FUnction to show angular view.
    *
    * @return \Illuminate\Http\Response
    */
    public function getAngularView($route = 'chat')
    {
        $user = auth()->user();
        $angularAssets = $this->readScriptAndStyleFiles();
        //JS variables need on chat page can be passed here
        $jsVar         = [
            'id'             => $user->id,
            'groupId'        => '',
            'display_name'   => $user->name,
            'online_status'  => $user->online_status,
            'orgId'          => $user->organization_id,
            'route'          => $route,
            'broadcast_port' => config('broadcasting.port'),
            'api_token'      => $user->api_token,
            'is_super_admin' => Gate::allows('superadmin'),
            'is_ticket'      => ($route == 'ticket')
        ];
        
        return view('chat.index', ['jsVar' => $jsVar])
        ->withAngularAssets($angularAssets)
        ->withTitle(array_key_exists($route, $this->titles) ? $this->titles[$route] : 'Chat');
    }
    
    /**
     * 
     * @param string $dir
     * @return string
     */
    private function readScriptAndStyleFiles($dir = 'app')
    {
        $links = '';
        if (is_dir($dir)) {
            $files = glob(public_path($dir) . "/*.{js,css}", GLOB_BRACE);
            foreach ($files as $key => $value) {
                $ext   = pathinfo($value)['extension'];
                $value = \URL::asset($dir . '/' . pathinfo($value)['basename']);
                $links .= ($ext == 'js') ? "<script src='" . $value . "'></script>\n" : "<link rel='stylesheet' href='" . $value . "'>\n";
            }
        }
        return $links;
    }
}
