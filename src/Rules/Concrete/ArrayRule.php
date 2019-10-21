<?php


namespace Luur\Validator\Rules\Concrete;

use Luur\Validator\Rules\AbstractRule;


class ArrayRule extends AbstractRule
{
    /**
     * @param mixed $value
     * @return bool
     */
    public function passes($value)
    {
        return is_array($value);
    }
}
