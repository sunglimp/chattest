<?php

namespace App\Http\Requests\APIRequest;

use App\Rules\AttachmentSizeCheck;
use App\Rules\AttachmentFormatCheck;

class AttachmentRequest extends CustomFormRequest
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
            'file' => ['required', new AttachmentSizeCheck(), new AttachmentFormatCheck()],
            'chat_channel_id' => 'required',
            'recipient'     => 'required',
            'message_type'  => 'required',
            'sender_display_name' => 'required',
            'channel_name'        => 'required'
        ];
    }
    
    public function messages()
    {
        return [
            'file.mimes' =>  __("message.incorrect_file_format")
        ];
    }
}
