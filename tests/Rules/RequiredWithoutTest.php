<?php

namespace Luur\Validator\Tests\Rules;

use Luur\Validator\Context;
use Luur\Validator\Rules\Concrete\RequiredWithoutRule;
use PHPUnit\Framework\TestCase;

class RequiredWithoutTest extends TestCase
{
    public function passingDataProvider()
    {
        return [
            [
                new Context([
                    'test1' => 1,
                    'test2' => 2,
                    'test3' => 3,
                ]),
                [
                    'test2',
                    'test3',
                ],
                'test1',
            ],
            [
                new Context([
                    'test1' => null,
                    'test2' => null,
                    'test3' => 3,
                ]),
                [
                    'test2',
                    'test3',
                ],
                'test1',
            ],
            [
                new Context([
                    'test1' => [
                        'test21' => 1,
                    ],
                ]),
                [
                    'test1.test22',
                    'test1.test23',
                ],
                'test1.test21',
            ],
        ];
    }

    public function nonPassingDataProvider()
    {
        return [
            [
                new Context([
                    'test1' => null,
                    'test2' => null,
                    'test3' => null,
                ]),
                [
                    'test2',
                    'test3',
                ],
                'test1',
            ],
            [
                new Context([
                    'test1' => [],
                ]),
                [
                    'test1.test22',
                    'test1.test23',
                ],
                'test1.test21',
            ],
        ];
    }

    /**
     * @dataProvider nonPassingDataProvider
     * @param Context $context
     * @param array $params
     * @param string $key
     */
    public function testDoesNotPassRule($context, $params, $key)
    {
        $rule = new RequiredWithoutRule(...$params);
        $rule->setContext($context);
        $this->assertFalse($rule->passesByKey($key));
    }

    /**
     * @dataProvider passingDataProvider
     * @param Context $context
     * @param array $params
     * @param string $key
     */
    public function testPassesRule($context, $params, $key)
    {
        $rule = new RequiredWithoutRule(...$params);
        $rule->setContext($context);
        $this->assertTrue($rule->passesByKey($key));
    }

    public function testGetsSlug()
    {
        $this->assertEquals('required_without', RequiredWithoutRule::getSlug());
    }
}
