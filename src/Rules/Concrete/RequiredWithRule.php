<?php


namespace Luur\Validator\Rules\Concrete;


use Luur\Validator\Rules\AbstractRule;

class RequiredWithRule extends AbstractRule
{
    protected $precedence = 98;

    /**
     * @var array
     */
    protected $params;

    public function __construct(...$params)
    {
        $this->params = $params;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function passes($value)
    {
        if ($this->allOtherParamsExist()) {
            return !is_null($value);
        }
        return true;
    }

    /**
     * @return bool
     */
    protected function allOtherParamsExist()
    {
        $existing = array_filter($this->params, function ($key) {
            $value = $this->context->get($key);
            return !is_null($value);
        });

        return count($existing) === count($this->params);
    }
}
