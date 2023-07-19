<?php

namespace Luur\Validator\Rules;

use Luur\Validator\Exceptions\InvalidRule;
use Luur\Validator\Exceptions\RuleRegistryException;

class RuleRegistry
{
    /**
     * @var array
     */
    protected $rules;

    /**
     * RuleRegistry constructor.
     * @param array $rules
     */
    public function __construct($rules = [])
    {
        $this->rules = $rules;
    }

    /**
     * @param string $ruleSlug
     * @param string $ruleClass
     * @throws RuleRegistryException
     */
    public function register($ruleSlug, $ruleClass)
    {
        if (array_key_exists($ruleSlug, $this->rules) && $this->rules[$ruleSlug] !== $ruleClass) {
            throw new RuleRegistryException("Rule [$ruleSlug] already registered");
        }

        $this->rules[$ruleSlug] = $ruleClass;
    }

    /**
     * @param $ruleSlug
     * @return string
     * @throws InvalidRule
     */
    public function find($ruleSlug)
    {
        if (!array_key_exists($ruleSlug, $this->rules)) {
            throw new InvalidRule("Invalid rule provided [{$ruleSlug}]");
        }

        return $this->rules[$ruleSlug];
    }
}
