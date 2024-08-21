<?php

namespace App\Http\Requests\UniqueKey;

use Illuminate\Foundation\Http\FormRequest;

class GetKeyRequest extends FormRequest
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
            'orgId' => 'required'
        ];
    }
    
    public function messages()
    {
        return [
            'orgId.required' => __('message.org_id_required')
        ];
    }
}
