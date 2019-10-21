<?php


namespace Luur\Validator\Rules\Concrete;


use Luur\Validator\Rules\AbstractRule;

class MinRule extends AbstractRule
{
    protected $minValue;

    public function __construct($minValue)
    {
        $this->minValue = $minValue;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function passes($value)
    {
        return SizeRule::getSize($value) >= $this->minValue;
    }
}
