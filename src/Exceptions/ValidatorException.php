<?php

namespace Luur\Validator\Exceptions;

use Exception;

class ValidatorException extends Exception
{
    public function __construct($message = 'Validator encountered an exception', $code = 430, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
