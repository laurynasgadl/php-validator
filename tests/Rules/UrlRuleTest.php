<?php


namespace Luur\Validator\Tests\Rules;


use Luur\Validator\Rules\Concrete\UrlRule;
use PHPUnit\Framework\TestCase;

class UrlRuleTest extends TestCase
{
    public function dataProvider()
    {
        return [
            [
                0.0, false
            ],
            [
                1.0, false
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
            [
                'test', false
            ],
            [
                'www.test', false
            ],
            [
                'test.com', false
            ],
            [
                'http://test', true
            ],
            [
                'http://test.com', true
            ],
            [
                'https://www.test.com/api/test?test=true', true
            ],
            [
                'https://www.test.com api/test?test=true', false
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
        $this->assertEquals($expected, (new UrlRule())->passes($arg));
    }

    public function testGetsSlug()
    {
        $this->assertEquals('url', UrlRule::getSlug());
    }
}
