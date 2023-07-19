<?php

namespace Luur\Validator\Tests\Rules;

use Luur\Validator\Rules\Concrete\BooleanRule;
use PHPUnit\Framework\TestCase;

class BooleanRuleTest extends TestCase
{
    public function dataProvider()
    {
        return [
            [
                0.0,
                false,
            ],
            [
                1.0,
                false,
            ],
            [
                false,
                true,
            ],
            [
                true,
                true,
            ],
            [
                1,
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
                [],
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
        $this->assertEquals($expected, (new BooleanRule())->passes($arg));
    }

    public function testGetsSlug()
    {
        $this->assertEquals('boolean', BooleanRule::getSlug());
    }
}
