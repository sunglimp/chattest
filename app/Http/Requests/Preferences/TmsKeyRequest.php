<?php

namespace App\Http\Requests\Preferences;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class TmsKeyRequest extends FormRequest
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
    public function rules(Request $request)
    {
            return [
            'tms_key' => 'required|unique:organizations,tms_unique_key,'.$request->get('organization_id'),
            ];
    }
    
    public function messages()
    {
        return [
            'tms_key.required' => default_trans(Session::get('userOrganizationId').'/permission.validation_messages.tms_key_required',__('message.msg_tms_key_required')),
        ];
    }
}
