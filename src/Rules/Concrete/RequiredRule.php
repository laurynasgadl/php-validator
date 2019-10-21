<?php


namespace Luur\Validator\Rules\Concrete;


use Luur\Validator\Rules\AbstractRule;

class RequiredRule extends AbstractRule
{
    protected $precedence = 99;

    /**
     * @param mixed $value
     * @return bool
     */
    public function passes($value)
    {
        return !is_null($value);
    }
}
