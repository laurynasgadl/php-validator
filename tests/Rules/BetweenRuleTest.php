<?php

namespace Luur\Validator\Tests\Rules;

use Luur\Validator\Rules\Concrete\BetweenRule;
use PHPUnit\Framework\TestCase;

class BetweenRuleTest extends TestCase
{
    public function dataProvider()
    {
        return [
            [
                '12345678901',
                false,
            ],
            [
                11,
                false,
            ],
            [
                [
                    1,
                    2,
                    3,
                    4,
                    5,
                    6,
                    7,
                    8,
                    9,
                    0,
                    1,
                ],
                false,
            ],
            [
                11.0,
                false,
            ],
            [
                5.1,
                true,
            ],
            [
                '12345',
                true,
            ],
            [
                null,
                false,
            ],
            [
                true,
                false,
            ],
            [
                false,
                false,
            ],
            [
                1,
                false,
            ],
            [
                [1],
                false,
            ],
            [
                -10,
                false,
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     * @param $arg
     * @param $expected
     */
    public function testPassesOnExisting($arg, $expected)
    {
        $this->assertEquals($expected, (new BetweenRule(5, 10))->passes($arg));
    }

    public function testGetsSlug()
    {
        $this->assertEquals('between', BetweenRule::getSlug());
    }

    public function testGetsSignature()
    {
        $this->assertEquals('between:0,10', (new BetweenRule(0, 10))->getSignature());
    }
}
