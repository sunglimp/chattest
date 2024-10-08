<?php

namespace App\Http\Requests\APIRequest;

use Illuminate\Foundation\Http\FormRequest;

class ChatTransferRequest extends CustomFormRequest
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
            'comment' => 'sometimes|max:'.config('chat.chat_transfer_size')
            
        ];
    }
    
    public function messages()
    {
        return [
            'comment.max' => __('message.chat_transfer_max')
        ];
    }
}
