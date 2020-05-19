<?php


namespace Luur\Validator\Rules\Concrete;

use Luur\Validator\Rules\AbstractRule;


class RegexRule extends AbstractRule
{
    /**
     * @var string
     */
    protected $pattern;

    public function __construct($pattern)
    {
        $this->pattern = $pattern;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function passes($value)
    {
        return preg_match($this->pattern, $value);
    }
}
