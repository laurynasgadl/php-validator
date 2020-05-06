<?php


namespace Luur\Validator\Tests\Rules;


use Luur\Validator\Rules\Concrete\NumericRule;
use PHPUnit\Framework\TestCase;

class NumericRuleTest extends TestCase
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
                1.1, true
            ],
            [
                '123', true
            ],
            [
                'abcd', false
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
        $this->assertEquals($expected, (new NumericRule())->passes($arg));
    }

    public function testGetsSlug()
    {
        $this->assertEquals('numeric', NumericRule::getSlug());
    }
}
