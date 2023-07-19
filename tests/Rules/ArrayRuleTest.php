<?php

namespace Luur\Validator\Tests\Rules;

use Luur\Validator\Rules\Concrete\ArrayRule;
use PHPUnit\Framework\TestCase;

class ArrayRuleTest extends TestCase
{
    public function dataProvider()
    {
        return [
            [
                [],
                true,
            ],
            [
                null,
                false,
            ],
            [
                [1, 2, 3],
                true,
            ],
            [
                'test',
                false,
            ],
            [
                1.1,
                false,
            ],
            [
                123,
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
        $this->assertEquals($expected, (new ArrayRule())->passes($arg));
    }

    public function testGetsSlug()
    {
        $this->assertEquals('array', ArrayRule::getSlug());
    }
}
