<?php

declare(strict_types=1);

namespace Eloquent\Phony\Matcher;

use Eloquent\Phony\Matcher\Exception\UndefinedTypeException;
use Eloquent\Phony\Test\TestClassA;
use Eloquent\Phony\Test\TestClassB;
use Eloquent\Phony\Test\TestInterfaceA;
use PHPUnit\Framework\TestCase;

class InstanceOfMatcherTest extends TestCase
{
    public function testConstructor()
    {
        $subject = new InstanceOfMatcher(TestInterfaceA::class);

        $this->assertSame(TestInterfaceA::class, $subject->type());
    }

    public function testMatchesWithInterface()
    {
        $subject = new InstanceOfMatcher(TestInterfaceA::class);

        $this->assertTrue($subject->matches(new TestClassA()));
        $this->assertTrue($subject->matches(new TestClassB()));
        $this->assertFalse($subject->matches((object) []));
        $this->assertFalse($subject->matches(false));
    }

    public function testMatchesWithClass()
    {
        $subject = new InstanceOfMatcher(TestClassA::class);

        $this->assertTrue($subject->matches(new TestClassA()));
        $this->assertTrue($subject->matches(new TestClassB()));
        $this->assertFalse($subject->matches((object) []));
        $this->assertFalse($subject->matches(false));
    }

    public function testMatchesFailureUndefinedType()
    {
        $subject = new InstanceOfMatcher(Undefined::class);

        $this->expectException(UndefinedTypeException::class);
        $this->expectExceptionMessage("Undefined type 'Eloquent\\\\Phony\\\\Matcher\\\\Undefined'.");
        $subject->matches(null);
    }

    public function testDescribe()
    {
        $subject = new InstanceOfMatcher(TestInterfaceA::class);

        $this->assertSame('<instanceof TestInterfaceA>', $subject->describe());
        $this->assertSame('<instanceof TestInterfaceA>', strval($subject));
    }
}
