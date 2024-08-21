<?php

namespace App\Http\Requests\Organization;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class StoreOrganizationRequest extends FormRequest
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
            'contact_name' => 'required|string|regex:/^[a-zA-Z\'., ]+$/',
            'mobile_number' => 'required|digits_between:10,15',
            'company_name' => 'required|unique:organizations,company_name|string',
            'email' => 'required|email|unique:organizations,email,,,deleted_at,NULL',
            'seat_alloted' => 'required|integer|between:1,999',
            'timezone' => 'required',
            'image' => 'nullable|image|max:900',
            'website' => 'nullable|url',
            'language'    => 'required'
        ];
    }

    public function messages()
    {
        return [
            'contact_name.required' => __('message.msg_name_required'),
            'contact_name.regex' => __('message.msg_name_regex'),
            'company_name.regex' => __('message.msg_company_name_regex'),
            'mobile_number.required' => __('message.msg_mobile_required'),
            'company_name.required' => __('message.msg_company_name_required'),
            'company_name.unique' => __('message.msg_company_name_exist'),
            'email.required' => __('message.msg_email_required'),
            'seat_alloted.required' => __('message.msg_seat_alloted_required'),
            'seat_alloted.integer' => __('message.msg_seat_alloted_integer'),
            'timezone.required' => __('message.msg_timezone_required'),
            'website.url' => __('message.msg_website_url'),
            'language.required' => __('message.msg_language_required'),
        ];
    }
}
