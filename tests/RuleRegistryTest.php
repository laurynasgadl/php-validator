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
        self::assertTrue($registry instanceof RuleRegistry);
    }

    public function testRegistersRule()
    {
        $registry = new RuleRegistry();
        $registry->register(ArrayRule::getSlug(), ArrayRule::class);
        $rule = $registry->find(ArrayRule::getSlug());
        self::assertEquals(ArrayRule::class, $rule);
    }

    public function testThrowsExceptionOnSameSlug()
    {
        $slug = ArrayRule::getSlug();

        self::expectException(RuleRegistryException::class);
        self::expectExceptionMessage("Rule [$slug] already registered");
        self::expectExceptionCode(434);

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

        self::assertEquals(ArrayRule::class, $rule);
    }

    public function testThrowsExceptionOnUnregisteredRule()
    {
        $slug = 'test';

        self::expectException(InvalidRule::class);
        self::expectExceptionMessage("Invalid rule provided [{$slug}]");
        self::expectExceptionCode(431);

        $registry = new RuleRegistry();
        $registry->find($slug);
    }
}
