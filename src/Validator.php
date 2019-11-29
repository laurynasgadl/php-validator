<?php


namespace Luur\Validator;


use Exception;
use Luur\Validator\Exceptions\InvalidRule;
use Luur\Validator\Exceptions\ValidationFailed;
use Luur\Validator\Rules\AbstractRule;
use Luur\Validator\Rules\Concrete\RequiredRule;
use Luur\Validator\Rules\Concrete\RequiredWithoutRule;
use Luur\Validator\Rules\RuleFactory;

class Validator
{
    const PATH_DELIMITER = '.';

    const PATH_WILDCARD_DELIMITER = '*';

    const RULE_DELIMITER = '|';

    const RULE_ARG_DELIMITER = ':';

    const RULE_PARAM_DELIMITER = ',';

    /**
     * @var array
     */
    protected $rules;

    /**
     * @var ContextInterface
     */
    protected $contextHandler;

    /**
     * @var array
     */
    protected $errorBag = [];

    /**
     * Validator constructor.
     * @param ContextInterface|null $context
     */
    public function __construct($context = null)
    {
        $this->contextHandler = $context ? : new Context();
    }

    /**
     * @param array $rules
     * @param array $params
     * @return array
     * @throws InvalidRule
     * @throws Exception
     */
    public function validate($rules, $params)
    {
        $this->emptyErrorBag();

        $this->setRules($rules);
        $this->setParams($params);

        $this->validateParams($this->sortRules($rules));

        return $this->getParams();
    }

    /**
     * @param ContextInterface $handler
     */
    public function setContextHandler(ContextInterface $handler)
    {
        $this->contextHandler = $handler;
    }

    protected function emptyErrorBag()
    {
        $this->errorBag = [];
    }

    /**
     * @param array $rules
     */
    protected function setRules($rules)
    {
        $this->rules = $rules;
    }

    /**
     * @param array $params
     */
    protected function setParams($params)
    {
        $this->contextHandler->setParams($params);
    }

    /**
     * @param array $rules
     * @throws InvalidRule
     * @throws Exception
     */
    protected function validateParams($rules)
    {
        $skipKeys = [];

        foreach ($rules as $key => $ruleSet) {
            if (in_array($key, $skipKeys)) {
                continue;
            }

            $resolvedRules = $this->resolveRuleSet($this->parseRuleSetArray($ruleSet));

            foreach ($this->expandKey($key, $this->contextHandler->toArray()) as $expandedKey) {
                if ($this->valueRequiresValidation($this->contextHandler->get($expandedKey), $resolvedRules)) {
                    $this->validateRules($expandedKey, $resolvedRules);

                    if ($this->containsErrors()) {
                        throw new ValidationFailed($this->errorBag);
                    }
                } else {
                    $skipKeys = $this->getRelatedKeys($rules, $expandedKey);
                }
            }
        }
    }

    /**
     * @param array $rules
     * @param string $expandedKey
     * @return array
     */
    protected function getRelatedKeys($rules, $expandedKey)
    {
        $keys = [];

        foreach ($rules as $key => $ruleSet) {
            if (substr($key, 0, strlen($expandedKey)) === $expandedKey) {
                $keys[] = $key;
            }
        }

        return $keys;
    }

    /**
     * @param string $expandedKey
     * @param array $rules
     */
    protected function validateRules($expandedKey, $rules)
    {
        foreach ($rules as $rule) {
            /**
             * @var AbstractRule $rule
             */
            if (!$rule->passesByKey($expandedKey)) {
                $this->addError($expandedKey, $rule);
            }
        }
    }

    /**
     * @param mixed $value
     * @param array $resolvedRules
     * @return bool
     */
    protected function valueRequiresValidation($value, $resolvedRules)
    {
        if ($value !== null) {
            return true;
        }

        foreach ($resolvedRules as $rule) {
            if ($rule instanceof RequiredRule || $rule instanceof RequiredWithoutRule) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $key
     * @param AbstractRule $rule
     */
    protected function addError($key, $rule)
    {
        $this->errorBag[$key][] = $rule->getSignature();
    }

    /**
     * @return bool
     */
    protected function containsErrors()
    {
        return !empty($this->errorBag);
    }

    /**
     * @param array $rules
     * @return array
     */
    protected function sortRules($rules)
    {
        $parser = function ($a) {
            return count(explode(self::PATH_DELIMITER, $a));
        };

        uksort($rules, function ($a, $b) use ($parser) {
            $aCount = $parser($a);
            $bCount = $parser($b);
            return $aCount > $bCount;
        });

        return $rules;
    }

    /**
     * @param array $rulesSet
     * @return array
     */
    protected function sortRuleSet($rulesSet)
    {
        usort($rulesSet, function ($a, $b) {
            /**
             * @var AbstractRule $a
             * @var AbstractRule $b
             */
            return $a->getPrecedence() < $b->getPrecedence();
        });

        return $rulesSet;
    }

    /**
     * @param array $rules
     * @return array
     * @throws InvalidRule
     */
    protected function resolveRuleSet($rules)
    {
        $ruleSet = [];

        foreach ($rules as $rule) {
            if (is_string($rule)) {
                $ruleArgs = explode(self::RULE_ARG_DELIMITER, $rule);
                $ruleSlug = array_shift($ruleArgs);

                if (count($ruleArgs) > 0) {
                    $ruleArgs = explode(self::RULE_PARAM_DELIMITER, $ruleArgs[0]);
                }

                $ruleSet[] = RuleFactory::build($this->contextHandler, $ruleSlug, $ruleArgs);
            } elseif ($rule instanceof AbstractRule) {
                $rule->setContext($this->contextHandler);
                $ruleSet[] = $rule;
            }
        }

        return $this->sortRuleSet($ruleSet);
    }

    /**
     * @param $ruleSet
     * @return array
     */
    protected function parseRuleSetArray($ruleSet)
    {
        if (is_string($ruleSet)) {
            $ruleSet = explode(self::RULE_DELIMITER, $ruleSet);
        }

        if ($ruleSet instanceof AbstractRule) {
            $ruleSet = [$ruleSet];
        }

        return is_array($ruleSet) ? $ruleSet : [];
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->contextHandler->toArray();
    }

    /**
     * @return array
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errorBag;
    }

    /**
     * @param string $key
     * @param array $context
     * @return array
     */
    protected function expandKey($key, $context)
    {
        return $this->findKeys($context, explode(self::PATH_DELIMITER, $key));
    }

    /**
     * @param array $data
     * @param array $parts
     * @param string|null $currentPath
     * @return array
     */
    protected function findKeys($data, $parts, $currentPath = null)
    {
        if (count($parts) < 1) {
            return [$currentPath];
        }

        $current = array_shift($parts);

        if ($current == self::PATH_WILDCARD_DELIMITER) {
            $paths = array_keys($data);
        } else {
            $paths = [$current];
        }

        $keys = [];

        foreach ($paths as $path) {
            $resolvedPath = $currentPath ? $currentPath . self::PATH_DELIMITER . $path : $path;
            if (array_key_exists($path, $data)) {
                $keys = array_merge($this->findKeys($data[$path], $parts, $resolvedPath), $keys);
            } else {
                $keys[] = $resolvedPath;
            }
        }

        return $keys;
    }
}
