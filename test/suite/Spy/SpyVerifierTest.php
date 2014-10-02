<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Spy;

use Eloquent\Phony\Assertion\AssertionRecorder;
use Eloquent\Phony\Assertion\Renderer\AssertionRenderer;
use Eloquent\Phony\Call\Call;
use Eloquent\Phony\Call\Factory\CallVerifierFactory;
use Eloquent\Phony\Matcher\EqualToMatcher;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Test\TestAssertionRecorder;
use Eloquent\Phony\Test\TestCallFactory;
use Eloquent\Phony\Test\TestClass;
use Exception;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionFunction;
use RuntimeException;

class SpyVerifierTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->spySubject = 'implode';
        $this->callFactory = new TestCallFactory();
        $this->spy = new Spy($this->spySubject, $this->callFactory);

        $this->matcherFactory = new MatcherFactory();
        $this->matcherVerifier = new MatcherVerifier();
        $this->callVerifierFactory = new CallVerifierFactory();
        $this->assertionRecorder = new TestAssertionRecorder();
        $this->assertionRenderer = new AssertionRenderer();
        $this->subject = new SpyVerifier(
            $this->spy,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer
        );

        $this->returnValueA = 'x';
        $this->returnValueB = 'y';
        $this->exceptionA = new RuntimeException('You done goofed.');
        $this->exceptionB = new RuntimeException('Consequences will never be the same.');
        $this->thisValueA = new TestClass();
        $this->thisValueB = new TestClass();
        $this->arguments = array('a', 'b', 'c');
        $this->argumentMatchers = $this->matcherFactory->adaptAll($this->arguments);
        $this->callA = $this->callFactory->create(
            array(
                $this->callFactory->createCalledEvent(array($this->thisValueA, 'methodA'), $this->arguments),
                $this->callFactory->createReturnedEvent($this->returnValueA),
            )
        );
        $this->callB = $this->callFactory->create(
            array(
                $this->callFactory->createCalledEvent(array($this->thisValueB, 'methodA')),
                $this->callFactory->createReturnedEvent($this->returnValueB),
            )
        );
        $this->callC = $this->callFactory->create(
            array(
                $this->callFactory->createCalledEvent(array($this->thisValueA, 'methodA'), $this->arguments),
                $this->callFactory->createThrewEvent($this->exceptionA),
            )
        );
        $this->callD = $this->callFactory->create(
            array(
                $this->callFactory->createCalledEvent('implode'),
                $this->callFactory->createThrewEvent($this->exceptionB),
            )
        );
        $this->calls = array($this->callA, $this->callB, $this->callC, $this->callD);
        $this->wrappedCallA = $this->callVerifierFactory->adapt($this->callA);
        $this->wrappedCallB = $this->callVerifierFactory->adapt($this->callB);
        $this->wrappedCallC = $this->callVerifierFactory->adapt($this->callC);
        $this->wrappedCallD = $this->callVerifierFactory->adapt($this->callD);
        $this->wrappedCalls = array($this->wrappedCallA, $this->wrappedCallB, $this->wrappedCallC, $this->wrappedCallD);

        $this->callFactory->sequencer()->reset();
        $this->callFactory->clock()->reset();
    }

    public function testConstructor()
    {
        $this->assertSame($this->spy, $this->subject->spy());
        $this->assertSame($this->matcherFactory, $this->subject->matcherFactory());
        $this->assertSame($this->matcherVerifier, $this->subject->matcherVerifier());
        $this->assertSame($this->callVerifierFactory, $this->subject->callVerifierFactory());
        $this->assertSame($this->assertionRecorder, $this->subject->assertionRecorder());
        $this->assertSame($this->assertionRenderer, $this->subject->assertionRenderer());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new SpyVerifier();

        $this->assertEquals(new Spy(), $this->subject->spy());
        $this->assertSame(MatcherFactory::instance(), $this->subject->matcherFactory());
        $this->assertSame(MatcherVerifier::instance(), $this->subject->matcherVerifier());
        $this->assertSame(CallVerifierFactory::instance(), $this->subject->callVerifierFactory());
        $this->assertSame(AssertionRecorder::instance(), $this->subject->assertionRecorder());
        $this->assertSame(AssertionRenderer::instance(), $this->subject->assertionRenderer());
    }

    public function testProxyMethods()
    {
        $this->assertSame($this->spySubject, $this->subject->subject());
    }

    public function testSetCalls()
    {
        $this->subject->setCalls($this->calls);

        $this->assertSame($this->calls, $this->subject->spy()->calls());
    }

    public function testAddCall()
    {
        $this->subject->addCall($this->callA);

        $this->assertSame(array($this->callA), $this->subject->spy()->calls());

        $this->subject->addCall($this->callB);

        $this->assertSame(array($this->callA, $this->callB), $this->subject->spy()->calls());
    }

    public function testCalls()
    {
        $this->assertSame(array(), $this->subject->calls());

        $this->subject->setCalls($this->calls);

        $this->assertEquals($this->wrappedCalls, $this->subject->calls());
    }

    public function testInvokeMethods()
    {
        $verifier = $this->subject;
        $spy = $verifier->spy();
        $verifier->invokeWith(array(array('a')));
        $verifier->invoke(array('b', 'c'));
        $verifier(array('d'));
        $this->callFactory->sequencer()->reset();
        $this->callFactory->clock()->reset();
        $expected = array(
            $this->callFactory->create(
                array(
                    $this->callFactory->createCalledEvent($spy->subject(), array(array('a'))),
                    $this->callFactory->createReturnedEvent('a'),
                )
            ),
            $this->callFactory->create(
                array(
                    $this->callFactory->createCalledEvent($spy->subject(), array(array('b', 'c'))),
                    $this->callFactory->createReturnedEvent('bc'),
                )
            ),
            $this->callFactory->create(
                array(
                    $this->callFactory->createCalledEvent($spy->subject(), array(array('d'))),
                    $this->callFactory->createReturnedEvent('d'),
                )
            ),
        );

        $this->assertEquals($expected, $this->spy->calls());
    }

    public function testInvokeMethodsWithoutSubject()
    {
        $spy = new Spy(null, $this->callFactory);
        $verifier = new SpyVerifier($spy);
        $verifier->invokeWith(array('a'));
        $verifier->invoke('b', 'c');
        $verifier('d');
        $this->callFactory->sequencer()->reset();
        $this->callFactory->clock()->reset();
        $expected = array(
            $this->callFactory->create(
                array(
                    $this->callFactory->createCalledEvent($spy->subject(), array('a')),
                    $this->callFactory->createReturnedEvent(),
                )
            ),
            $this->callFactory->create(
                array(
                    $this->callFactory->createCalledEvent($spy->subject(), array('b', 'c')),
                    $this->callFactory->createReturnedEvent(),
                )
            ),
            $this->callFactory->create(
                array(
                    $this->callFactory->createCalledEvent($spy->subject(), array('d')),
                    $this->callFactory->createReturnedEvent(),
                )
            ),
        );

        $this->assertEquals($expected, $spy->calls());
    }

    public function testInvokeWithExceptionThrown()
    {
        $exceptions = array(new Exception(), new Exception(), new Exception());
        $subject = function () use (&$exceptions) {
            list(, $exception) = each($exceptions);
            throw $exception;
        };
        $spy = new Spy($subject, $this->callFactory);
        $verifier = new SpyVerifier($spy);
        $caughtExceptions = array();
        try {
            $verifier->invokeWith(array('a'));
        } catch (Exception $caughtException) {
            $caughtExceptions[] = $caughtException;
        }
        try {
            $verifier->invoke('b', 'c');
        } catch (Exception $caughtException) {
            $caughtExceptions[] = $caughtException;
        }
        try {
            $verifier('d');
        } catch (Exception $caughtException) {
            $caughtExceptions[] = $caughtException;
        }
        $this->callFactory->sequencer()->reset();
        $this->callFactory->clock()->reset();
        $expected = array(
            $this->callFactory->create(
                array(
                    $this->callFactory->createCalledEvent($spy->subject(), array('a')),
                    $this->callFactory->createThrewEvent($exceptions[0]),
                )
            ),
            $this->callFactory->create(
                array(
                    $this->callFactory->createCalledEvent($spy->subject(), array('b', 'c')),
                    $this->callFactory->createThrewEvent($exceptions[1]),
                )
            ),
            $this->callFactory->create(
                array(
                    $this->callFactory->createCalledEvent($spy->subject(), array('d')),
                    $this->callFactory->createThrewEvent($exceptions[2]),
                )
            ),
        );

        $this->assertEquals($expected, $spy->calls());
    }

    public function testInvokeWithWithReferenceParameters()
    {
        $subject = function (&$argument) {
            $argument = 'x';
        };
        $spy = new Spy($subject, $this->callFactory);
        $verifier = new SpyVerifier($spy);
        $value = null;
        $arguments = array(&$value);
        $verifier->invokeWith($arguments);

        $this->assertSame('x', $value);
    }

    public function testCallCount()
    {
        $this->assertSame(0, $this->subject->callCount());

        $this->subject->setCalls($this->calls);

        $this->assertSame(4, $this->subject->callCount());
    }

    public function testCallAt()
    {
        $this->subject->setCalls($this->calls);

        $this->assertEquals($this->wrappedCallA, $this->subject->callAt(0));
        $this->assertEquals($this->wrappedCallB, $this->subject->callAt(1));
    }

    public function testCallAtFailureUndefined()
    {
        $this->setExpectedException('Eloquent\Phony\Spy\Exception\UndefinedCallException');
        $this->subject->callAt(0);
    }

    public function testFirstCall()
    {
        $this->subject->setCalls($this->calls);

        $this->assertEquals($this->wrappedCallA, $this->subject->firstCall());
    }

    public function testFirstCallFailureUndefined()
    {
        $this->setExpectedException('Eloquent\Phony\Spy\Exception\UndefinedCallException');
        $this->subject->firstCall();
    }

    public function testLastCall()
    {
        $this->subject->setCalls($this->calls);

        $this->assertEquals($this->wrappedCallD, $this->subject->lastCall());
    }

    public function testLastCallFailureUndefined()
    {
        $this->setExpectedException('Eloquent\Phony\Spy\Exception\UndefinedCallException');
        $this->subject->lastCall();
    }

    public function testCalled()
    {
        $this->assertFalse($this->subject->called());

        $this->subject->setCalls($this->calls);

        $this->assertTrue($this->subject->called());
    }

    public function testAssertCalled()
    {
        $this->subject->setCalls($this->calls);

        $this->assertNull($this->subject->assertCalled());
    }

    public function testAssertCalledFailure()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', 'Never called.');
        $this->subject->assertCalled();
    }

    public function testCalledOnce()
    {
        $this->assertFalse($this->subject->calledOnce());

        $this->subject->addCall($this->callA);

        $this->assertTrue($this->subject->calledOnce());

        $this->subject->addCall($this->callB);

        $this->assertFalse($this->subject->calledOnce());
    }

    public function testAssertCalledOnce()
    {
        $this->subject->addCall($this->callA);

        $this->assertNull($this->subject->assertCalledOnce());
    }

    public function testAssertCalledOnceFailureWithNoCalls()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected 1 call. Called 0 time(s).'
        );
        $this->subject->assertCalledOnce();
    }

    public function testAssertCalledOnceFailureWithMultipleCalls()
    {
        $this->subject->setCalls($this->calls);

        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected 1 call. Called 4 time(s).'
        );
        $this->subject->assertCalledOnce();
    }

    public function testCalledTimes()
    {
        $this->assertTrue($this->subject->calledTimes(0));
        $this->assertFalse($this->subject->calledTimes(4));

        $this->subject->setCalls($this->calls);

        $this->assertFalse($this->subject->calledTimes(0));
        $this->assertTrue($this->subject->calledTimes(4));
    }

    public function testAssertCalledTimes()
    {
        $this->subject->setCalls($this->calls);

        $this->assertNull($this->subject->assertCalledTimes(4));
    }

    public function testAssertCalledTimesFailure()
    {
        $this->subject->setCalls($this->calls);

        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected 2 call(s). Called 4 time(s).'
        );
        $this->subject->assertCalledTimes(2);
    }

    public function testCalledBefore()
    {
        $spyA = new Spy();
        $spyA->setCalls(array($this->callA, $this->callC));
        $spyB = new Spy();
        $spyB->setCalls(array($this->callA));
        $spyC = new Spy();

        $this->assertFalse($this->subject->calledBefore($spyA));
        $this->assertFalse($this->subject->calledBefore($spyB));
        $this->assertFalse($this->subject->calledBefore($spyC));

        $this->subject->setCalls(array($this->callB, $this->callD));

        $this->assertTrue($this->subject->calledBefore($spyA));
        $this->assertFalse($this->subject->calledBefore($spyB));
        $this->assertFalse($this->subject->calledBefore($spyC));
    }

    public function testAssertCalledBefore()
    {
        $this->subject->setCalls(array($this->callB, $this->callD));
        $spy = new Spy();
        $spy->setCalls(array($this->callA, $this->callC));

        $this->assertNull($this->subject->assertCalledBefore($spy));
    }

    public function testAssertCalledBeforeFailure()
    {
        $this->subject->setCalls(array($this->callC, $this->callD));
        $spy = new Spy();
        $spy->setCalls(array($this->callA, $this->callB));
        $spyVerifier = new SpyVerifier($spy);
        $expected = <<<'EOD'
Not called before supplied spy. Actual calls:
    - Eloquent\Phony\Test\TestClass->methodA('a', 'b', 'c')
    - Eloquent\Phony\Test\TestClass->methodA()
    - Eloquent\Phony\Test\TestClass->methodA('a', 'b', 'c')
    - implode()
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertCalledBefore($spyVerifier);
    }

    public function testCalledAfter()
    {
        $spyA = new Spy();
        $spyA->setCalls(array($this->callB, $this->callD));
        $spyB = new Spy();
        $spyB->setCalls(array($this->callD));
        $spyC = new Spy();

        $this->assertFalse($this->subject->calledAfter($spyA));
        $this->assertFalse($this->subject->calledAfter($spyB));
        $this->assertFalse($this->subject->calledAfter($spyC));

        $this->subject->setCalls(array($this->callA, $this->callC));

        $this->assertTrue($this->subject->calledAfter($spyA));
        $this->assertFalse($this->subject->calledAfter($spyB));
        $this->assertFalse($this->subject->calledAfter($spyC));
    }

    public function testAssertCalledAfter()
    {
        $this->subject->setCalls(array($this->callB, $this->callD));
        $spy = new Spy();
        $spy->setCalls(array($this->callA, $this->callC));

        $this->assertNull($this->subject->assertCalledAfter($spy));
    }

    public function testAssertCalledAfterFailure()
    {
        $this->subject->setCalls(array($this->callA, $this->callB));
        $spy = new Spy();
        $spy->setCalls(array($this->callC, $this->callD));
        $spyVerifier = new SpyVerifier($spy);
        $expected = <<<'EOD'
Not called after supplied spy. Actual calls:
    - Eloquent\Phony\Test\TestClass->methodA('a', 'b', 'c')
    - Eloquent\Phony\Test\TestClass->methodA()
    - Eloquent\Phony\Test\TestClass->methodA('a', 'b', 'c')
    - implode()
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertCalledAfter($spyVerifier);
    }

    public function calledWithData()
    {
        //                                    arguments                                                  calledWith calledWithExactly
        return array(
            'Exact arguments'        => array(array('a', 'b', 'c'),              true,      true),
            'First arguments'        => array(array('a', 'b'),                           true,      false),
            'Single argument'        => array(array('a'),                                        true,      false),
            'Last arguments'         => array(array('b', 'c'),                           false,     false),
            'Last argument'          => array(array('c'),                                        false,     false),
            'Extra arguments'        => array(array('a', 'b', 'c', 'd'), false,     false),
            'First argument differs' => array(array('d', 'b', 'c'),              false,     false),
            'Last argument differs'  => array(array('a', 'b', 'd'),              false,     false),
            'Unused argument'        => array(array('d'),                                        false,     false),
        );
    }

    /**
     * @dataProvider calledWithData
     */
    public function testCalledWith(array $arguments, $calledWith, $calledWithExactly)
    {
        $this->subject->setCalls($this->calls);
        $matchers = $this->matcherFactory->adaptAll($arguments);

        $this->assertSame($calledWith, call_user_func_array(array($this->subject, 'calledWith'), $arguments));
        $this->assertSame($calledWith, call_user_func_array(array($this->subject, 'calledWith'), $matchers));
    }

    public function testCalledWithWithEmptyArguments()
    {
        $this->subject->setCalls($this->calls);

        $this->assertTrue($this->subject->calledWith());
    }

    public function testCalledWithWithNoCalls()
    {
        $this->assertFalse($this->subject->calledWith());
    }

    public function testAssertCalledWith()
    {
        $this->subject->setCalls($this->calls);

        $this->assertNull($this->subject->assertCalledWith('a', 'b', 'c'));
        $this->assertNull(
            $this->subject
                ->assertCalledWith($this->argumentMatchers[0], $this->argumentMatchers[1], $this->argumentMatchers[2])
        );
        $this->assertNull($this->subject->assertCalledWith('a', 'b'));
        $this->assertNull($this->subject->assertCalledWith($this->argumentMatchers[0], $this->argumentMatchers[1]));
        $this->assertNull($this->subject->assertCalledWith('a'));
        $this->assertNull($this->subject->assertCalledWith($this->argumentMatchers[0]));
        $this->assertNull($this->subject->assertCalledWith());
        $this->assertSame(7, $this->assertionRecorder->successCount());
    }

    public function testAssertCalledWithFailure()
    {
        $this->subject->setCalls($this->calls);

        $expected = <<<'EOD'
Expected arguments like:
    <'b'>, <'c'>, <any>*
Actual calls:
    - 'a', 'b', 'c'
    - <none>
    - 'a', 'b', 'c'
    - <none>
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertCalledWith('b', 'c');
    }

    public function testAssertCalledWithFailureWithNoCalls()
    {
        $expected = <<<'EOD'
Expected arguments like:
    <'b'>, <'c'>, <any>*
Never called.
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertCalledWith('b', 'c');
    }

    /**
     * @dataProvider calledWithData
     */
    public function testAlwaysCalledWith(array $arguments, $calledWith, $calledWithExactly)
    {
        $this->subject->setCalls(array($this->callA, $this->callA));
        $matchers = $this->matcherFactory->adaptAll($arguments);

        $this->assertSame($calledWith, call_user_func_array(array($this->subject, 'alwaysCalledWith'), $arguments));
        $this->assertSame($calledWith, call_user_func_array(array($this->subject, 'alwaysCalledWith'), $matchers));
    }

    /**
     * @dataProvider calledWithData
     */
    public function testAlwaysCalledWithWithDifferingCalls(array $arguments, $calledWith, $calledWithExactly)
    {
        $this->subject->setCalls(array($this->callA, $this->callB));
        $matchers = $this->matcherFactory->adaptAll($arguments);

        $this->assertFalse(call_user_func_array(array($this->subject, 'alwaysCalledWith'), $arguments));
        $this->assertFalse(call_user_func_array(array($this->subject, 'alwaysCalledWith'), $matchers));
    }

    public function testAlwaysCalledWithWithEmptyArguments()
    {
        $this->subject->setCalls($this->calls);

        $this->assertTrue($this->subject->alwaysCalledWith());
    }

    public function testAlwaysCalledWithWithNoCalls()
    {
        $this->assertFalse($this->subject->alwaysCalledWith());
    }

    public function testAssertAlwaysCalledWith()
    {
        $this->subject->setCalls(array($this->callA, $this->callA));

        $this->assertNull($this->subject->assertAlwaysCalledWith('a', 'b', 'c'));
        $this->assertNull(
            $this->subject->assertAlwaysCalledWith(
                $this->argumentMatchers[0],
                $this->argumentMatchers[1],
                $this->argumentMatchers[2]
            )
        );
        $this->assertNull($this->subject->assertAlwaysCalledWith('a', 'b'));
        $this->assertNull(
            $this->subject->assertAlwaysCalledWith($this->argumentMatchers[0], $this->argumentMatchers[1])
        );
        $this->assertNull($this->subject->assertAlwaysCalledWith('a'));
        $this->assertNull($this->subject->assertAlwaysCalledWith($this->argumentMatchers[0]));
        $this->assertNull($this->subject->assertAlwaysCalledWith());
        $this->assertSame(7, $this->assertionRecorder->successCount());
    }

    public function testAssertAlwaysCalledWithFailure()
    {
        $this->subject->setCalls($this->calls);

        $expected = <<<'EOD'
Expected every call with arguments like:
    <'a'>, <'b'>, <'c'>, <any>*
Actual calls:
    - 'a', 'b', 'c'
    - <none>
    - 'a', 'b', 'c'
    - <none>
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertAlwaysCalledWith('a', 'b', 'c');
    }

    public function testAssertAlwaysCalledWithFailureWithNoCalls()
    {
        $expected = <<<'EOD'
Expected every call with arguments like:
    <'a'>, <'b'>, <'c'>, <any>*
Never called.
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertAlwaysCalledWith('a', 'b', 'c');
    }

    /**
     * @dataProvider calledWithData
     */
    public function testCalledWithExactly(array $arguments, $calledWith, $calledWithExactly)
    {
        $this->subject->setCalls($this->calls);
        $matchers = $this->matcherFactory->adaptAll($arguments);

        $this->assertSame(
            $calledWithExactly,
            call_user_func_array(array($this->subject, 'calledWithExactly'), $arguments)
        );
        $this->assertSame(
            $calledWithExactly,
            call_user_func_array(array($this->subject, 'calledWithExactly'), $matchers)
        );
    }

    public function testCalledWithExactlyWithEmptyArguments()
    {
        $this->subject->setCalls($this->calls);

        $this->assertTrue($this->subject->calledWithExactly());
    }

    public function testCalledWithExactlyWithNoCalls()
    {
        $this->assertFalse($this->subject->calledWithExactly());
    }

    public function testAssertCalledWithExactly()
    {
        $this->subject->setCalls($this->calls);

        $this->assertNull($this->subject->assertCalledWithExactly('a', 'b', 'c'));
        $this->assertNull(
            $this->subject->assertCalledWithExactly(
                $this->argumentMatchers[0],
                $this->argumentMatchers[1],
                $this->argumentMatchers[2]
            )
        );
        $this->assertSame(2, $this->assertionRecorder->successCount());
    }

    public function testAssertCalledWithExactlyFailure()
    {
        $this->subject->setCalls($this->calls);

        $expected = <<<'EOD'
Expected arguments like:
    <'b'>, <'c'>
Actual calls:
    - 'a', 'b', 'c'
    - <none>
    - 'a', 'b', 'c'
    - <none>
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertCalledWithExactly('b', 'c');
    }

    public function testAssertCalledWithExactlyFailureWithNoCalls()
    {
        $expected = <<<'EOD'
Expected arguments like:
    <'b'>, <'c'>
Never called.
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertCalledWithExactly('b', 'c');
    }

    /**
     * @dataProvider calledWithData
     */
    public function testAlwaysCalledWithExactly(array $arguments, $calledWith, $calledWithExactly)
    {
        $this->subject->setCalls(array($this->callA, $this->callA));
        $matchers = $this->matcherFactory->adaptAll($arguments);

        $this->assertSame(
            $calledWithExactly,
            call_user_func_array(array($this->subject, 'alwaysCalledWithExactly'), $arguments)
        );
        $this->assertSame(
            $calledWithExactly,
            call_user_func_array(array($this->subject, 'alwaysCalledWithExactly'), $matchers)
        );
    }

    /**
     * @dataProvider calledWithData
     */
    public function testAlwaysCalledWithExactlyWithDifferingCalls(array $arguments, $calledWith, $calledWithExactly)
    {
        $this->subject->setCalls(array($this->callA, $this->callB));
        $matchers = $this->matcherFactory->adaptAll($arguments);

        $this->assertFalse(call_user_func_array(array($this->subject, 'alwaysCalledWithExactly'), $arguments));
        $this->assertFalse(call_user_func_array(array($this->subject, 'alwaysCalledWithExactly'), $matchers));
    }

    public function testAlwaysCalledWithExactlyWithEmptyArguments()
    {
        $this->subject->setCalls($this->calls);

        $this->assertFalse($this->subject->alwaysCalledWithExactly());
    }

    public function testAlwaysCalledWithExactlyWithNoCalls()
    {
        $this->assertFalse($this->subject->alwaysCalledWithExactly());
    }

    public function testAssertAlwaysCalledWithExactly()
    {
        $this->subject->setCalls(array($this->callA, $this->callA));

        $this->assertNull($this->subject->assertAlwaysCalledWithExactly('a', 'b', 'c'));
        $this->assertNull(
            $this->subject->assertAlwaysCalledWithExactly(
                $this->argumentMatchers[0],
                $this->argumentMatchers[1],
                $this->argumentMatchers[2]
            )
        );
        $this->assertSame(2, $this->assertionRecorder->successCount());
    }

    public function testAssertAlwaysCalledWithExactlyFailure()
    {
        $this->subject->setCalls($this->calls);

        $expected = <<<'EOD'
Expected every call with arguments like:
    <'a'>, <'b'>, <'c'>
Actual calls:
    - 'a', 'b', 'c'
    - <none>
    - 'a', 'b', 'c'
    - <none>
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertAlwaysCalledWithExactly('a', 'b', 'c');
    }

    public function testAssertAlwaysCalledWithExactlyFailureWithNoCalls()
    {
        $expected = <<<'EOD'
Expected every call with arguments like:
    <'a'>, <'b'>, <'c'>
Never called.
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertAlwaysCalledWithExactly('a', 'b', 'c');
    }

    /**
     * @dataProvider calledWithData
     */
    public function testNeverCalledWith(array $arguments, $calledWith, $calledWithExactly)
    {
        $this->subject->setCalls($this->calls);
        $matchers = $this->matcherFactory->adaptAll($arguments);

        $this->assertSame(!$calledWith, call_user_func_array(array($this->subject, 'neverCalledWith'), $arguments));
        $this->assertSame(!$calledWith, call_user_func_array(array($this->subject, 'neverCalledWith'), $matchers));
    }

    public function testNeverCalledWithWithEmptyArguments()
    {
        $this->subject->setCalls($this->calls);

        $this->assertFalse($this->subject->neverCalledWith());
    }

    public function testNeverCalledWithWithNoCalls()
    {
        $this->assertTrue($this->subject->neverCalledWith());
    }

    public function testAssertNeverCalledWith()
    {
        $this->assertNull($this->subject->assertNeverCalledWith());

        $this->subject->setCalls($this->calls);

        $this->assertNull($this->subject->assertNeverCalledWith('b', 'c'));
        $this->assertNull(
            $this->subject->assertNeverCalledWith($this->argumentMatchers[1], $this->argumentMatchers[2])
        );
        $this->assertNull($this->subject->assertNeverCalledWith('c'));
        $this->assertNull($this->subject->assertNeverCalledWith($this->argumentMatchers[2]));
        $this->assertNull($this->subject->assertNeverCalledWith('a', 'b', 'c', 'd'));
        $this->assertNull(
            $this->subject->assertNeverCalledWith(
                $this->argumentMatchers[0],
                $this->argumentMatchers[1],
                $this->argumentMatchers[2],
                $this->matcherFactory->adapt('d')
            )
        );
        $this->assertNull($this->subject->assertNeverCalledWith('d', 'b', 'c'));
        $this->assertNull(
            $this->subject->assertNeverCalledWith(
                $this->matcherFactory->adapt('d'),
                $this->argumentMatchers[1],
                $this->argumentMatchers[2]
            )
        );
        $this->assertNull($this->subject->assertNeverCalledWith('a', 'b', 'd'));
        $this->assertNull(
            $this->subject->assertNeverCalledWith(
                $this->argumentMatchers[0],
                $this->argumentMatchers[1],
                $this->matcherFactory->adapt('d')
            )
        );
        $this->assertNull($this->subject->assertNeverCalledWith('d'));
        $this->assertNull($this->subject->assertNeverCalledWith($this->matcherFactory->adapt('d')));
        $this->assertSame(13, $this->assertionRecorder->successCount());
    }

    public function testAssertNeverCalledWithFailure()
    {
        $this->subject->setCalls($this->calls);

        $expected = <<<'EOD'
Expected no call with arguments like:
    <'a'>, <'b'>, <'c'>, <any>*
Actual calls:
    - 'a', 'b', 'c'
    - <none>
    - 'a', 'b', 'c'
    - <none>
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertNeverCalledWith('a', 'b', 'c');
    }

    /**
     * @dataProvider calledWithData
     */
    public function testNeverCalledWithExactly(array $arguments, $calledWith, $calledWithExactly)
    {
        $this->subject->setCalls($this->calls);
        $matchers = $this->matcherFactory->adaptAll($arguments);

        $this->assertSame(
            !$calledWithExactly,
            call_user_func_array(array($this->subject, 'neverCalledWithExactly'), $arguments)
        );
        $this->assertSame(
            !$calledWithExactly,
            call_user_func_array(array($this->subject, 'neverCalledWithExactly'), $matchers)
        );
    }

    public function testNeverCalledWithExactlyWithEmptyArguments()
    {
        $this->subject->setCalls($this->calls);

        $this->assertFalse($this->subject->neverCalledWithExactly());
    }

    public function testNeverCalledWithExactlyWithNoCalls()
    {
        $this->assertTrue($this->subject->neverCalledWithExactly());
    }

    public function testAssertNeverCalledWithExactly()
    {
        $this->assertNull($this->subject->assertNeverCalledWithExactly());

        $this->subject->setCalls($this->calls);

        $this->assertNull($this->subject->assertNeverCalledWithExactly('a', 'b'));
        $this->assertNull(
            $this->subject->assertNeverCalledWithExactly($this->argumentMatchers[0], $this->argumentMatchers[1])
        );
        $this->assertNull($this->subject->assertNeverCalledWithExactly('a'));
        $this->assertNull($this->subject->assertNeverCalledWithExactly($this->argumentMatchers[0]));
        $this->assertNull($this->subject->assertNeverCalledWithExactly('b', 'c'));
        $this->assertNull(
            $this->subject->assertNeverCalledWithExactly($this->argumentMatchers[1], $this->argumentMatchers[2])
        );
        $this->assertNull($this->subject->assertNeverCalledWithExactly('c'));
        $this->assertNull($this->subject->assertNeverCalledWithExactly($this->argumentMatchers[2]));
        $this->assertNull(
            $this->subject->assertNeverCalledWithExactly('a', 'b', 'c', 'd')
        );
        $this->assertNull(
            $this->subject->assertNeverCalledWithExactly(
                $this->argumentMatchers[0],
                $this->argumentMatchers[1],
                $this->argumentMatchers[2],
                $this->matcherFactory->adapt('d')
            )
        );
        $this->assertNull($this->subject->assertNeverCalledWithExactly('d', 'b', 'c'));
        $this->assertNull(
            $this->subject->assertNeverCalledWithExactly(
                $this->matcherFactory->adapt('d'),
                $this->argumentMatchers[1],
                $this->argumentMatchers[2]
            )
        );
        $this->assertNull($this->subject->assertNeverCalledWithExactly('a', 'b', 'd'));
        $this->assertNull(
            $this->subject->assertNeverCalledWithExactly(
                $this->argumentMatchers[0],
                $this->argumentMatchers[1],
                $this->matcherFactory->adapt('d')
            )
        );
        $this->assertNull($this->subject->assertNeverCalledWithExactly('d'));
        $this->assertNull($this->subject->assertNeverCalledWithExactly($this->matcherFactory->adapt('d')));
        $this->assertSame(17, $this->assertionRecorder->successCount());
    }

    public function testAssertNeverCalledWithExactlyFailure()
    {
        $this->subject->setCalls($this->calls);

        $expected = <<<'EOD'
Expected no call with arguments like:
    <'a'>, <'b'>, <'c'>
Actual calls:
    - 'a', 'b', 'c'
    - <none>
    - 'a', 'b', 'c'
    - <none>
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertNeverCalledWithExactly('a', 'b', 'c');
    }

    public function testCalledOn()
    {
        $this->assertFalse($this->subject->calledOn(null));
        $this->assertFalse($this->subject->calledOn($this->thisValueA));
        $this->assertFalse($this->subject->calledOn($this->thisValueB));
        $this->assertFalse($this->subject->calledOn(new EqualToMatcher($this->thisValueA)));
        $this->assertFalse($this->subject->calledOn((object) array()));

        $this->subject->setCalls($this->calls);

        $this->assertTrue($this->subject->calledOn(null));
        $this->assertTrue($this->subject->calledOn($this->thisValueA));
        $this->assertTrue($this->subject->calledOn($this->thisValueB));
        $this->assertTrue($this->subject->calledOn(new EqualToMatcher($this->thisValueA)));
        $this->assertFalse($this->subject->calledOn((object) array()));
    }

    public function testAssertCalledOn()
    {
        $this->subject->setCalls($this->calls);

        $this->assertNull($this->subject->assertCalledOn(null));
        $this->assertNull($this->subject->assertCalledOn($this->thisValueA));
        $this->assertNull($this->subject->assertCalledOn($this->thisValueB));
        $this->assertNull($this->subject->assertCalledOn(new EqualToMatcher($this->thisValueA)));
        $this->assertSame(4, $this->assertionRecorder->successCount());
    }

    public function testAssertCalledOnFailure()
    {
        $this->subject->setCalls($this->calls);
        $expected = <<<'EOD'
Not called on expected object. Actual objects:
    - Eloquent\Phony\Test\TestClass Object ()
    - Eloquent\Phony\Test\TestClass Object ()
    - Eloquent\Phony\Test\TestClass Object ()
    - null
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertCalledOn((object) array());
    }

    public function testAssertCalledOnFailureWithNoCalls()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Not called on expected object. Never called.'
        );
        $this->subject->assertCalledOn((object) array());
    }

    public function testAssertCalledOnFailureWithMatcher()
    {
        $this->subject->setCalls($this->calls);
        $expected = <<<'EOD'
Not called on object like <stdClass Object (...)>. Actual objects:
    - Eloquent\Phony\Test\TestClass Object ()
    - Eloquent\Phony\Test\TestClass Object ()
    - Eloquent\Phony\Test\TestClass Object ()
    - null
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertCalledOn(new EqualToMatcher((object) array('property' => 'value')));
    }

    public function testAssertCalledOnFailureWithMatcherWithNoCalls()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Not called on object like <stdClass Object (...)>. Never called.'
        );
        $this->subject->assertCalledOn(new EqualToMatcher((object) array('property' => 'value')));
    }

    public function testAlwaysCalledOn()
    {
        $this->assertFalse($this->subject->alwaysCalledOn(null));
        $this->assertFalse($this->subject->alwaysCalledOn($this->thisValueA));
        $this->assertFalse($this->subject->alwaysCalledOn($this->thisValueB));
        $this->assertFalse($this->subject->alwaysCalledOn(new EqualToMatcher($this->thisValueA)));
        $this->assertFalse($this->subject->alwaysCalledOn((object) array()));

        $this->subject->setCalls($this->calls);

        $this->assertFalse($this->subject->alwaysCalledOn(null));
        $this->assertFalse($this->subject->alwaysCalledOn($this->thisValueA));
        $this->assertFalse($this->subject->alwaysCalledOn($this->thisValueB));
        $this->assertFalse($this->subject->alwaysCalledOn(new EqualToMatcher($this->thisValueA)));
        $this->assertFalse($this->subject->alwaysCalledOn((object) array()));

        $this->subject->setCalls(array($this->callC, $this->callC));

        $this->assertTrue($this->subject->alwaysCalledOn($this->thisValueA));
        $this->assertTrue($this->subject->alwaysCalledOn(new EqualToMatcher($this->thisValueA)));
        $this->assertFalse($this->subject->alwaysCalledOn(null));
        $this->assertFalse($this->subject->alwaysCalledOn($this->thisValueB));
        $this->assertFalse($this->subject->alwaysCalledOn((object) array()));
    }

    public function testAssertAlwaysCalledOn()
    {
        $this->subject->setCalls(array($this->callC, $this->callC));

        $this->assertNull($this->subject->assertAlwaysCalledOn($this->thisValueA));
        $this->assertNull($this->subject->assertAlwaysCalledOn(new EqualToMatcher($this->thisValueA)));
        $this->assertSame(2, $this->assertionRecorder->successCount());
    }

    public function testAssertAlwaysCalledOnFailure()
    {
        $this->subject->setCalls($this->calls);
        $expected = <<<'EOD'
Not always called on expected object. Actual objects:
    - Eloquent\Phony\Test\TestClass Object ()
    - Eloquent\Phony\Test\TestClass Object ()
    - Eloquent\Phony\Test\TestClass Object ()
    - null
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertAlwaysCalledOn($this->thisValueA);
    }

    public function testAssertAlwaysCalledOnFailureWithNoCalls()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Not called on expected object. Never called.'
        );
        $this->subject->assertAlwaysCalledOn($this->thisValueA);
    }

    public function testAssertAlwaysCalledOnFailureWithMatcher()
    {
        $this->subject->setCalls($this->calls);
        $expected = <<<'EOD'
Not always called on object like <Eloquent\Phony\Test\TestClass Object ()>. Actual objects:
    - Eloquent\Phony\Test\TestClass Object ()
    - Eloquent\Phony\Test\TestClass Object ()
    - Eloquent\Phony\Test\TestClass Object ()
    - null
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertAlwaysCalledOn(new EqualToMatcher($this->thisValueA));
    }

    public function testAssertAlwaysCalledOnFailureWithMatcherWithNoCalls()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Not called on object like <Eloquent\Phony\Test\TestClass Object ()>. Never called.'
        );
        $this->subject->assertAlwaysCalledOn(new EqualToMatcher($this->thisValueA));
    }

    public function testReturned()
    {
        $this->assertFalse($this->subject->returned(null));
        $this->assertFalse($this->subject->returned($this->returnValueA));
        $this->assertFalse($this->subject->returned($this->returnValueB));
        $this->assertFalse($this->subject->returned(new EqualToMatcher($this->returnValueA)));
        $this->assertFalse($this->subject->returned('z'));

        $this->subject->setCalls($this->calls);

        $this->assertTrue($this->subject->returned(null));
        $this->assertTrue($this->subject->returned($this->returnValueA));
        $this->assertTrue($this->subject->returned($this->returnValueB));
        $this->assertTrue($this->subject->returned(new EqualToMatcher($this->returnValueA)));
        $this->assertFalse($this->subject->returned('z'));
    }

    public function testAssertReturned()
    {
        $this->subject->setCalls($this->calls);

        $this->assertNull($this->subject->assertReturned(null));
        $this->assertNull($this->subject->assertReturned($this->returnValueA));
        $this->assertNull($this->subject->assertReturned($this->returnValueB));
        $this->assertNull($this->subject->assertReturned(new EqualToMatcher($this->returnValueA)));
        $this->assertSame(4, $this->assertionRecorder->successCount());
    }

    public function testAssertReturnedFailure()
    {
        $this->subject->setCalls($this->calls);

        $expected = <<<'EOD'
Expected return value like <'z'>. Actually returned:
    - 'x'
    - 'y'
    - null
    - null
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertReturned('z');
    }

    public function testAssertReturnedFailureWithNoCalls()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected return value like <'x'>. Never called."
        );
        $this->subject->assertReturned($this->returnValueA);
    }

    public function testAlwaysReturned()
    {
        $this->assertFalse($this->subject->alwaysReturned(null));
        $this->assertFalse($this->subject->alwaysReturned($this->returnValueA));
        $this->assertFalse($this->subject->alwaysReturned($this->returnValueB));
        $this->assertFalse($this->subject->alwaysReturned(new EqualToMatcher($this->returnValueA)));
        $this->assertFalse($this->subject->alwaysReturned('z'));

        $this->subject->setCalls($this->calls);

        $this->assertFalse($this->subject->alwaysReturned(null));
        $this->assertFalse($this->subject->alwaysReturned($this->returnValueA));
        $this->assertFalse($this->subject->alwaysReturned($this->returnValueB));
        $this->assertFalse($this->subject->alwaysReturned(new EqualToMatcher($this->returnValueA)));
        $this->assertFalse($this->subject->alwaysReturned('z'));

        $this->subject->setCalls(array($this->callA, $this->callA));

        $this->assertTrue($this->subject->alwaysReturned($this->returnValueA));
        $this->assertTrue($this->subject->alwaysReturned(new EqualToMatcher($this->returnValueA)));
        $this->assertFalse($this->subject->alwaysReturned(null));
        $this->assertFalse($this->subject->alwaysReturned($this->returnValueB));
        $this->assertFalse($this->subject->alwaysReturned('y'));
    }

    public function testAssertAlwaysReturned()
    {
        $this->subject->setCalls(array($this->callA, $this->callA));

        $this->assertNull($this->subject->assertAlwaysReturned($this->returnValueA));
        $this->assertNull($this->subject->assertAlwaysReturned(new EqualToMatcher($this->returnValueA)));
        $this->assertSame(2, $this->assertionRecorder->successCount());
    }

    public function testAssertAlwaysReturnedFailure()
    {
        $this->subject->setCalls($this->calls);

        $expected = <<<'EOD'
Expected every call with return value like <'x'>. Actually returned:
    - 'x'
    - 'y'
    - null
    - null
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertAlwaysReturned($this->returnValueA);
    }

    public function testAssertAlwaysReturnedFailureWithNoCalls()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected return value like <'x'>. Never called."
        );
        $this->subject->assertAlwaysReturned($this->returnValueA);
    }

    public function testThrew()
    {
        $this->assertFalse($this->subject->threw());
        $this->assertFalse($this->subject->threw('Exception'));
        $this->assertFalse($this->subject->threw('RuntimeException'));
        $this->assertFalse($this->subject->threw($this->exceptionA));
        $this->assertFalse($this->subject->threw($this->exceptionB));
        $this->assertFalse($this->subject->threw(new EqualToMatcher($this->exceptionA)));
        $this->assertFalse($this->subject->threw('InvalidArgumentException'));
        $this->assertFalse($this->subject->threw(new Exception()));
        $this->assertFalse($this->subject->threw(new RuntimeException()));
        $this->assertFalse($this->subject->threw(new EqualToMatcher(null)));
        $this->assertFalse($this->subject->threw(111));

        $this->subject->setCalls($this->calls);

        $this->assertTrue($this->subject->threw());
        $this->assertTrue($this->subject->threw('Exception'));
        $this->assertTrue($this->subject->threw('RuntimeException'));
        $this->assertTrue($this->subject->threw($this->exceptionA));
        $this->assertTrue($this->subject->threw($this->exceptionB));
        $this->assertTrue($this->subject->threw(new EqualToMatcher($this->exceptionA)));
        $this->assertFalse($this->subject->threw('InvalidArgumentException'));
        $this->assertFalse($this->subject->threw(new Exception()));
        $this->assertFalse($this->subject->threw(new RuntimeException()));
        $this->assertTrue($this->subject->threw(new EqualToMatcher(null)));
        $this->assertFalse($this->subject->threw(111));
    }

    public function testAssertThrew()
    {
        $this->subject->setCalls($this->calls);

        $this->assertNull($this->subject->assertThrew());
        $this->assertNull($this->subject->assertThrew('Exception'));
        $this->assertNull($this->subject->assertThrew('RuntimeException'));
        $this->assertNull($this->subject->assertThrew($this->exceptionA));
        $this->assertNull($this->subject->assertThrew($this->exceptionB));
        $this->assertNull($this->subject->assertThrew(new EqualToMatcher($this->exceptionA)));
        $this->assertNull($this->subject->assertThrew(new EqualToMatcher(null)));
        $this->assertSame(7, $this->assertionRecorder->successCount());
    }

    public function testAssertThrewFailureExpectingAny()
    {
        $this->subject->setCalls(array($this->callA, $this->callB));

        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Nothing thrown in 2 call(s).'
        );
        $this->subject->assertThrew();
    }

    public function testAssertThrewFailureExpectingAnyWithNoCalls()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Nothing thrown. Never called.'
        );
        $this->subject->assertThrew();
    }

    public function testAssertThrewFailureExpectingType()
    {
        $this->subject->setCalls(array($this->callC, $this->callD));
        $expected = <<<'EOD'
Expected 'Eloquent\Phony\Spy\Exception\UndefinedCallException' exception. Actually threw:
    - RuntimeException('You done goofed.')
    - RuntimeException('Consequences will never be the same.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertThrew('Eloquent\Phony\Spy\Exception\UndefinedCallException');
    }

    public function testAssertThrewFailureExpectingTypeWithNoCalls()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected 'Eloquent\Phony\Spy\Exception\UndefinedCallException' exception. Never called."
        );
        $this->subject->assertThrew('Eloquent\Phony\Spy\Exception\UndefinedCallException');
    }

    public function testAssertThrewFailureExpectingTypeWithNoExceptions()
    {
        $this->subject->setCalls(array($this->callA, $this->callB));

        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected 'Eloquent\Phony\Spy\Exception\UndefinedCallException' exception. Nothing thrown in 2 call(s)."
        );
        $this->subject->assertThrew('Eloquent\Phony\Spy\Exception\UndefinedCallException');
    }

    public function testAssertThrewFailureExpectingException()
    {
        $this->subject->setCalls(array($this->callC, $this->callD));
        $expected = <<<'EOD'
Expected exception equal to RuntimeException(). Actually threw:
    - RuntimeException('You done goofed.')
    - RuntimeException('Consequences will never be the same.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertThrew(new RuntimeException());
    }

    public function testAssertThrewFailureExpectingExceptionWithNoCalls()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected exception equal to RuntimeException(). Never called.'
        );
        $this->subject->assertThrew(new RuntimeException());
    }

    public function testAssertThrewFailureExpectingExceptionWithNoExceptions()
    {
        $this->subject->setCalls(array($this->callA, $this->callB));

        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected exception equal to RuntimeException(). Nothing thrown in 2 call(s)."
        );
        $this->subject->assertThrew(new RuntimeException());
    }

    public function testAssertThrewFailureExpectingMatcher()
    {
        $this->subject->setCalls(array($this->callC, $this->callD));
        $expected = <<<'EOD'
Expected exception like <RuntimeException Object (...)>. Actually threw:
    - RuntimeException('You done goofed.')
    - RuntimeException('Consequences will never be the same.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertThrew(new EqualToMatcher(new RuntimeException()));
    }

    public function testAssertThrewFailureExpectingMatcherWithNoCalls()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected exception like <RuntimeException Object (...)>. Never called.'
        );
        $this->subject->assertThrew(new EqualToMatcher(new RuntimeException()));
    }

    public function testAssertThrewFailureExpectingMatcherWithNoExceptions()
    {
        $this->subject->setCalls(array($this->callA, $this->callB));

        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected exception like <RuntimeException Object (...)>. ' .
                'Nothing thrown in 2 call(s).'
        );
        $this->subject->assertThrew(new EqualToMatcher(new RuntimeException()));
    }

    public function testAssertThrewFailureInvalidInput()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Unable to match exceptions against stdClass Object ()."
        );
        $this->subject->assertThrew((object) array());
    }

    public function testAlwaysThrew()
    {
        $this->assertFalse($this->subject->alwaysThrew());
        $this->assertFalse($this->subject->alwaysThrew('Exception'));
        $this->assertFalse($this->subject->alwaysThrew('RuntimeException'));
        $this->assertFalse($this->subject->alwaysThrew($this->exceptionA));
        $this->assertFalse($this->subject->alwaysThrew($this->exceptionB));
        $this->assertFalse($this->subject->alwaysThrew(new EqualToMatcher($this->exceptionA)));
        $this->assertFalse($this->subject->alwaysThrew('InvalidArgumentException'));
        $this->assertFalse($this->subject->alwaysThrew(new Exception()));
        $this->assertFalse($this->subject->alwaysThrew(new RuntimeException()));
        $this->assertFalse($this->subject->alwaysThrew(new EqualToMatcher(null)));
        $this->assertFalse($this->subject->alwaysThrew(111));

        $this->subject->setCalls($this->calls);

        $this->assertFalse($this->subject->alwaysThrew());
        $this->assertFalse($this->subject->alwaysThrew('Exception'));
        $this->assertFalse($this->subject->alwaysThrew('RuntimeException'));
        $this->assertFalse($this->subject->alwaysThrew($this->exceptionA));
        $this->assertFalse($this->subject->alwaysThrew($this->exceptionB));
        $this->assertFalse($this->subject->alwaysThrew(new EqualToMatcher($this->exceptionA)));
        $this->assertFalse($this->subject->alwaysThrew('InvalidArgumentException'));
        $this->assertFalse($this->subject->alwaysThrew(new Exception()));
        $this->assertFalse($this->subject->alwaysThrew(new RuntimeException()));
        $this->assertFalse($this->subject->alwaysThrew(new EqualToMatcher(null)));
        $this->assertFalse($this->subject->alwaysThrew(111));

        $this->subject->setCalls(array($this->callC, $this->callC));

        $this->assertTrue($this->subject->alwaysThrew());
        $this->assertTrue($this->subject->alwaysThrew('Exception'));
        $this->assertTrue($this->subject->alwaysThrew('RuntimeException'));
        $this->assertTrue($this->subject->alwaysThrew($this->exceptionA));
        $this->assertFalse($this->subject->alwaysThrew($this->exceptionB));
        $this->assertTrue($this->subject->alwaysThrew(new EqualToMatcher($this->exceptionA)));
        $this->assertFalse($this->subject->alwaysThrew('InvalidArgumentException'));
        $this->assertFalse($this->subject->alwaysThrew(new Exception()));
        $this->assertFalse($this->subject->alwaysThrew(new RuntimeException()));
        $this->assertFalse($this->subject->alwaysThrew(new EqualToMatcher(null)));
        $this->assertFalse($this->subject->alwaysThrew(111));

        $this->subject->setCalls(array($this->callA, $this->callA));

        $this->assertTrue($this->subject->alwaysThrew(new EqualToMatcher(null)));
    }

    public function testAssertAlwaysThrew()
    {
        $this->subject->setCalls(array($this->callC, $this->callC));

        $this->assertNull($this->subject->assertAlwaysThrew());
        $this->assertNull($this->subject->assertAlwaysThrew('Exception'));
        $this->assertNull($this->subject->assertAlwaysThrew('RuntimeException'));
        $this->assertNull($this->subject->assertAlwaysThrew($this->exceptionA));
        $this->assertNull($this->subject->assertAlwaysThrew(new EqualToMatcher($this->exceptionA)));

        $this->subject->setCalls(array($this->callA, $this->callA));

        $this->assertNull($this->subject->assertAlwaysThrew(new EqualToMatcher(null)));
        $this->assertSame(6, $this->assertionRecorder->successCount());
    }

    public function testAssertAlwaysThrewFailureExpectingAny()
    {
        $this->subject->setCalls(array($this->callA, $this->callB));

        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Nothing thrown in 2 call(s).'
        );
        $this->subject->assertAlwaysThrew();
    }

    public function testAssertAlwaysThrewFailureExpectingAnyWithNoCalls()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Nothing thrown. Never called.'
        );
        $this->subject->assertAlwaysThrew();
    }

    public function testAssertAlwaysThrewFailureExpectingType()
    {
        $this->subject->setCalls(array($this->callC, $this->callD));
        $expected = <<<'EOD'
Expected every call to throw 'Eloquent\Phony\Spy\Exception\UndefinedCallException' exception. Actually threw:
    - RuntimeException('You done goofed.')
    - RuntimeException('Consequences will never be the same.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertAlwaysThrew('Eloquent\Phony\Spy\Exception\UndefinedCallException');
    }

    public function testAssertAlwaysThrewFailureExpectingTypeWithNoCalls()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected 'Eloquent\Phony\Spy\Exception\UndefinedCallException' exception. Never called."
        );
        $this->subject->assertAlwaysThrew('Eloquent\Phony\Spy\Exception\UndefinedCallException');
    }

    public function testAssertAlwaysThrewFailureExpectingTypeWithNoExceptions()
    {
        $this->subject->setCalls(array($this->callA, $this->callB));

        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected 'Eloquent\Phony\Spy\Exception\UndefinedCallException' exception. Nothing thrown in 2 call(s)."
        );
        $this->subject->assertAlwaysThrew('Eloquent\Phony\Spy\Exception\UndefinedCallException');
    }

    public function testAssertAlwaysThrewFailureExpectingException()
    {
        $this->subject->setCalls(array($this->callC, $this->callD));
        $expected = <<<'EOD'
Expected every call to throw exception equal to RuntimeException(). Actually threw:
    - RuntimeException('You done goofed.')
    - RuntimeException('Consequences will never be the same.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertAlwaysThrew(new RuntimeException());
    }

    public function testAssertAlwaysThrewFailureExpectingExceptionWithNoCalls()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected exception equal to RuntimeException(). Never called.'
        );
        $this->subject->assertAlwaysThrew(new RuntimeException());
    }

    public function testAssertAlwaysThrewFailureExpectingExceptionWithNoExceptions()
    {
        $this->subject->setCalls(array($this->callA, $this->callB));

        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected exception equal to RuntimeException(). Nothing thrown in 2 call(s)."
        );
        $this->subject->assertAlwaysThrew(new RuntimeException());
    }

    public function testAssertAlwaysThrewFailureExpectingMatcher()
    {
        $this->subject->setCalls(array($this->callC, $this->callD));
        $expected = <<<'EOD'
Expected every call to throw exception like <RuntimeException Object (...)>. Actually threw:
    - RuntimeException('You done goofed.')
    - RuntimeException('Consequences will never be the same.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertAlwaysThrew(new EqualToMatcher(new RuntimeException()));
    }

    public function testAssertAlwaysThrewFailureExpectingMatcherWithNoCalls()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected exception like <RuntimeException Object (...)>. Never called.'
        );
        $this->subject->assertAlwaysThrew(new EqualToMatcher(new RuntimeException()));
    }

    public function testAssertAlwaysThrewFailureExpectingMatcherWithNoExceptions()
    {
        $this->subject->setCalls(array($this->callA, $this->callB));

        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected exception like <RuntimeException Object (...)>. ' .
                'Nothing thrown in 2 call(s).'
        );
        $this->subject->assertAlwaysThrew(new EqualToMatcher(new RuntimeException()));
    }

    public function testAssertAlwaysThrewFailureInvalidInput()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Unable to match exceptions against stdClass Object ()."
        );
        $this->subject->assertAlwaysThrew((object) array());
    }

    public function testMergeCalls()
    {
        $spyA = new Spy();
        $spyA->setCalls(array($this->callC));
        $spyB = new Spy();
        $spyB->setCalls(array($this->callB, $this->callD));
        $spyC = new Spy();
        $spyC->setCalls(array($this->callA, $this->callB));

        $this->assertSame($this->calls, SpyVerifier::mergeCalls(array($spyA, $spyB, $spyC)));
    }

    public function testCompareCallOrder()
    {
        $this->assertSame(0, SpyVerifier::compareCallOrder($this->callA, $this->callA));
        $this->assertLessThan(0, SpyVerifier::compareCallOrder($this->callA, $this->callB));
        $this->assertGreaterThan(0, SpyVerifier::compareCallOrder($this->callB, $this->callA));
    }

    protected function thisValue($closure)
    {
        $reflectorReflector = new ReflectionClass('ReflectionFunction');
        if (!$reflectorReflector->hasMethod('getClosureThis')) {
            return null;
        }

        $reflector = new ReflectionFunction($closure);

        return $reflector->getClosureThis();
    }
}
