<?php

namespace Luur\Validator\Tests;

use Luur\Validator\Context;
use Luur\Validator\ContextInterface;
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

        $rules = [
            'test.test'      => 'test',
            'test1'          => 'test',
            'test2'          => 'test',
            'test.test.test' => 'test',
            'test3'          => 'test',
        ];

        $sorted = $validator->mockSortRules($rules);

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

        $context = new Context();
        $context->setParams($params);
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
        $validator = $this->createValidator();
        $validator->validate([
            'test' => 'integer|required',
        ], ['test' => 'test']);
    }

    public function testReturnsFailingValidation()
    {
        $this->expectException(ValidationFailed::class);
        $this->expectExceptionMessage('test failed [integer] rule validation');

        $validator = $this->createValidator();
        $validator->validate([
            'test' => 'integer|required',
        ], ['test' => 'test']);
    }

    public function testValidatesArrayParams()
    {
        $validator = $this->createValidator();

        $params = [
            'test' => [
                'test'  => -10,
                'test1' => 'test',
                'test2' => 1.1,
            ],
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
            ],
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
                '3' => [],
            ],
        ];

        $result = $validator->validate([
            '*.*.id'            => new IntegerRule(),
            'test_assoc.2.name' => 'string',
            '*.2'               => 'array',
        ], $params);

        $this->assertEquals($result, $params);
    }

    public function testValidatesEmptyParamsWithRequiredRule()
    {
        $this->expectException(ValidationFailed::class);

        $validator = $this->createValidator();
        $validator->validate([
            'test'  => 'required|integer',
            'test2' => 'required|string',
            'test3' => 'required|float',
        ], []);
    }

    public function testSetsDefaultContextHandler()
    {
        $validator = new ValidatorMock();
        $this->assertTrue($validator->getContextHandler() instanceof Context);
    }

    public function testSetsMockContextHandler()
    {
        $validator = new ValidatorMock(null, new ContextMock());
        $this->assertTrue($validator->getContextHandler() instanceof ContextMock);
    }

    public function testValidatesNestedValues()
    {
        $rules = [
            'arg_3'         => 'required|array',
            'arg_5'         => 'required|array',
            'arg_5.arg_3'   => 'string',
            'arg_1'         => 'required|array',
            'arg_1.*.arg_1' => 'string',
        ];

        $params = [
            'arg_1' => [],
            'arg_3' => ['test'],
            'arg_5' => [],
        ];

        $validator = new Validator();
        $result    = $validator->validate($rules, $params);

        $this->assertEquals($result, $params);
    }

    public function testValidatesNestedEmptyValues()
    {
        $this->expectException(ValidationFailed::class);
        $this->expectExceptionMessage('arg_2.*.arg_1 failed [required] rule validation');

        $rules = [
            'arg_2.*.arg_1' => 'required|string',
        ];

        $params = [
            'arg_2' => [
                [],
            ],
        ];

        $validator = new Validator();
        $validator->validate($rules, $params);
    }

    public function testSkipsNestedRequiredParams()
    {
        $rules  = [
            'arg_1'       => 'required|array',
            'arg_1.arg_1' => 'integer',
            'arg_2'       => 'array',
            'arg_2.arg_1' => 'required|string',
            'arg_3'       => 'array',
            'arg_3.arg_1' => 'required|string',
        ];
        $params = [
            'arg_1' => [
                'arg_1' => 1,
            ],
            'arg_3' => [
                'arg_1' => 'test',
            ],
        ];

        $validator = new Validator();
        $result    = $validator->validate($rules, $params);
        $this->assertEquals($params, $result);
    }

    public function testValidatesRequiredWithoutRule()
    {
        $rules  = [
            'arg_1' => 'required_without:arg_2,arg_3|string',
        ];
        $params = [
            'arg_2' => null,
            'arg_3' => 'test',
        ];

        $validator = new Validator();
        $result    = $validator->validate($rules, $params);
        $this->assertEquals($params, $result);
    }

    public function testThrowsRequiredWithoutRuleException()
    {
        $this->expectException(ValidationFailed::class);
        $this->expectExceptionMessage('arg_1 failed [required_without:arg_2,arg_3] rule validation');

        $rules  = [
            'arg_1' => 'required_without:arg_2,arg_3',
        ];
        $params = [
            'arg_3' => null,
        ];

        $validator = new Validator();
        $validator->validate($rules, $params);
    }

    public function testValidatesRequiredWithRule()
    {
        $rules  = [
            'arg_1' => 'required_with:arg_2,arg_3|string',
        ];
        $params = [
            'arg_2' => null,
            'arg_3' => 'test',
        ];

        $validator = new Validator();
        $result    = $validator->validate($rules, $params);
        $this->assertEquals($params, $result);
    }

    public function testThrowsRequiredWithRuleException()
    {
        $this->expectException(ValidationFailed::class);
        $this->expectExceptionMessage('arg_1 failed [required_with:arg_2,arg_3] rule validation');

        $rules  = [
            'arg_1' => 'required_with:arg_2,arg_3',
        ];
        $params = [
            'arg_2' => 2,
            'arg_3' => 3,
        ];

        $validator = new Validator();
        $validator->validate($rules, $params);
    }

    public function testReturnsCustomValidationMessageSetFromConstructor()
    {
        $message  = 'Option key should be a string';
        $rules    = ['options.*.key' => 'required|string'];
        $messages = ['options.*.key.string' => $message];

        $params = [
            'options' => [
                [
                    'key' => 'passes',
                    'value' => true,
                ],
                [
                    'key' => ['passes'],
                    'value' => false,
                ],
            ],
        ];

        self::expectException(ValidationFailed::class);
        self::expectExceptionMessage($message);

        $validator = new Validator($messages);
        $validator->validate($rules, $params);
    }

    public function testReturnsCustomValidationMessageSetFromMethod()
    {
        $message  = 'Option key should be a string';
        $rules    = ['options.*.key' => 'required|string'];
        $messages = ['options.*.key.string' => $message];

        $params = [
            'options' => [
                [
                    'key' => 'passes',
                    'value' => true,
                ],
                [
                    'key' => ['passes'],
                    'value' => false,
                ],
            ],
        ];

        self::expectException(ValidationFailed::class);
        self::expectExceptionMessage($message);

        $validator = new Validator();
        $validator->validate($rules, $params, $messages);
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

class ContextMock implements ContextInterface
{
    /**
     * @param array $params
     */
    public function setParams($params)
    {
        echo 'Setting params';
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return $key;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function set($key, $value)
    {
        return $value;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [];
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
    public function mockSortRules($rules)
    {
        return $this->sortRules($rules);
    }

    public function getRules()
    {
        return $this->rules;
    }

    public function setRules($rules)
    {
        return $this->rules = $rules;
    }

    public function setParams($params)
    {
        $this->contextHandler->setParams($params);
    }

    public function mockParseRuleSetArray($rule)
    {
        return $this->parseRuleSetArray($rule);
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

    /**
     * @return ContextInterface
     */
    public function getContextHandler()
    {
        return $this->contextHandler;
    }
}
