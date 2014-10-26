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

use Eloquent\Phony\Matcher\WildcardMatcher;
use Eloquent\Phony\Mock\Builder\MockBuilder;
use Eloquent\Phony\Stub\Factory\StubFactory;
use Eloquent\Phony\Stub\Factory\StubVerifierFactory;
use PHPUnit_Framework_TestCase;

class VerificationProxyTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->stubs = (object) array();
        $this->isFull = true;
        $this->id = 'id';
        $this->stubFactory = new StubFactory();
        $this->stubVerifierFactory = new StubVerifierFactory();
        $this->wildcardMatcher = new WildcardMatcher();
    }

    protected function setUpWith($className)
    {
        $this->mockBuilder = new MockBuilder($className);
        $this->class = $this->mockBuilder->build(true);
        $this->mock = $this->mockBuilder->create();
        $this->subject = new VerificationProxy(
            $this->mock,
            $this->stubs,
            $this->isFull,
            $this->id,
            $this->stubFactory,
            $this->stubVerifierFactory,
            $this->wildcardMatcher
        );

        $this->className = $this->class->getName();
    }

    public function testConstructor()
    {
        $this->setUpWith('Eloquent\Phony\Test\TestClassB');

        $this->assertSame($this->mock, $this->subject->mock());
        $this->assertInstanceOf('ReflectionClass', $this->subject->clazz());
        $this->assertSame($this->className, $this->subject->clazz()->getName());
        $this->assertSame($this->className, $this->subject->className());
        $this->assertSame($this->stubs, $this->subject->stubs());
        $this->assertSame($this->isFull, $this->subject->isFull());
        $this->assertSame($this->id, $this->subject->id());
        $this->assertTrue($this->subject->hasParent());
        $this->assertTrue($this->subject->isMagic());
        $this->assertSame($this->stubFactory, $this->subject->stubFactory());
        $this->assertSame($this->stubVerifierFactory, $this->subject->stubVerifierFactory());
        $this->assertSame($this->wildcardMatcher, $this->subject->wildcardMatcher());
    }

    public function testConstructorDefaults()
    {
        $this->mockBuilder = new MockBuilder('Eloquent\Phony\Test\TestClassB');
        $this->class = $this->mockBuilder->build(true);
        $this->mock = $this->mockBuilder->create();
        $this->subject = new VerificationProxy($this->mock);

        $this->assertEquals((object) array(), $this->subject->stubs());
        $this->assertFalse($this->subject->isFull());
        $this->assertSame(StubFactory::instance(), $this->subject->stubFactory());
        $this->assertSame(StubVerifierFactory::instance(), $this->subject->stubVerifierFactory());
        $this->assertSame(WildcardMatcher::instance(), $this->subject->wildcardMatcher());
    }

    // public function testMagicStubs()
    // {
    //     $this->subject->nonexistentA;
    //     $this->subject->nonexistentB;

    //     $this->assertSame(array('nonexistentA', 'nonexistentB'), array_keys($this->subject->magicStubs()));
    // }

    // public function testStubMethods()
    // {
    //     $this->assertSame($this->stubs['testClassAMethodA'], $this->subject->stub('testClassAMethodA')->spy());
    //     $this->assertSame($this->stubs['testClassAMethodA'], $this->subject->testClassAMethodA->spy());
    //     $this->mock->testClassAMethodA('a', 'b');
    //     $this->assertSame($this->subject, $this->subject->testClassAMethodA('a', 'b'));
    // }

    // public function testStubFailure()
    // {
    //     $this->setUpWith('Eloquent\Phony\Test\TestClassA');

    //     $this->setExpectedException('Eloquent\Phony\Mock\Exception\UndefinedMethodStubException');
    //     $this->subject->stub('nonexistent');
    // }

    // public function testMagicPropertyFailure()
    // {
    //     $this->setUpWith('Eloquent\Phony\Test\TestClassA');

    //     $this->setExpectedException('Eloquent\Phony\Mock\Proxy\Exception\UndefinedPropertyException');
    //     $this->subject->nonexistent;
    // }

    // public function testMagicCallFailure()
    // {
    //     $this->setUpWith('Eloquent\Phony\Test\TestClassA');

    //     $this->setExpectedException('Eloquent\Phony\Mock\Proxy\Exception\UndefinedMethodException');
    //     $this->subject->nonexistent();
    // }

    // protected function expectedStubs(array $stubs)
    // {
    //     foreach ($stubs as $name => $stub) {
    //         $stubs[$name] = StubVerifierFactory::instance()->create($stub->callback(), $stub);
    //     }

    //     return $stubs;
    // }
}
