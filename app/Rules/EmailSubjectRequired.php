<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class EmailSubjectRequired implements Rule
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
        $emailData = json_decode($value,true);
        return !empty($emailData['subject']);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('message.email_subject_required');
    }
}
