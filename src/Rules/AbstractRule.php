<?php


namespace Luur\Validator\Rules;


use Luur\Validator\Tools\Helpers;
use Luur\Validator\ContextInterface;

abstract class AbstractRule
{
    /**
     * @var ContextInterface
     */
    protected $context;

    /**
     * @var int
     */
    protected $precedence = 0;

    /**
     * @param ContextInterface $context
     */
    public function setContext(ContextInterface &$context)
    {
        $this->context = $context;
    }

    /**
     * @param int $precedence
     */
    public function setPrecedence($precedence)
    {
        $this->precedence = $precedence;
    }

    /**
     * @return int
     */
    public function getPrecedence()
    {
        return  $this->precedence;
    }

    /**
     * @return string
     */
    public static function getSlug()
    {
        $class = str_replace('Rule', '', static::class);
        return Helpers::camelToSnake(
            substr($class, strrpos($class, '\\') + 1)
        );
    }

    /**
     * @return string
     */
    public function getSignature()
    {
        $addon = null;

        $params = array_filter(get_object_vars($this), function ($key) {
            return !in_array($key, [
                'precedence', 'context',
            ]);
        }, ARRAY_FILTER_USE_KEY);

        $values = array_map(function ($value) {
            return is_array($value) ? implode(',', $value) : $value;
        }, $params);

        if ($values) {
            $addon = ':' . implode(',', $values);
        }

        return self::getSlug() . $addon;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    abstract public function passes($value);

    /**
     * @param $key
     * @return bool
     */
    public function passesByKey($key)
    {
        return $this->passes($this->context->get($key));
    }
}
