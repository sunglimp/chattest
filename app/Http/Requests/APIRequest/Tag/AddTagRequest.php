<?php

namespace App\Http\Requests\APIRequest\Tag;

use App\Http\Requests\APIRequest\TokenRequiredRequest;
use App\User;
use App\Rules\DuplicateTagRule;
use Illuminate\Support\Facades\Input;
use App\Http\Requests\APIRequest\UserIdRequiredRequest;

class AddTagRequest extends UserIdRequiredRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $userId = Input::get('userId') ?? null;
        $rules = parent::rules();
        $rules['name'] = ['required','regex:'.config('constants.SPACE_NOT_ALLOWED_CONFIG')
            , new DuplicateTagRule(), 'unique:tags,name,NULL,id,user_id,'.$userId.',deleted_at,NULL'];
        $rules['organizationId'] = 'required';
        $rules['channelId'] = 'required';
        return $rules;
    }
    
    /**
     * {@inheritDoc}
     * @see \App\Http\Requests\APIRequest\TokenRequiredRequest::messages()
     */
    public function messages()
    {
        $messages = parent::messages();
        $messages['name.required'] = __('message.tag_name_required');
        $messages['name.regex'] = __('message.tag_incorrect');
        return $messages;
    }
}
