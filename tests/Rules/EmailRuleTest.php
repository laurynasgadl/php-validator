<?php

namespace Luur\Validator\Tests\Rules;

use Luur\Validator\Rules\Concrete\EmailRule;
use PHPUnit\Framework\TestCase;

class EmailRuleTest extends TestCase
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
                'test@',
                false,
            ],
            [
                '@test',
                false,
            ],
            [
                '@test.com',
                false,
            ],
            [
                'test@test.com',
                true,
            ],
            [
                'test.test@test.com',
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
        $this->assertEquals($expected, (new EmailRule())->passes($arg));
    }

    public function testGetsSlug()
    {
        $this->assertEquals('email', EmailRule::getSlug());
    }
}
