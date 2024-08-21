<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\BaseController;
use App\Models\ChatChannel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Jobs\UpdateChatTypeByMlModel;

class MlModelController extends BaseController
{
    //

    public function updateChatStatusType(Request $request)
    {

    }
    public function chageMlTicketStatus(Request $request)
    {
        $rawUpdateData=[];
        $message="";

        if(isset($request->ticket_status)&& ($request->ticket_status==0))
        {
            $rawUpdateData['ticket_status']=$request->ticket_status;
            $message="Ticket Status changed Successfully";


        }
        if(isset($request->ticket_type)&& !empty($request->ticket_type))
        {
            $rawUpdateData['ticket_type']=$request->ticket_type;
            $message="Ticket Type changed Successfully";

        }

        $data= ChatChannel::where('id', $request->channel_id)->update($rawUpdateData);
        if ($data) {
            return  $this->successResponse($message, $data);
        } else {
            return  $this->failResponse('Something is wrong');
        }

    }
}
