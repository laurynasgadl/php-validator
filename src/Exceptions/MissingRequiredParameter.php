<?php


namespace Luur\Validator\Exceptions;

use Exception;

class MissingRequiredParameter extends Exception
{
    public function __construct($message = null) {
        parent::__construct($message ?: 'Missing required parameter.');
    }
}
