<?php


namespace Luur\Validator\Tests\Rules;


use Luur\Validator\Rules\Concrete\MaxRule;
use PHPUnit\Framework\TestCase;

class MaxRuleTest extends TestCase
{
    public function dataProvider()
    {
        return [
            [
                '1234567890', false
            ],
            [
                10, false
            ],
            [
                [
                    1,2,3,4,5,6,7,8,9,0
                ], false
            ],
            [
                10.0, false
            ],
            [
                1.1, true
            ],
            [
                '123', true
            ],
            [
                null, true
            ],
            [
                true, true
            ],
            [
                false, true
            ],
            [
                1, true
            ],
            [
                [1], true
            ],
            [
                -10, true
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
        $this->assertEquals($expected, (new MaxRule(5))->passes($arg));
    }

    public function testGetsSlug()
    {
        $this->assertEquals('max', MaxRule::getSlug());
    }
}
