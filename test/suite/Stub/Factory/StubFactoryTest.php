<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Stub\Factory;

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
        $this->idSequencer = new Sequencer();
        $this->matcherFactory = new MatcherFactory();
        $this->matcherVerifier = new MatcherVerifier();
        $this->invoker = new Invoker();
        $this->subject =
            new StubFactory($this->idSequencer, $this->matcherFactory, $this->matcherVerifier, $this->invoker);
    }

    public function testConstructor()
    {
        $this->assertSame($this->idSequencer, $this->subject->idSequencer());
        $this->assertSame($this->matcherFactory, $this->subject->matcherFactory());
        $this->assertSame($this->matcherVerifier, $this->subject->matcherVerifier());
        $this->assertSame($this->invoker, $this->subject->invoker());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new StubFactory();

        $this->assertEquals($this->idSequencer, $this->subject->idSequencer());
        $this->assertSame(MatcherFactory::instance(), $this->subject->matcherFactory());
        $this->assertSame(MatcherVerifier::instance(), $this->subject->matcherVerifier());
        $this->assertSame(Invoker::instance(), $this->subject->invoker());
    }

    public function testCreate()
    {
        $callback = function () {};
        $self = (object) array();
        $expected = new Stub($callback, $self, 0, $this->matcherFactory, $this->matcherVerifier);
        $actual = $this->subject->create($callback, $self);

        $this->assertEquals($expected, $actual);
        $this->assertSame($callback, $actual->callback());
        $this->assertSame($self, $actual->self());
        $this->assertSame($this->matcherFactory, $actual->matcherFactory());
        $this->assertSame($this->matcherVerifier, $actual->matcherVerifier());
        $this->assertSame($this->invoker, $actual->invoker());
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
