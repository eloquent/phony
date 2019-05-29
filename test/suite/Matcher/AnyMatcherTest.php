<?php

declare(strict_types=1);

namespace Eloquent\Phony\Matcher;

use PHPUnit\Framework\TestCase;
use ReflectionClass;

class AnyMatcherTest extends TestCase
{
    protected function setUp(): void
    {
        $this->subject = new AnyMatcher();
    }

    public function testConstructor()
    {
        $this->assertSame('<any>', $this->subject->describe());
        $this->assertSame('<any>', strval($this->subject));
    }

    public function testMatches()
    {
        $this->assertTrue($this->subject->matches('x'));
        $this->assertTrue($this->subject->matches('y'));
    }

    public function testInstance()
    {
        $class = get_class($this->subject);
        $reflector = new ReflectionClass($class);
        $property = $reflector->getProperty('instance');
        $property->setAccessible(true);
        $property->setValue(null, null);
        $instance = $class::instance();

        $this->assertInstanceOf($class, $instance);
        $this->assertSame($instance, $class::instance());
    }
}
