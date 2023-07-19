<?php

namespace Luur\Validator\Tests;

use Luur\Validator\Exceptions\InvalidRule;
use Luur\Validator\Exceptions\RuleRegistryException;
use Luur\Validator\Rules\Concrete\ArrayRule;
use Luur\Validator\Rules\Concrete\BetweenRule;
use Luur\Validator\Rules\RuleRegistry;
use PHPUnit\Framework\TestCase;

class RuleRegistryTest extends TestCase
{
    public function testBuildsRegistry()
    {
        $registry = new RuleRegistry();
        $this->assertInstanceOf(RuleRegistry::class, $registry);
    }

    public function testRegistersRule()
    {
        $registry = new RuleRegistry();
        $registry->register(ArrayRule::getSlug(), ArrayRule::class);
        $rule = $registry->find(ArrayRule::getSlug());
        $this->assertEquals(ArrayRule::class, $rule);
    }

    public function testThrowsExceptionOnSameSlug()
    {
        $slug = ArrayRule::getSlug();

        $this->expectException(RuleRegistryException::class);
        $this->expectExceptionMessage("Rule [$slug] already registered");
        $this->expectExceptionCode(434);

        $registry = new RuleRegistry();
        $registry->register($slug, ArrayRule::class);
        $registry->register($slug, BetweenRule::class);
    }

    public function testDoesntThrowExceptionOnDuplicate()
    {
        $slug = ArrayRule::getSlug();

        $registry = new RuleRegistry();
        $registry->register($slug, ArrayRule::class);
        $registry->register($slug, ArrayRule::class);
        $rule = $registry->find(ArrayRule::getSlug());

        $this->assertEquals(ArrayRule::class, $rule);
    }

    public function testThrowsExceptionOnUnregisteredRule()
    {
        $slug = 'test';

        $this->expectException(InvalidRule::class);
        $this->expectExceptionMessage("Invalid rule provided [{$slug}]");
        $this->expectExceptionCode(431);

        $registry = new RuleRegistry();
        $registry->find($slug);
    }
}
