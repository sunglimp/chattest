<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class UpdatePasswordRequest extends FormRequest
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
            'password' => 'required|string|regex:/(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$/',
            'confirm_password' => 'required|same:password'
        ];
    }

    public function messages()
    {
        return [
            'report_to.required_unless' => __('message.report_to_required'),
            'password.regex' => __('message.msg_password_policy'),
        ];
    }
}
