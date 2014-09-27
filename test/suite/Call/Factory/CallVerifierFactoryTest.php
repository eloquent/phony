<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Factory;

use Eloquent\Phony\Assertion\AssertionRecorder;
use Eloquent\Phony\Call\Call;
use Eloquent\Phony\Call\CallVerifier;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use SebastianBergmann\Exporter\Exporter;

class CallVerifierFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->matcherFactory = new MatcherFactory();
        $this->matcherVerifier = new MatcherVerifier();
        $this->assertionRecorder = new AssertionRecorder();
        $this->exporter = new Exporter();
        $this->subject = new CallVerifierFactory(
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->assertionRecorder,
            $this->exporter
        );

        $this->callA = new Call(array(), null, 0, 1.11, 2.22);
        $this->callB = new Call(array(), null, 1, 3.33, 4.44);
    }

    public function testConstructor()
    {
        $this->assertSame($this->matcherFactory, $this->subject->matcherFactory());
        $this->assertSame($this->matcherVerifier, $this->subject->matcherVerifier());
        $this->assertSame($this->assertionRecorder, $this->subject->assertionRecorder());
        $this->assertSame($this->exporter, $this->subject->exporter());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new CallVerifierFactory();

        $this->assertSame(MatcherFactory::instance(), $this->subject->matcherFactory());
        $this->assertSame(MatcherVerifier::instance(), $this->subject->matcherVerifier());
        $this->assertSame(AssertionRecorder::instance(), $this->subject->assertionRecorder());
        $this->assertEquals($this->exporter, $this->subject->exporter());
    }

    public function testAdapt()
    {
        $verifier = new CallVerifier($this->callA);
        $adaptedCall = $this->subject->adapt($this->callA);

        $this->assertSame($verifier, $this->subject->adapt($verifier));
        $this->assertNotSame($verifier, $adaptedCall);
        $this->assertEquals($verifier, $adaptedCall);
        $this->assertSame($this->matcherFactory, $adaptedCall->matcherFactory());
        $this->assertSame($this->matcherVerifier, $adaptedCall->matcherVerifier());
        $this->assertSame($this->assertionRecorder, $adaptedCall->assertionRecorder());
        $this->assertSame($this->exporter, $adaptedCall->exporter());
    }

    public function testAdaptAll()
    {
        $callBVerifier = new CallVerifier($this->callB);
        $calls = array($this->callA, $callBVerifier);
        $actual = $this->subject->adaptAll($calls);
        $expected = array(new CallVerifier($this->callA), $callBVerifier);

        $this->assertEquals($expected, $actual);
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
