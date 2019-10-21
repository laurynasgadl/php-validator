<?php


namespace Luur\Validator\Rules\Concrete;


use Luur\Validator\Rules\AbstractRule;

class IntegerRule extends AbstractRule
{
    /**
     * @param mixed $value
     * @return bool
     */
    public function passes($value)
    {
        return is_int($value);
    }
}
