<?php

namespace App\Exceptions;

/**
 * Description of InvalidInputException
 *
 * @author ankit
 */
class InvalidInputException extends \Exception
{
    public function getMessage()
    {
        return __('invalid_input_exception');
    }
}
