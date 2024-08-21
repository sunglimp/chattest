<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use App\Rules\GroupExist;
use Illuminate\Support\Facades\Session;

class StoreUserRequest extends FormRequest
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
//        dd($request->get('role_id'))
        $rules = [
            'name' => ['required','string','regex:/^[a-zA-Z\'., ]+$/','max:20',new \App\Rules\SeatLimit($request)],
            'mobile_number' => 'required|digits_between:10,15',
            'organization_id' => 'required',
            'gender' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|regex:/(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$/',
            'role_id' => ['required', new \App\Rules\AdminExist($request)],
            'timezone' => 'required|string',
            'report_to' =>  'required_unless:role_id,'.config('constants.user.role.admin'),
            'image' => 'nullable|image',
            'group' => 'required_unless:role_id,'.config('constants.user.role.admin')
        ];
        
        if ($request->get('role_id') != config('constants.user.role.admin')) {
            $rules['no_of_chats'] = 'integer|between:1,999';
        }
        return $rules;
    }

    public function messages()
    {
        return [
            'report_to.required_unless' => default_trans(Session::get('userOrganizationId').'/user_list.validation_messages.report_to_required', __('default/user_list.validation_messages.report_to_required')),
            'password.regex' => default_trans(Session::get('userOrganizationId').'/user_list.validation_messages.msg_password_policy', __('default/user_list.validation_messages.msg_password_policy')),
            'name.required' => default_trans(Session::get('userOrganizationId').'/user_list.validation_messages.msg_user_name_required', __('default/user_list.validation_messages.msg_user_name_required')),
            'name.regex' => default_trans(Session::get('userOrganizationId').'/user_list.validation_messages.msg_user_name_alpha', __('default/user_list.validation_messages.msg_user_name_alpha')),
            'mobile_number.required' => default_trans(Session::get('userOrganizationId').'/user_list.validation_messages.msg_mobile_required', __('default/user_list.validation_messages.msg_mobile_required')),
            'mobile_number.digits_between' => default_trans(Session::get('userOrganizationId').'/user_list.validation_messages.msg_mobile_required', __('default/user_list.validation_messages.msg_mobile_required')),
            'no_of_chats.required' => default_trans(Session::get('userOrganizationId').'/user_list.validation_messages.msg_concurrent_chat_required', __('default/user_list.validation_messages.msg_concurrent_chat_required')),
           // 'no_of_chats.required_unless' => __('message.msg_concurrent_chat_required'),
            'no_of_chats.integer' => default_trans(Session::get('userOrganizationId').'/user_list.validation_messages.msg_concurrent_chat_integer', __('default/user_list.validation_messages.msg_concurrent_chat_integer')),
            'no_of_chats.between' => default_trans(Session::get('userOrganizationId').'/user_list.validation_messages.msg_concurrent_chat_betweeen', __('default/user_list.validation_messages.msg_concurrent_chat_betweeen')),
            'email.required' => default_trans(Session::get('userOrganizationId').'/user_list.validation_messages.msg_email_required', __('default/user_list.validation_messages.msg_email_required')),
            'timezone.required' => default_trans(Session::get('userOrganizationId').'/user_list.validation_messages.msg_timezone_required', __('default/user_list.validation_messages.msg_timezone_required')),
            'role_id.required' => default_trans(Session::get('userOrganizationId').'/user_list.validation_messages.msg_role_id_required', __('default/user_list.validation_messages.msg_role_id_required')),
            'name.string' => default_trans(Session::get('userOrganizationId').'/user_list.validation_messages.msg_user_name_alpha', __('default/user_list.validation_messages.msg_user_name_alpha')),
            'gender.required' => default_trans(Session::get('userOrganizationId').'/user_list.validation_messages.msg_gender_required', __('default/user_list.validation_messages.msg_gender_required')),
            'email.email' => default_trans(Session::get('userOrganizationId').'/user_list.validation_messages.msg_email_incorrect', __('default/user_list.validation_messages.msg_email_incorrect')),
            'email.unique' => default_trans(Session::get('userOrganizationId').'/user_list.validation_messages.msg_email_unique', __('default/user_list.validation_messages.msg_email_unique')),
            'password.required' => default_trans(Session::get('userOrganizationId').'/user_list.validation_messages.msg_password_required', __('default/user_list.validation_messages.msg_password_required')),
            'group.required_unless' => default_trans(Session::get('userOrganizationId').'/user_list.validation_messages.group_required_unless', __('default/user_list.validation_messages.group_required_unless')),
            'name.max' => default_trans(Session::get('userOrganizationId').'/user_list.validation_messages.max_char_count', __('default/user_list.validation_messages.max_char_count')),
        ];
    }
}
