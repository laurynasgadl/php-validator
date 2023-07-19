<?php

namespace Luur\Validator\Tests\Rules;

use Luur\Validator\Rules\Concrete\RequiredRule;
use PHPUnit\Framework\TestCase;

class RequiredRuleTest extends TestCase
{
    public function dataProvider()
    {
        return [
            [
                'test',
            ],
            [
                0,
            ],
            [
                1,
            ],
            [
                0.2,
            ],
            [
                true,
            ],
            [
                false,
            ],
            [
                [],
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     * @param $arg
     */
    public function testPassesOnExisting($arg)
    {
        $this->assertTrue((new RequiredRule())->passes($arg));
    }

    public function testFailsOnNonExisting()
    {
        $this->assertFalse((new RequiredRule())->passes(null));
    }

    public function testDoesntFailOnEmptyString()
    {
        $this->assertTrue((new RequiredRule())->passes(''));
    }

    public function testGetsSlug()
    {
        $this->assertEquals('required', RequiredRule::getSlug());
    }
}
