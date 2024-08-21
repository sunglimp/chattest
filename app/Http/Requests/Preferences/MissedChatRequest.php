<?php

namespace App\Http\Requests\Preferences;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;

class MissedChatRequest extends FormRequest
{
    public function __construct() {
        
    }
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
    public function rules(Request $request)
    {
        return [
            'api' => 'required',
            'template_id' => 'required',
            'bot_id' => 'required',
            'token' => 'required'
        ];
    }
    
    public function messages()
    {
        return [
            'api.required' => default_trans(Session::get('userOrganizationId').'/permission.validation_messages.api_required', __('default/permission.validation_messages.api_required')),
            'template_id.required' => default_trans(Session::get('userOrganizationId').'/permission.validation_messages.template_id_required', __('default/permission.validation_messages.template_id_required')),
            'bot_id.required' => default_trans(Session::get('userOrganizationId').'/permission.validation_messages.bot_id_required', __('default/permission.validation_messages.bot_id_required')),
            'token.required' => default_trans(Session::get('userOrganizationId').'/permission.validation_messages.classified_token_required', __('default/permission.validation_messages.classified_token_required')),
        ];
    }

}
