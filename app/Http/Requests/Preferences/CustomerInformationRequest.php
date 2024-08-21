<?php

namespace App\Http\Requests\Preferences;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Session;

class CustomerInformationRequest extends FormRequest
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
            'customerChatInfoLabel' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'customerChatInfoLabel.required' => default_trans(Session::get('userOrganizationId').'/permission.validation_messages.customer_information_label_required', __('default/permission.validation_messages.customer_information_label_required')),
        ];
    }
}
