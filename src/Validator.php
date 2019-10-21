<?php


namespace Luur\Validator;


use Luur\Validator\Exceptions\InvalidRule;
use Luur\Validator\Exceptions\ValidationFailed;
use Luur\Validator\Rules\AbstractRule;
use Luur\Validator\Rules\Concrete\RequiredRule;
use Luur\Validator\Rules\RuleFactory;

class Validator
{
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

    public function __construct()
    {
        $this->contextHandler = new Context();
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
            if (!$this->valueRequiresValidation($key, $rules)) {
                continue;
            }

            /**
             * @var AbstractRule $rule
             */
            if (!$rule->passesByKey($key)) {
                $this->addError($key, $rule);
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
        $this->errorBag[$key] = $rule->getSignature();
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
            return count(explode('.', $a));
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
                $ruleArgs = explode(':', $rule);
                $ruleSlug = array_shift($ruleArgs);

                if (count($ruleArgs) > 0) {
                    $ruleArgs = explode(',', $ruleArgs[0]);
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
            $ruleSet = explode('|', $ruleSet);
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
}
