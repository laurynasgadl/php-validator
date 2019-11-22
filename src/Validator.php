<?php


namespace Luur\Validator;


use Luur\Validator\Exceptions\InvalidRule;
use Luur\Validator\Exceptions\ValidationFailed;
use Luur\Validator\Rules\AbstractRule;
use Luur\Validator\Rules\Concrete\RequiredRule;
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
     * @throws ValidationFailed
     */
    public function validate(array $rules, array $params)
    {
        $this->emptyErrorBag();

        $this->setRules($rules);
        $this->setParams($params);

        $this->sortRules();
        $this->validateRules();

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
    protected function setRules(array $rules)
    {
        $this->rules = $rules;
    }

    /**
     * @param array $params
     */
    protected function setParams(array $params)
    {
        $this->contextHandler->setParams($params);
    }

    /**
     * @throws InvalidRule
     * @throws ValidationFailed
     */
    protected function validateRules()
    {
        foreach ($this->rules as $key => $ruleSet) {
            $resolved = $this->resolveRuleSet($this->parseRuleSetArray($ruleSet));
            $this->validateRule($key, $resolved);

            if ($this->containsErrors()) {
                throw new ValidationFailed($this->errorBag);
            }
        }
    }

    /**
     * @param string $key
     * @param array $rules
     */
    protected function validateRule($key, array $rules)
    {
        foreach ($rules as $rule) {
            $keys = $this->expandKey($key);

            foreach ($keys as $expandedKey) {
                if (!$this->valueRequiresValidation($expandedKey, $rules)) {
                    continue;
                }

                /**
                 * @var AbstractRule $rule
                 */
                if (!$rule->passesByKey($expandedKey)) {
                    $this->addError($expandedKey, $rule);
                }
            }
        }
    }

    /**
     * @param $key
     * @param $rules
     * @return bool
     */
    protected function valueRequiresValidation($key, $rules)
    {
        $value = $this->contextHandler->get($key);

        if ($value !== null) {
            return true;
        }

        $requiredRuleSet = false;

        foreach($rules as $rule) {
            /**
             * @var AbstractRule $rule
             */
            if ($rule::getSlug() == RequiredRule::getSlug()) {
                $requiredRuleSet = true;
            }
        }

        return $requiredRuleSet;
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

    protected function sortRules()
    {
        $parser = function ($a) {
            return count(explode(self::PATH_DELIMITER, $a));
        };

        $sort = function ($a, $b) use ($parser) {
            $aCount = $parser($a);
            $bCount = $parser($b);
            return $aCount > $bCount;
        };

        uksort($this->rules, $sort);
    }

    /**
     * @param array $rulesSet
     */
    protected function sortRuleSet(&$rulesSet)
    {
        $sort = function ($a, $b) {
            /**
             * @var AbstractRule $a
             * @var AbstractRule $b
             */
            return $a->getPrecedence() < $b->getPrecedence();
        };

        usort($rulesSet, $sort);
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

        $this->sortRuleSet($ruleSet);

        return $ruleSet;
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
     * @return array
     */
    protected function expandKey($key)
    {
        $parts = explode(self::PATH_DELIMITER, $key);
        return $this->findKeys($this->contextHandler->toArray(), $parts);
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
            if (array_key_exists($path, $data)) {
                $nextPath = $currentPath ? $currentPath . self::PATH_DELIMITER . $path : $path;
                $keys     = array_merge($this->findKeys($data[$path], $parts, $nextPath), $keys);
            } else {
                $keys[] = $currentPath ? $currentPath . self::PATH_DELIMITER . $path : $path;
            }
        }

        return $keys;
    }
}
