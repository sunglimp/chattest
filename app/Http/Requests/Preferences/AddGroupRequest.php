<?php

namespace App\Http\Requests\Preferences;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Session;

class AddGroupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('groups.create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
          'organization_id' => 'required|exists:organizations,id',
          'name' => ['required', 'string','max:20', Rule::unique('groups', 'name')->where(function ($query) {
              $query->where('organization_id', $this->input('organization_id'));
          })],



        ];
    }
    
    public function messages()
    {
        return [
            'name.required' => default_trans(Session::get('userOrganizationId').'/permission.validation_messages.name_required', __('default/permission.validation_messages.name_required')),
            'name.unique'  =>default_trans(Session::get('userOrganizationId').'/permission.validation_messages.name_unique', __('default/permission.validation_messages.name_unique')),
        ];
    }
    public function attributes()
    {
        return ['name'=>'Group Name'];
    }
}
