<?php


namespace Luur\Validator\Rules\Concrete;


use Luur\Validator\Rules\AbstractRule;

class MaxRule extends AbstractRule
{
    protected $maxValue;

    public function __construct($maxValue)
    {
        $this->maxValue = $maxValue;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function passes($value)
    {
        return SizeRule::getSize($value) <= $this->maxValue;
    }
}
