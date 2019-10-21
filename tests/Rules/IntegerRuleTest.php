<?php


namespace Luur\Validator\Tests\Rules;


use Luur\Validator\Rules\Concrete\IntegerRule;
use PHPUnit\Framework\TestCase;

class IntegerRuleTest extends TestCase
{
    public function dataProvider()
    {
        return [
            [
                0, true
            ],
            [
                1, true
            ],
            [
                false, false
            ],
            [
                true, false
            ],
            [
                1.1, false
            ],
            [
                '123', false
            ],
            [
                null, false
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
        $this->assertEquals($expected, (new IntegerRule())->passes($arg));
    }

    public function testGetsSlug()
    {
        $this->assertEquals('integer', IntegerRule::getSlug());
    }
}
