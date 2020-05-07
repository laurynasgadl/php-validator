<?php


namespace Luur\Validator\Exceptions;

class ValidationFailed extends ValidatorException
{
    public function __construct($message = 'Validation failed', $code = 432, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
