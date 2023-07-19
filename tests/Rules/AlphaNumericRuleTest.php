<?php

namespace Luur\Validator\Tests\Rules;

use Luur\Validator\Rules\Concrete\AlphaNumericRule;
use PHPUnit\Framework\TestCase;

class AlphaNumericRuleTest extends TestCase
{
    public function dataProvider()
    {
        return [
            [
                'Test',
                true,
            ],
            [
                '123',
                true,
            ],
            [
                123,
                true,
            ],
            [
                null,
                false,
            ],
            [
                '!test',
                false,
            ],
            [
                1.1,
                false,
            ],
            [
                '?!123',
                false,
            ],
            [
                'test123_',
                false,
            ],
            [
                'test123-',
                false,
            ],
            [
                '_',
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
        $this->assertEquals($expected, (new AlphaNumericRule())->passes($arg));
    }

    public function testGetsSlug()
    {
        $this->assertEquals('alpha_numeric', AlphaNumericRule::getSlug());
    }
}
