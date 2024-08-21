<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class AttachmentFormatCheck implements Rule
{

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return in_array(strtolower($value->getClientOriginalExtension()),
                array_map('strtolower', config('config.ATTACHMENT_EXTENSIONS')));
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'File format is not supported.';
    }
}
