<?php


namespace Luur\Validator\Rules\Concrete;


use Luur\Validator\Rules\AbstractRule;

class UrlRule extends AbstractRule
{
    /**
     * @param mixed $value
     * @return bool
     */
    public function passes($value)
    {
        if (!is_string($value)) {
            return false;
        }

        return !!filter_var($value, FILTER_VALIDATE_URL);
    }
}
