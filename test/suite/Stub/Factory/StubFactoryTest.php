<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Stub\Factory;

use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Stub\Stub;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class StubFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->labelSequencer = new Sequencer();
        $this->matcherFactory = new MatcherFactory();
        $this->matcherVerifier = new MatcherVerifier();
        $this->invoker = new Invoker();
        $this->invocableInspector = new InvocableInspector();
        $this->subject = new StubFactory(
            $this->labelSequencer,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->invoker,
            $this->invocableInspector
        );
    }

    public function testConstructor()
    {
        $this->assertSame($this->labelSequencer, $this->subject->labelSequencer());
        $this->assertSame($this->matcherFactory, $this->subject->matcherFactory());
        $this->assertSame($this->matcherVerifier, $this->subject->matcherVerifier());
        $this->assertSame($this->invoker, $this->subject->invoker());
        $this->assertSame($this->invocableInspector, $this->subject->invocableInspector());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new StubFactory();

        $this->assertSame(Sequencer::sequence('stub-label'), $this->subject->labelSequencer());
        $this->assertSame(MatcherFactory::instance(), $this->subject->matcherFactory());
        $this->assertSame(MatcherVerifier::instance(), $this->subject->matcherVerifier());
        $this->assertSame(Invoker::instance(), $this->subject->invoker());
        $this->assertSame(InvocableInspector::instance(), $this->subject->invocableInspector());
    }

    public function testCreate()
    {
        $callback = function () { return 'a'; };
        $self = (object) array();
        $defaultAnswerCallback = function ($stub) { $stub->forwards(); };
        $expected = new Stub(
            $callback,
            $self,
            0,
            $defaultAnswerCallback,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->invoker,
            $this->invocableInspector
        );
        $actual = $this->subject->create($callback, $self, $defaultAnswerCallback);

        $this->assertEquals($expected, $actual);
        $this->assertSame('a', call_user_func($actual->callback()));
        $this->assertSame($self, $actual->self());
        $this->assertSame($this->matcherFactory, $actual->matcherFactory());
        $this->assertSame($this->matcherVerifier, $actual->matcherVerifier());
        $this->assertSame($this->invoker, $actual->invoker());
        $this->assertSame($this->invocableInspector, $actual->invocableInspector());
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
