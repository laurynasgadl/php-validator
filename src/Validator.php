<?php


namespace Luur\Validator;


use Luur\Validator\Exceptions\InvalidRule;
use Luur\Validator\Exceptions\RuleRegistryException;
use Luur\Validator\Exceptions\ValidationFailed;
use Luur\Validator\Exceptions\ValidatorException;
use Luur\Validator\Rules\AbstractRule;
use Luur\Validator\Rules\RuleRegistry;

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
    protected $messages;

    /**
     * @var RuleRegistry
     */
    protected $registry;

    /**
     * @var ContextInterface
     */
    protected $contextHandler;

    /**
     * @var ContextInterface
     */
    protected $validated;

    /**
     * Validator constructor.
     * @param array|null $messages
     * @param ContextInterface|null $context
     */
    public function __construct($messages = null, $context = null)
    {
        $this->contextHandler  = $context ? : new Context();
        $this->messages        = $messages;
        $this->registry        = $this->buildRegistry();

        $contextClass          = get_class($this->contextHandler);
        $this->validated       = new $contextClass();
    }

    /**
     * @param array $rules
     * @param array $params
     * @param array|null $messages
     * @return array
     * @throws ValidatorException
     */
    public function validate($rules, $params, $messages = null)
    {
        $this->validated->setParams([]);
        $this->setMessages($messages);
        $this->contextHandler->setParams($params);
        $this->execValidation($this->sortRules($rules));

        return $this->contextHandler->toArray();
    }

    /**
     * @param array|null $messages
     */
    public function setMessages($messages)
    {
        if (is_array($messages)) {
            $this->messages = $messages;
        }
    }

    /**
     * @param ContextInterface $handler
     */
    public function setContextHandler($handler)
    {
        $this->contextHandler = $handler;
    }

    /**
     * @param string $ruleSlug
     * @param string $ruleClass
     * @throws RuleRegistryException
     */
    public function registerRule($ruleSlug, $ruleClass)
    {
        $this->registry->register($ruleSlug, $ruleClass);
    }

    /**
     * Get only the validated params of the last validation
     *
     * @return array
     */
    public function validated()
    {
        return $this->validated->toArray();
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
            $resolvedPaths = $this->resolveFullPaths($path);

            foreach ($resolvedPaths as $fullPath) {
                $value = $this->contextHandler->get($fullPath);

                /**
                 * valueNeedsValidation returns false if the value doesn't exist
                 * and it is not required, meaning, the values for related child
                 * paths do not exist either
                 */
                if (!$this->valueNeedsValidation($value, $resolvedRules)) {
                    $skippablePaths = array_merge($skippablePaths, $this->getChildPaths($path, array_keys($ruleSet)));
                    continue;
                }

                foreach ($resolvedRules as $rule) {
                    /** @var AbstractRule $rule */
                    $passes = $this->ruleEvaluatedByPath($rule) ?
                        $rule->passes($path) :
                        $rule->passes($value);

                    /**
                     * After the rule is applied, get the value again
                     * as it may have been changed by the rule.
                     */
                    $value = $this->contextHandler->get($fullPath);

                    /**
                     * If the value is null and it passed the current rule
                     * it should not run validation for other rules.
                     * This is in order to allow rules such as `required_with`
                     * to work as expected
                     */
                    if ($passes && $value === null) {
                        break;
                    }

                    if (!$passes) {
                        throw new ValidationFailed($this->getRuleFailMessage($path, $rule));
                    }

                    $this->validated->set($fullPath, $value);
                }
            }
        }
    }

    /**
     * Check if the path should be passed to the rule
     * instead of the value. This is mostly used for
     * `default` rule.
     *
     * @param AbstractRule $rule
     * @return bool
     */
    protected function ruleEvaluatedByPath(AbstractRule $rule)
    {
        return $rule->expectsPath();
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
        /**
         * Always run rule validation on values that exist in params
         */
        if ($value !== null) {
            return true;
        }

        foreach ($resolvedRules as $rule) {
            /**
             * Run rule validation if any one of the rules
             * belongs to `required` or `default` rule type
             *
             * @var AbstractRule $rule
             */
            if (strpos($rule->getSignature(), 'required') !== false ||
                strpos($rule->getSignature(), 'default') !== false) {
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
        $messageKey = $path.'.'.$rule->getSignature();

        if (is_array($this->messages) && array_key_exists($messageKey, $this->messages)) {
            return $this->messages[$messageKey];
        } else {
            return "<{$path}> failed [{$rule->getSignature()}] rule validation";
        }
    }

    /**
     * Return the full paths for specified parameter path.
     * This will always return an array with a single full path
     * unless the path contains a wildcard part, in this case
     * all paths matching the wildcard pattern will be returned
     *
     * @param string $path
     * @return array
     */
    protected function resolveFullPaths($path)
    {
        return $this->expandPath($path, $this->contextHandler->toArray());
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

                $ruleClass = $this->registry->find($ruleSlug);
                $rule      = new $ruleClass(...$ruleArgs);
            }

            $rule->setContext($this->contextHandler);
            $ruleSet[] = $rule;
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
            $resolvedPath = $currentPath ? $currentPath.self::PATH_DELIMITER.$path : $path;
            if (is_array($data) && array_key_exists($path, $data)) {
                $keys = array_merge($this->findPaths($data[$path], $parts, $resolvedPath), $keys);
            } else {
                $keys[] = $resolvedPath;
            }
        }

        return $keys;
    }

    /**
     * @return RuleRegistry
     */
    protected function buildRegistry()
    {
        $rules = [];

        $ruleClasses = array_map(function ($ruleFile) {
            return __NAMESPACE__ . '\\Rules\\Concrete\\' . pathinfo($ruleFile, PATHINFO_FILENAME);
        }, array_diff(scandir(__DIR__ . '/Rules/Concrete'), array('.', '..')));

        foreach ($ruleClasses as $ruleClass) {
            /**
             * @var AbstractRule $ruleClass
             */
            $rules[$ruleClass::getSlug()] = $ruleClass;
        }

        return new RuleRegistry($rules);
    }
}
