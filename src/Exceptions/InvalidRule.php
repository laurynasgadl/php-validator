<?php


namespace Luur\Validator\Exceptions;

use Exception;

class InvalidRule extends Exception
{
    public function __construct($message = null) {
        parent::__construct($message ?: 'Invalid rule provided.');
    }
}
