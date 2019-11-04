<?php

namespace Luur\Validator\Tests;

use Luur\Validator\Context;
use Luur\Validator\Exceptions\InvalidRule;
use Luur\Validator\Exceptions\ValidationFailed;
use Luur\Validator\Rules\AbstractRule;
use Luur\Validator\Rules\Concrete\IntegerRule;
use Luur\Validator\Rules\Concrete\RequiredRule;
use Luur\Validator\Validator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    public function testSortsRules()
    {
        $validator = $this->createValidatorMock();

        $params = [];
        $rules  = [
            'test.test'      => 'test',
            'test1'          => 'test',
            'test2'          => 'test',
            'test.test.test' => 'test',
            'test3'          => 'test',
        ];

        $validator->setRules($rules);
        $validator->setParams($params);
        $validator->mockSortRules();

        $sorted = $validator->getRules();

        $this->assertEquals(['test.test.test' => 'test'], array_slice($sorted, -1, 1));
        $this->assertEquals(['test.test' => 'test'], array_slice($sorted, -2, 1));
    }

    public function testResolvesRuleSetInOrder()
    {
        $validator = $this->createValidatorMock();

        $params = ['test' => 'test'];
        $rules  = ['test' => 'integer|required'];

        $validator->setRules($rules);
        $validator->setParams($params);

        $ruleArray = $validator->mockParseRuleSetArray(array_shift($rules));

        $context      = new Context();
        $requiredRule = new RequiredRule();
        $requiredRule->setContext($context);
        $integerRule = new IntegerRule();
        $integerRule->setContext($context);

        $this->assertEquals(
            [$requiredRule, $integerRule],
            $validator->getResolvedRules($validator->mockParseRuleSetArray($ruleArray))
        );
    }

    public function testSetsContextHandler()
    {
        $validator = $this->createValidatorMock();
        $validator->setContextHandler(new Context());
    }

    public function testThrowsValidationFailedError()
    {
        $this->expectException(ValidationFailed::class);
        $validator = $this->createValidatorMock();
        $validator->validate([
            'test' => 'integer|required',
        ], ['test' => 'test']);
    }

    public function testReturnsFailingValidation()
    {
        $validator = new ValidatorMock();

        try {
            $validator->validate([
                'test' => 'integer|required',
            ], ['test' => 'test']);
        } catch (ValidationFailed $exception) {
        }

        $this->assertEquals(['test' => IntegerRule::getSlug()], $validator->getErrors());
    }

    public function testValidatesArrayParams()
    {
        $validator = $this->createValidator();

        $params = [
            'test' => [
                'test'  => -10,
                'test1' => 'test',
                'test2' => 1.1,
            ]
        ];

        $result = $validator->validate([
            'test'       => 'array|required',
            'test.test'  => 'integer|required|min:-100|max:125|between:-25,125',
            'test.test1' => 'string|required|size:4',
            'test.test2' => 'float|required',
            'test.test3' => 'float',
        ], $params);

        $this->assertEquals($result, $params);
    }

    public function testValidatesWithVariousRuleDefinitions()
    {
        $validator = $this->createValidator();

        $params = [
            'test' => [
                'test'  => 123,
                'test1' => 123,
            ]
        ];

        $result = $validator->validate([
            'test'       => ['array', new RequiredRule()],
            'test.test'  => new IntegerRule(),
            'test.test1' => 'min:100',
        ], $params);

        $this->assertEquals($result, $params);
    }

    public function testValidatesWithCustomRule()
    {
        $validator = $this->createValidator();

        $params = ['test' => 123];
        $result = $validator->validate([
            'test' => new MockRule(),
        ], $params);
        $this->assertEquals($result, $params);
    }

    public function testValidatesWildcardPaths()
    {
        $validator = $this->createValidator();

        $params = [
            'test_unassoc' => [
                [
                    'id' => 1,
                ],
                [
                    'id' => 2,
                ],
            ],
            'test_assoc'   => [
                '1' => [
                    'id' => 3,
                ],
                '2' => [
                    'name' => 'test',
                ],
            ],
        ];

        $result = $validator->validate([
            '*.*.id'            => new IntegerRule(),
            'test_assoc.2.name' => 'string',
        ], $params);

        $this->assertEquals($result, $params);
    }

    public function createValidator()
    {
        return new Validator();
    }

    public function createValidatorMock()
    {
        return new ValidatorMock();
    }
}

class MockRule extends AbstractRule
{
    /**
     * @param mixed $value
     * @return bool
     */
    public function passes($value)
    {
        return true;
    }
}

class ValidatorMock extends Validator
{
    public function mockSortRules()
    {
        $this->sortRules();
    }

    public function getRules()
    {
        return $this->rules;
    }

    public function setRules(array $rules)
    {
        return $this->rules = $rules;
    }

    public function setParams(array $params)
    {
        return $this->params = $params;
    }

    /**
     * @param $array
     * @return array
     * @throws InvalidRule
     */
    public function getResolvedRules($array)
    {
        return $this->resolveRuleSet($array);
    }

    public function mockParseRuleSetArray($rule)
    {
        return $this->parseRuleSetArray($rule);
    }
}
