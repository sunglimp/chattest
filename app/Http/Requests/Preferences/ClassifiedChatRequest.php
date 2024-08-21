<?php

namespace App\Http\Requests\Preferences;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Session;

class ClassifiedChatRequest extends FormRequest
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
        return [
            //
            'classified_token'=>'required | regex:/^[a-zA-Z0-9_\-]*$/ |max:100'
        ];
    }
    public function messages()
    {
        return [
            'classified_token.required' => default_trans(Session::get('userOrganizationId').'/permission.validation_messages.classified_token_required', __('permission.validation_messages.classified_token_required')),
            'classified_token.regex'=>default_trans(Session::get('userOrganizationId').'/permission.validation_messages.classified_token_regex', __('permission.validation_messages.classified_token_regex')),
        ];
    }
}
