<?php


namespace Luur\Validator\Rules\Concrete;

use Luur\Validator\ContextInterface;
use Luur\Validator\Rules\AbstractRule;


class DefaultRule extends AbstractRule
{
    protected $precedence = 100;

    protected $passPath = true;

    protected $defaultValue;

    public function __construct($defaultValue)
    {
        $this->defaultValue = $defaultValue;
    }

    /**
     * @param string $path
     * @return bool
     */
    public function passes($path)
    {
        if (!$this->context instanceof ContextInterface) {
            return false;
        }

        $value = $this->context->get($path);

        if (is_null($value)) {
            $this->context->set($path, $this->defaultValue);
        }

        return true;
    }
}
