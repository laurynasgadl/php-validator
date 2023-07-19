<?php

namespace Luur\Validator\Tests\Rules;

use Luur\Validator\Rules\Concrete\SizeRule;
use PHPUnit\Framework\TestCase;

class SizeRuleTest extends TestCase
{
    public function dataProvider()
    {
        return [
            [
                '1234567890',
                true,
            ],
            [
                10,
                true,
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
                ],
                true,
            ],
            [
                10.0,
                true,
            ],
            [
                1.1,
                false,
            ],
            [
                '123',
                false,
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
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 0, 1, 2, 3, 4, 5],
                false,
            ],
            [
                15,
                false,
            ],
            [
                '123456789012345',
                false,
            ],
            [
                15.5,
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
        $this->assertEquals($expected, (new SizeRule(10))->passes($arg));
    }

    public function testGetsSlug()
    {
        $this->assertEquals('size', SizeRule::getSlug());
    }
}
