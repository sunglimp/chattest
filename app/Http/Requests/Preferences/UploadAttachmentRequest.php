<?php

namespace App\Http\Requests\Preferences;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Session;

class UploadAttachmentRequest extends FormRequest
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
            'size' => 'required|integer|min:1|max:20',
        ];
    }
    
    public function messages()
    {
        return [
            'size.required' => default_trans(Session::get('userOrganizationId').'/permission.validation_messages.size_required', __('default/permission.validation_messages.size_required')),
            'size.integer'  =>default_trans(Session::get('userOrganizationId').'/permission.validation_messages.size_integer', __('default/permission.validation_messages.size_integer')),
            'size.min' => default_trans(Session::get('userOrganizationId').'/permission.validation_messages.size_min', __('default/permission.validation_messages.size_min')),
            'size.max'  =>default_trans(Session::get('userOrganizationId').'/permission.validation_messages.size_max', __('default/permission.validation_messages.size_max')),
        ];
    }
}
