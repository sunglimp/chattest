<?php

namespace App\Http\Requests\APIRequest;

use App\Rules\EmailSubjectRequired;
use App\Rules\EmailBodyRequired;
use App\Rules\EmailFileSizeCheck;

class EmailRequest extends CustomFormRequest
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
            'request.recipient.*.to' => 'required|email',
            'request.recipient.*.cc' => 'sometimes|email',
            'request.recipient.*.bcc' => 'sometimes|email',
            'request' => [new EmailSubjectRequired(), new EmailBodyRequired()],
            'request.*.chatChannelId' => 'required',
            'file' => ['sometimes', new EmailFileSizeCheck()]
        ];
    }
}
