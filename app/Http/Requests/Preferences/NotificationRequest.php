<?php

namespace App\Http\Requests\Preferences;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Session;

class NotificationRequest extends FormRequest
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
            'notificationEvents' => 'required',
            'organizationId'     => 'required'
        ];
    }
    
    public function messages()
    {
        return [
            'notificationEvents.required' => default_trans(Session::get('userOrganizationId').'/permission.validation_messages.notificationEvents_required', __('default/permission.validation_messages.notificationEvents_required'))
        ];
    }
}
