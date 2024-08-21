<?php

namespace App\Http\Requests\Preferences;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class EmailRequest extends FormRequest
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
            'provider_type'=>'required',
            'username' => 'required',
            'password' => 'required',
            'host' =>     'required',
            'port'   =>   'required|digits_between:1,6',
            'encryption'=> 'nullable|required_unless:provider_type,1|in:'. config('constants.MAIL_ENCRYPTION'),
            'from_email' => 'required|email'
            ];
    }

    public function messages()
    {
        return [
            'provider_type.required' => default_trans(Session::get('userOrganizationId').'/permission.validation_messages.provider_type_required', __('default/permission.validation_messages.provider_type_required')),
            'username.required' => default_trans(Session::get('userOrganizationId').'/permission.validation_messages.username_required', __('default/permission.validation_messages.username_required')),
            'password.required'  =>default_trans(Session::get('userOrganizationId').'/permission.validation_messages.password_required', __('default/permission.validation_messages.password_required')),
            'host.required' => default_trans(Session::get('userOrganizationId').'/permission.validation_messages.host_required', __('default/permission.validation_messages.host_required')),
            'port.required'  =>default_trans(Session::get('userOrganizationId').'/permission.validation_messages.port_required', __('default/permission.validation_messages.port_required')),
            'port.digits_between'  =>default_trans(Session::get('userOrganizationId').'/permission.validation_messages.port_digit_between', __('default/permission.validation_messages.port_digit_between')),
            'encryption.in'  =>default_trans(Session::get('userOrganizationId').'/permission.validation_messages.encryption_in', __('default/permission.validation_messages.encryption_in')),
            'encryption.required_unless'  =>default_trans(Session::get('userOrganizationId').'/permission.validation_messages.encryption_required_unless', __('default/permission.validation_messages.encryption_required_unless')),
                   ];
    }
}
