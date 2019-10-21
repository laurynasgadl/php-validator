<?php

namespace Luur\Validator\Tests\Rules;

use Luur\Validator\Context;
use Luur\Validator\Exceptions\InvalidRule;
use Luur\Validator\Rules\AbstractRule;
use Luur\Validator\Rules\RuleFactory;
use PHPUnit\Framework\TestCase;

class RuleFactoryTest extends TestCase
{
    public function testBuildsValidRule()
    {
        $rule = RuleFactory::build(new Context(), 'required');
        $this->assertTrue($rule instanceof AbstractRule);
    }

    public function testThrowsInvalidRuleException()
    {
        $this->expectException(InvalidRule::class);
        RuleFactory::build(new Context(), 'test');
    }
}
