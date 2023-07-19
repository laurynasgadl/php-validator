<?php

namespace Luur\Validator\Exceptions;

class RuleRegistryException extends ValidatorException
{
    public function __construct($message = 'Rule registry encountered an exception', $code = 434, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
