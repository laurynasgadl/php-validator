<?php


namespace Luur\Validator\Tests\Rules;


use Luur\Validator\Rules\Concrete\RegexRule;
use PHPUnit\Framework\TestCase;

class RegexRuleTest extends TestCase
{
    public function dataProvider()
    {
        return [
            [
                '/^[A-Za-z]+$/', 'Test', true
            ],
            [
                '/^[0-9]+$/', '123', true
            ],
            [
                '/^[0-9A-Za-z\-_]+$/', 'test123_', true
            ],
            [
                '/^_$/', '_', true
            ],
            [
                '/^[0-9]+$/', 123, true
            ],
            [
                '/^[0-9]+$/', null, false
            ],
            [
                '/^[a-z]+$/', '!test', false
            ],
            [
                '/^[0-9]+$/', 1.1, false
            ],
            [
                '/^[\-_]+$/', '?!123', false
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     * @param $pattern
     * @param $arg
     * @param $expected
     */
    public function testPassesOnExisting($pattern, $arg, $expected)
    {
        $this->assertEquals($expected, (new RegexRule($pattern))->passes($arg));
    }

    public function testGetsSlug()
    {
        $this->assertEquals('regex', RegexRule::getSlug());
    }
}
