<?php

namespace App\Http\Requests\Tag;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;

class TagRequest extends FormRequest
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
        $loggedInUserId = Auth::id();
        $orgId = Input::get('organizationId');
        return [
            'name' => 'required|max:'.config('config.TAG_MAX_LENGTH').'|regex:'.config('constants.SPACE_NOT_ALLOWED_CONFIG').'|unique:tags,name,NULL,id,organization_id,'.$orgId.',is_admin_tag,1,deleted_at,NULL',
        ];
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \Illuminate\Foundation\Http\FormRequest::messages()
     */
    public function messages()
    {
        return [
            'name.required' => default_trans(Session::get('userOrganizationId').'/permission.validation_messages.tag_name_required', __('default/permission.validation_messages.tag_name_required')),
            'name.regex' =>  default_trans(Session::get('userOrganizationId').'/permission.validation_messages.tag_spaces_disallowed', __('default/permission.validation_messages.tag_spaces_disallowed')),
            'name.unique' => default_trans(Session::get('userOrganizationId').'/permission.validation_messages.tag_name_unique', __('default/permission.validation_messages.tag_name_unique')),
            'name.max' => default_trans(Session::get('userOrganizationId').'/permission.validation_messages.tag_max_length', __('default/permission.validation_messages.tag_max_length')),
        ];
    }
}
;
