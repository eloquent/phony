<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Spy\Factory;

use Eloquent\Phony\Call\Factory\CallVerifierFactory;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Spy\Spy;
use Eloquent\Phony\Spy\SpyVerifier;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class SpyVerifierFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->matcherFactory = new MatcherFactory();
        $this->matcherVerifier = new MatcherVerifier();
        $this->callVerifierFactory = new CallVerifierFactory();
        $this->spyFactory = new SpyFactory();
        $this->subject = new SpyVerifierFactory(
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->callVerifierFactory,
            $this->spyFactory
        );
    }

    public function testConstructor()
    {
        $this->assertSame($this->matcherFactory, $this->subject->matcherFactory());
        $this->assertSame($this->matcherVerifier, $this->subject->matcherVerifier());
        $this->assertSame($this->callVerifierFactory, $this->subject->callVerifierFactory());
        $this->assertSame($this->spyFactory, $this->subject->spyFactory());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new SpyVerifierFactory();

        $this->assertSame(MatcherFactory::instance(), $this->subject->matcherFactory());
        $this->assertSame(MatcherVerifier::instance(), $this->subject->matcherVerifier());
        $this->assertSame(CallVerifierFactory::instance(), $this->subject->callVerifierFactory());
        $this->assertSame(SpyFactory::instance(), $this->subject->spyFactory());
    }

    public function testCreate()
    {
        $spy = new Spy();
        $expected = new SpyVerifier($spy, $this->matcherFactory, $this->matcherVerifier, $this->callVerifierFactory);
        $actual = $this->subject->create($spy);

        $this->assertEquals($expected, $actual);
        $this->assertSame($spy, $actual->spy());
        $this->assertSame($this->matcherFactory, $actual->matcherFactory());
        $this->assertSame($this->matcherVerifier, $actual->matcherVerifier());
        $this->assertSame($this->callVerifierFactory, $actual->callVerifierFactory());
    }

    public function testCreateDefaults()
    {
        $spy = new Spy();
        $expected = new SpyVerifier($spy, $this->matcherFactory, $this->matcherVerifier, $this->callVerifierFactory);
        $actual = $this->subject->create();

        $this->assertEquals($expected, $actual);
        $this->assertEquals($spy, $actual->spy());
        $this->assertSame($this->matcherFactory, $actual->matcherFactory());
        $this->assertSame($this->matcherVerifier, $actual->matcherVerifier());
        $this->assertSame($this->callVerifierFactory, $actual->callVerifierFactory());
    }

    public function testCreateFromSubject()
    {
        $subject = function () {};
        $spy = new Spy($subject);
        $expected = new SpyVerifier($spy, $this->matcherFactory, $this->matcherVerifier, $this->callVerifierFactory);
        $actual = $this->subject->createFromSubject($subject);

        $this->assertEquals($expected, $actual);
        $this->assertEquals($spy, $actual->spy());
        $this->assertSame($this->matcherFactory, $actual->matcherFactory());
        $this->assertSame($this->matcherVerifier, $actual->matcherVerifier());
        $this->assertSame($this->callVerifierFactory, $actual->callVerifierFactory());
    }

    public function testCreateFromSubjectDefaults()
    {
        $spy = new Spy();
        $expected = new SpyVerifier($spy, $this->matcherFactory, $this->matcherVerifier, $this->callVerifierFactory);
        $actual = $this->subject->createFromSubject();

        $this->assertEquals($expected, $actual);
        $this->assertEquals($spy, $actual->spy());
        $this->assertSame($this->matcherFactory, $actual->matcherFactory());
        $this->assertSame($this->matcherVerifier, $actual->matcherVerifier());
        $this->assertSame($this->callVerifierFactory, $actual->callVerifierFactory());
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
