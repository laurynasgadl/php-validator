<?php


namespace Luur\Validator\Rules\Concrete;


use Luur\Validator\Rules\AbstractRule;

class BetweenRule extends AbstractRule
{
    protected $minValue;
    protected $maxValue;

    public function __construct($minValue, $maxValue)
    {
        $this->minValue = $minValue;
        $this->maxValue = $maxValue;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function passes($value)
    {
        return SizeRule::getSize($value) >= $this->minValue &&
               SizeRule::getSize($value) <= $this->maxValue;
    }
}
