<?php

namespace Luur\Validator\Exceptions;

class InvalidRule extends ValidatorException
{
    public function __construct($message = 'Invalid rule provided', $code = 431, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
