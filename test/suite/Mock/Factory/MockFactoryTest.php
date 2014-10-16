<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Factory;

use Eloquent\Phony\Mock\Builder\MockBuilder;
use Eloquent\Phony\Stub\Factory\StubVerifierFactory;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class MockFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->stubVerifierFactory = new StubVerifierFactory();
        $this->subject = new MockFactory($this->stubVerifierFactory);
    }

    public function testConstructor()
    {
        $this->assertSame($this->stubVerifierFactory, $this->subject->stubVerifierFactory());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new MockFactory();

        $this->assertSame(StubVerifierFactory::instance(), $this->subject->stubVerifierFactory());
    }

    public function testCreateMock()
    {
        $builder = new MockBuilder(
            'Eloquent\Phony\Test\TestClassB',
            null,
            __NAMESPACE__ . '\PhonyMockFactoryTestCreateMock'
        );
        $actual = $this->subject->createMock($builder);
        $class = new ReflectionClass($actual);
        $protectedMethod = $class->getMethod('testClassAMethodC');
        $protectedMethod->setAccessible(true);

        $this->assertInstanceOf('Eloquent\Phony\Mock\MockInterface', $actual);
        $this->assertInstanceOf('Eloquent\Phony\Test\TestClassB', $actual);
        $this->assertNull($actual->testClassAMethodA('a', 'b'));
        $this->assertNull($protectedMethod->invoke($actual, 'a', 'b'));
        // $this->assertSame('ab', PhonyMockFactoryTestCreateMock::testClassAStaticMethodA());
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
