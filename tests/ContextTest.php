<?php

namespace Luur\Validator\Tests;

use Luur\Validator\Context;
use PHPUnit\Framework\TestCase;

class ContextTest extends TestCase
{
    public function testCreatesContext()
    {
        $context = new Context();
        $this->assertInstanceOf(Context::class, $context);
    }

    public function testGetsValueByKey()
    {
        $context = new Context([
            'test' => 123,
        ]);
        $this->assertEquals(123, $context->get('test'));
    }

    public function testSetsValueByKey()
    {
        $context = new Context([
            'test' => 123,
        ]);
        $context->set('test', 321);
        $this->assertEquals(321, $context->get('test'));
    }

    public function testGetsArray()
    {
        $context = new Context([
            'test' => 123,
        ]);
        $context->set('test', 321);
        $this->assertEquals([
            'test' => 321,
        ], $context->toArray());
    }

    public function testSetsParams()
    {
        $context = new Context();
        $context->setParams([
            'test' => 123,
        ]);
        $this->assertEquals([
            'test' => 123,
        ], $context->toArray());
    }
}
