<?php


namespace Luur\Validator\Rules\Concrete;


use Luur\Validator\Rules\AbstractRule;

class StringRule extends AbstractRule
{
    /**
     * @param mixed $value
     * @return bool
     */
    public function passes($value)
    {
        return is_string($value) || is_numeric($value);
    }
}
