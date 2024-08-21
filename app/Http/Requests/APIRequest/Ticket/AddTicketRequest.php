<?php

namespace App\Http\Requests\APIRequest\Ticket;

use App\Http\Requests\APIRequest\CustomFormRequest;
use App\Http\Requests\APIRequest\TMSFormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\TicketField;
use function GuzzleHttp\json_decode;
use Illuminate\Support\Facades\Input;

class AddTicketRequest extends CustomFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $loggedInUser = Auth::user();
        $organizationId = $loggedInUser->organization_id;
        $applicationId = Input::get('application');
        $ticketFieldData = TicketField::where('organization_id', $organizationId)->where('application_id',$applicationId)->first();
        $fieldData = $ticketFieldData->fields_data;
        
        $fieldData = json_decode($fieldData, true);

        $rules = array();
        foreach ($fieldData as $data) {
            if ($data['is_mandatory'] == 1) {
                $rules[$data['field_name']] = 'required';
            } else {
                $rules[$data['field_name']] = ['sometimes','nullable'];
            }
        }
        
        $rules1 = [
            'chat_id' => 'required|exists:chat_channels,id',
            //'ticket_data'=>'required',
            'first_name' => 'regex:/^[\pL\s\-]+$/u',
            'last_name'  => 'regex:/^[\pL\s\-]+$/u',
            'email'      => 'email',
            'mobile'     => ['regex:/^([0-9\s\-\+\(\)]*)$/','max:15','min:10'],
            'mobile_no'  => ['regex:/^([0-9\s\-\+\(\)]*)$','max:15','min:10'],
            'alternate_no' => ['regex:/^([0-9\s\-\+\(\)]*)$','max:15','min:10'],
            'phone_no'    => ['regex:/^[6-9]\d{11}$/','max:15','min:10'],
            'phone'     => ['regex:/^[6-9]\d{11}$/','max:15','min:10']
           
        ];
        
        return (array_merge_recursive($rules, $rules1));
    }
    
    public function messages()
    {
        return [
            'first_name.regex' => __('message.msg_tms_first_name_format'),
            'last_name.regex' => __('message.msg_tms_last_name_format'),
            'mobile.regex' => __('message.msg_tms_mobile_format'),
            'mobile.min' => __('message.msg_tms_size_format'),
            'mobile_no.regex' => __('message.msg_tms_mobile_format'),
            'mobile_no.min' => __('message.msg_tms_size_format'),
            'alternate_no.regex' => __('message.msg_tms_mobile_format'),
            'alternate_no.min' => __('message.msg_tms_size_format'),
            'phone_no.regex' => __('message.msg_tms_mobile_format'),
            'phone_no.min' => __('message.msg_tms_size_format'),
            'phone.regex' => __('message.msg_tms_mobile_format'),
            'phone.min' => __('message.msg_tms_size_format'),
            'email.email' => 'Email is not valid'
        ];
    }
    
    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = (new ValidationException($validator))->errors();
        $errorFinal = array();
        foreach ($errors as $key => $val) {
            $errorFinal[$key] = current($val);
        }
        
        throw new HttpResponseException(response()->json([
            'errors' => $errorFinal,
            'status'=>false], 
        JsonResponse::HTTP_UNPROCESSABLE_ENTITY));
    }
}
