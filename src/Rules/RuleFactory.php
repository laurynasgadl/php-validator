<?php


namespace Luur\Validator\Rules;


use Luur\Validator\Tools\Helpers;
use Luur\Validator\ContextInterface;
use Luur\Validator\Exceptions\InvalidRule;

/**
 * Class RuleFactory
 * @package Hostinger\EventLibrary\Tools\Validation
 */
class RuleFactory
{
    /**
     * @param ContextInterface $context
     * @param string $slug
     * @param array $args
     * @return AbstractRule
     * @throws InvalidRule
     */
    public static function build($context, $slug, $args = [])
    {
        $class = self::getClass($slug);
        /**
         * @var AbstractRule $rule
         */
        $rule = new $class(...$args);
        $rule->setContext($context);
        return $rule;
    }

    /**
     * @param $slug
     * @return mixed
     * @throws InvalidRule
     */
    protected static function getClass($slug)
    {
        $class = __NAMESPACE__ . '\\Concrete\\' . Helpers::snakeToPascal($slug) . 'Rule';

        if (!class_exists($class)) {
            throw new InvalidRule("Invalid rule provided '{$slug}'");
        }

        return $class;
    }
}
