<?php

namespace App\Http\Requests\APIRequest\Tag;

use App\Http\Requests\APIRequest\CustomFormRequest;

class LinkTagRequest extends CustomFormRequest
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
           'tagId'=> 'required',
           'channelId' => 'required'
        ];
    }
}
