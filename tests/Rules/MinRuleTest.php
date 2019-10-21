<?php


namespace Luur\Validator\Tests\Rules;


use Luur\Validator\Rules\Concrete\MinRule;
use PHPUnit\Framework\TestCase;

class MinRuleTest extends TestCase
{
    public function dataProvider()
    {
        return [
            [
                '1234567890', true
            ],
            [
                10, true
            ],
            [
                [
                    1,2,3,4,5,6,7,8,9,0
                ], true
            ],
            [
                10.0, true
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
            [
                true, false
            ],
            [
                false, false
            ],
            [
                1, false
            ],
            [
                [1], false
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
        $this->assertEquals($expected, (new MinRule(5))->passes($arg));
    }

    public function testGetsSlug()
    {
        $this->assertEquals('min', MinRule::getSlug());
    }
}
