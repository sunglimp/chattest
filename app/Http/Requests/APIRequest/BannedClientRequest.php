<?php

namespace App\Http\Requests\APIRequest;

use Illuminate\Foundation\Http\FormRequest;

class BannedClientRequest extends CustomFormRequest
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
            'keyword' => 'sometimes|in:'.config('constants.BANNED_CLIENTS.TEXT_SEARCH').','.config('constants.BANNED_CLIENTS.AGENT_SEARCH'),
        ];
    }
}
