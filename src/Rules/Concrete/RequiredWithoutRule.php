<?php


namespace Luur\Validator\Rules\Concrete;


use Luur\Validator\Rules\AbstractRule;

class RequiredWithoutRule extends AbstractRule
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
        return $this->otherParamsExist() || !is_null($value);
    }

    /**
     * @return bool
     */
    protected function otherParamsExist()
    {
        foreach ($this->params as $key) {
            $value = $this->context->get($key);
            if (!is_null($value)) {
                return true;
            }
        }
        return false;
    }
}
