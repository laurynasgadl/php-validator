<?php


namespace Luur\Validator\Rules\Concrete;


use Luur\Validator\Rules\AbstractRule;

class SizeRule extends AbstractRule
{
    protected $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function passes($value)
    {
        return $this->getSize($value) == $this->value;
    }

    /**
     * @param $value
     * @return int
     */
    public static function getSize($value)
    {
        if (is_array($value)) {
            return count($value);
        }

        if (is_string($value)) {
            return strlen($value);
        }

        if (is_bool($value)) {
            return (int) $value;
        }

        return $value;
    }
}
