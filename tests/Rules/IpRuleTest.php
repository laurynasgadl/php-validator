<?php

namespace Luur\Validator\Tests\Rules;

use Luur\Validator\Rules\Concrete\IpRule;
use PHPUnit\Framework\TestCase;

class IpRuleTest extends TestCase
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
                false,
            ],
            [
                true,
                false,
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
            [
                'test',
                false,
            ],
            [
                'www.test',
                false,
            ],
            [
                '123.123',
                false,
            ],
            [
                '1.1.1.1',
                true,
            ],
            [
                '127.0.0.1',
                true,
            ],
            [
                '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
                true,
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
        $this->assertEquals($expected, (new IpRule())->passes($arg));
    }

    public function testGetsSlug()
    {
        $this->assertEquals('ip', IpRule::getSlug());
    }
}
