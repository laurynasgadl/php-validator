<?php


namespace Luur\Validator\Tests\Rules;


use Luur\Validator\Rules\Concrete\StringRule;
use PHPUnit\Framework\TestCase;

class StringRuleTest extends TestCase
{
    public function dataProvider()
    {
        return [
            [
                'test', true
            ],
            [
                null, false
            ],
            [
                '123', true
            ],
            [
                [], false
            ],
            [
                1.1, false
            ],
            [
                123, false
            ],
            [
                0, false
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
        $this->assertEquals($expected, (new StringRule())->passes($arg));
    }

    public function testGetsSlug()
    {
        $this->assertEquals('string', StringRule::getSlug());
    }
}
