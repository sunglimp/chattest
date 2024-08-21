<?php
namespace App\Http\Requests\Preferences;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Session;

class AddBanUserRequest extends FormRequest
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
            'ban_days' => 'required|integer|min:1|max:999',
        ];
    }
    
    public function messages()
    {
        return [
            'ban_days.required' => default_trans(Session::get('userOrganizationId').'/permission.validation_messages.ban_days_required', __('default/permission.validation_messages.ban_days_required')),
            'ban_days.integer'  =>default_trans(Session::get('userOrganizationId').'/permission.validation_messages.ban_days_integer', __('default/permission.validation_messages.ban_days_integer')),
            'ban_days.min' => default_trans(Session::get('userOrganizationId').'/permission.validation_messages.ban_days_min', __('default/permission.validation_messages.ban_days_min')),
            'ban_days.max'  =>default_trans(Session::get('userOrganizationId').'/permission.validation_messages.ban_days_max', __('default/permission.validation_messages.ban_days_max')),
        ];
    }
}
