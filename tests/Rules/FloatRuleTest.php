<?php


namespace Luur\Validator\Tests\Rules;


use Luur\Validator\Rules\Concrete\FloatRule;
use PHPUnit\Framework\TestCase;

class FloatRuleTest extends TestCase
{
    public function dataProvider()
    {
        return [
            [
                0.0, true
            ],
            [
                1.0, true
            ],
            [
                false, false
            ],
            [
                true, false
            ],
            [
                1, false
            ],
            [
                '123', false
            ],
            [
                null, false
            ],
            [
                [], false
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
        $this->assertEquals($expected, (new FloatRule())->passes($arg));
    }

    public function testGetsSlug()
    {
        $this->assertEquals('float', FloatRule::getSlug());
    }
}
