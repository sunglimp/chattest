<?php

namespace App\Http\Requests\APIRequest\CannedResponse;

use App\Http\Requests\APIRequest\CustomFormRequest;
use Illuminate\Support\Facades\Session;
use Auth;

class EditCannedResponseRequest extends CustomFormRequest
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
        return  [
            'shortcut' => 'required|max:'.config('config.CANNED_RESPONSE_SHORTCUT_MAX'),
            'response' => 'required',
            'cannedResponseId' => 'required'
        ];
    }
    
    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'shortcut.required' => default_trans(Session::get('userOrganizationId') ?? Auth::user()->organization_id.'/canned_response.validation_messages.shortcut_required', __("default/canned_response.validation_messages.shortcut_required")),
            'response.required' => default_trans(Session::get('userOrganizationId') ?? Auth::user()->organization_id.'/canned_response.validation_messages.response_required', __("default/canned_response.validation_messages.response_required")),
            'response.unique' => default_trans(Session::get('userOrganizationId') ?? Auth::user()->organization_id.'/canned_response.validation_messages.unique_combination', __("default/canned_response.validation_messages.unique_combination")),
            'shortcut.max' => default_trans(Session::get('userOrganizationId') ?? Auth::user()->organization_id.'/canned_response.validation_messages.shortcut_max_length', __("default/canned_response.validation_messages.shortcut_max_length")),
            'cannedResponseId.required' =>default_trans(Session::get('userOrganizationId') ?? Auth::user()->organization_id.'/canned_response.validation_messages.canned_response_id_required', __('default/canned_response.validation_messages.canned_response_id_required'))
            ];
    }
}
