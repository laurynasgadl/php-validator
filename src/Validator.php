<?php


namespace Luur\Validator;


use Luur\Validator\Exceptions\InvalidRule;
use Luur\Validator\Exceptions\ValidationFailed;
use Luur\Validator\Exceptions\ValidatorException;
use Luur\Validator\Rules\AbstractRule;
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
     * @throws ValidatorException
     */
    public function validate($rules, $params)
    {
        $this->setRules($rules);
        $this->setParams($params);

        $this->execValidation($this->sortRules($rules));

        return $this->getParams();
    }

    /**
     * @param ContextInterface $handler
     */
    public function setContextHandler(ContextInterface $handler)
    {
        $this->contextHandler = $handler;
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
     * @param array $ruleSet
     * @throws ValidatorException
     */
    protected function execValidation($ruleSet)
    {
        $skippablePaths = [];
        foreach ($ruleSet as $path => $rules) {
            if (in_array($path, $skippablePaths)) {
                continue;
            }
            $resolvedRules = $this->resolveRuleSet($this->parseRuleSetArray($rules));
            $values        = $this->getValuesForPath($path);

            foreach ($values as $value) {
                // valueNeedsValidation returns false if the value doesn't exist
                // and it is not required, meaning, the values for related child
                // paths do not exist either
                if (!$this->valueNeedsValidation($value, $resolvedRules)) {
                    $skippablePaths = array_merge($skippablePaths, $this->getChildPaths($path, array_keys($ruleSet)));
                    continue;
                }

                foreach ($resolvedRules as $rule) {
                    /** @var AbstractRule $rule */
                    $passes = $rule->passes($value);

                    // If the value is null and it passed the current rule
                    // it should not run validation for other rules.
                    // This is in order to allow rules such as `required_with`
                    // to work as expected
                    if ($passes && $value === null) {
                        break;
                    }

                    if (!$passes) {
                        throw new ValidationFailed($this->getRuleFailMessage($path, $rule));
                    }
                }
            }
        }
    }

    /**
     * @param string $path
     * @param array $paths
     * @return array
     */
    protected function getChildPaths($path, $paths)
    {
        return array_filter($paths, function ($rulePath) use ($path) {
            return substr($rulePath, 0, strlen($path)) === $path;
        });
    }

    /**
     * @param mixed $value
     * @param array $resolvedRules
     * @return bool
     */
    protected function valueNeedsValidation($value, $resolvedRules)
    {
        // Always run rule validation on values that exist in params
        if ($value !== null) {
            return true;
        }

        foreach ($resolvedRules as $rule) {
            /**
             * Run rule validation if any one of the rules
             * belongs to `required` rule type
             *
             * @var AbstractRule $rule
             */
            if (strpos($rule->getSignature(), 'required') !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $path
     * @param AbstractRule $rule
     * @return string
     */
    protected function getRuleFailMessage($path, $rule)
    {
        return "{$path} failed [{$rule->getSignature()}] rule validation";
    }

    /**
     * Return the values for specified parameter path.
     * This will always return an array with a single value
     * unless the path contains a wildcard part, in this case
     * the values of all the matching paths will be returned
     *
     * @param string $path
     * @return array
     */
    protected function getValuesForPath($path)
    {
        return array_map(function ($fullPath) {
            return $this->contextHandler->get($fullPath);
        }, $this->expandPath($path, $this->contextHandler->toArray()));
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
     * @param string $key
     * @param array $context
     * @return array
     */
    protected function expandPath($key, $context)
    {
        return $this->findPaths($context, explode(self::PATH_DELIMITER, $key));
    }

    /**
     * @param array $data
     * @param array $parts
     * @param string|null $currentPath
     * @return array
     */
    protected function findPaths($data, $parts, $currentPath = null)
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
            if (is_array($data) && array_key_exists($path, $data)) {
                $keys = array_merge($this->findPaths($data[$path], $parts, $resolvedPath), $keys);
            } else {
                $keys[] = $resolvedPath;
            }
        }

        return $keys;
    }
}
