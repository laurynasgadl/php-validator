<?php


namespace Luur\Validator\Tests\Rules;


use Luur\Validator\Context;
use Luur\Validator\Rules\Concrete\DefaultRule;
use PHPUnit\Framework\TestCase;

class DefaultRuleTest extends TestCase
{
    protected $context;

    protected function setUp()
    {
        parent::setUp();
        $this->context = new Context([
            'test1' => null,
            'test2' => [],
        ]);
    }

    public function testSetsDefaultOnNull()
    {
        $defaultValue = 123.123;
        $rule         = new DefaultRule($defaultValue);
        $rule->setContext($this->context);

        $this->assertTrue($rule->passes('test1'));
        $this->assertEquals($defaultValue, $this->context->get('test1'));
    }

    public function testSetsDefaultOnNonSet()
    {
        $defaultValue = 123.123;
        $rule         = new DefaultRule($defaultValue);
        $rule->setContext($this->context);

        $this->assertTrue($rule->passes('test2.test1'));
        $this->assertEquals($defaultValue, $this->context->get('test2.test1'));
    }

    public function testGetsSlug()
    {
        $this->assertEquals('default', DefaultRule::getSlug());
    }

    public function testGetsSignature()
    {
        $this->assertEquals('default:test', (new DefaultRule('test'))->getSignature());
    }
}
