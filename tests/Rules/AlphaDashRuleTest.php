<?php

namespace Luur\Validator\Tests\Rules;

use Luur\Validator\Rules\Concrete\AlphaDashRule;
use PHPUnit\Framework\TestCase;

class AlphaDashRuleTest extends TestCase
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
                'test123_',
                true,
            ],
            [
                '_',
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
        ];
    }

    /**
     * @dataProvider dataProvider
     * @param $arg
     * @param $expected
     */
    public function testPassesOnExisting($arg, $expected)
    {
        $this->assertEquals($expected, (new AlphaDashRule())->passes($arg));
    }

    public function testGetsSlug()
    {
        $this->assertEquals('alpha_dash', AlphaDashRule::getSlug());
    }
}
