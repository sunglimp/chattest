<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\PermissionSetting;
use App\Models\ChatChannel;
use Illuminate\Support\Facades\Input;

class EmailFileSizeCheck implements Rule
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
        try {
            $sum = 0;
            foreach($value as $file) {
                $fileSize = $file->getSize()* config('config.FILE_CONVERSION.FACTOR');
                $sum += $fileSize;
            }
            
            if ($sum > config('config.EMAIL_MAX_LENGTH')) {
                return false;
            } else {
                return true;
            }
          
        } catch(\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('message.email_file_size_exceeded');
    }
}
