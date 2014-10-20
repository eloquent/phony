<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Proxy\Verification;

use Eloquent\Phony\Mock\Builder\MockBuilder;
use Eloquent\Phony\Stub\Factory\StubVerifierFactory;
use PHPUnit_Framework_TestCase;
use ReflectionProperty;

class VerificationProxyTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassA');
        $this->mock = $this->mockBuilder->create();
        $property = new ReflectionProperty($this->mock, '_stubs');
        $property->setAccessible(true);
        $this->stubs = $this->expectedStubs($property->getValue($this->mock));
        $this->subject = new VerificationProxy($this->mock, $this->stubs);

        $this->className = $this->mockBuilder->className();
    }

    public function testConstructor()
    {
        $this->assertSame($this->mock, $this->subject->mock());
        $this->assertSame($this->className, $this->subject->className());
        $this->assertSame($this->stubs, $this->subject->stubs());
    }

    public function testStubMethods()
    {
        $this->assertSame($this->stubs['testClassAMethodA'], $this->subject->stub('testClassAMethodA'));
        $this->assertSame($this->stubs['testClassAMethodA'], $this->subject->testClassAMethodA);
        $this->assertSame('ab', $this->mock->testClassAMethodA('a', 'b'));
        $this->assertInstanceOf(
            'Eloquent\Phony\Call\Event\CallEventCollection',
            $this->subject->testClassAMethodA('a', 'b')
        );
    }

    public function testStubFailure()
    {
        $this->setExpectedException('Eloquent\Phony\Mock\Exception\UndefinedMethodStubException');
        $this->subject->stub('nonexistent');
    }

    public function testMagicPropertyFailure()
    {
        $this->setExpectedException('Eloquent\Phony\Mock\Proxy\Exception\UndefinedPropertyException');
        $this->subject->nonexistent;
    }

    public function testMagicCallFailure()
    {
        $this->setExpectedException('Eloquent\Phony\Mock\Proxy\Exception\UndefinedMethodException');
        $this->subject->nonexistent();
    }

    protected function expectedStubs(array $stubs)
    {
        foreach ($stubs as $name => $stub) {
            $stubs[$name] = StubVerifierFactory::instance()->create($stub->callback(), $stub);
        }

        return $stubs;
    }
}
