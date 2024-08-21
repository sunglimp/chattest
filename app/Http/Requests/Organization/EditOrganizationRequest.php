<?php

namespace App\Http\Requests\Organization;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class EditOrganizationRequest extends StoreOrganizationRequest
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
        $rules = parent::rules($request);
        $newRules = [
            'company_name' => 'required|unique:organizations,company_name,'.$request->get('organization_id'),
            'email' => 'required|email|unique:organizations,email,'.$request->get('organization_id').',,deleted_at,NULL',
            'seat_alloted' => ['required','integer','between:1,999',new \App\Rules\CheckOrganizationSeats($request)],
            'language'    => ['required', new \App\Rules\LanguageCheck($request)]
        ];
        return array_merge($rules, $newRules);
    }

    public function messages()
    {
        $messages = parent::messages();
        $newMessages = [
            'company_name.unique' => __('message.msg_organization_exist'),
            'seat_alloted.required' => __('message.msg_seat_alloted_required'),
            'seat_alloted.integer' => __('message.msg_seat_alloted_integer'),
            'language.required' => __('message.msg_language_required'),
        ];
        return array_merge($messages, $newMessages);
    }
}
