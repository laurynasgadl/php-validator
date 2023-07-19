<?php

namespace Luur\Validator\Tests\Rules;

use Luur\Validator\Rules\AbstractRule;

class TestRule extends AbstractRule
{
    public function passes($value)
    {
        return $value === 'test';
    }
}
