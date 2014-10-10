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

use Eloquent\Phony\Assertion\Recorder\AssertionRecorder;
use Eloquent\Phony\Assertion\Renderer\AssertionRenderer;
use Eloquent\Phony\Call\Call;
use Eloquent\Phony\Call\Factory\CallVerifierFactory;
use Eloquent\Phony\Cardinality\Cardinality;
use Eloquent\Phony\Event\EventCollection;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Matcher\EqualToMatcher;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Test\TestCallFactory;
use Eloquent\Phony\Test\TestClass;
use Exception;
use PHPUnit_Framework_TestCase;
use RuntimeException;

class SpyVerifierTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->callback = 'implode';
        $this->callFactory = new TestCallFactory();
        $this->spy = new Spy($this->callback, false, $this->callFactory);

        $this->matcherFactory = new MatcherFactory();
        $this->matcherVerifier = new MatcherVerifier();
        $this->callVerifierFactory = new CallVerifierFactory();
        $this->assertionRecorder = new AssertionRecorder();
        $this->assertionRenderer = new AssertionRenderer();
        $this->invocableInspector = new InvocableInspector();
        $this->subject = new SpyVerifier(
            $this->spy,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );

        $this->callEventFactory = $this->callFactory->eventFactory();

        $this->returnValueA = 'x';
        $this->returnValueB = 'y';
        $this->exceptionA = new RuntimeException('You done goofed.');
        $this->exceptionB = new RuntimeException('Consequences will never be the same.');
        $this->thisValueA = new TestClass();
        $this->thisValueB = new TestClass();
        $this->arguments = array('a', 'b', 'c');
        $this->matchers = $this->matcherFactory->adaptAll($this->arguments);
        $this->otherMatcher = $this->matcherFactory->adapt('d');
        $this->callA = $this->callFactory->create(
            $this->callEventFactory->createCalled(array($this->thisValueA, 'methodA'), $this->arguments),
            $this->callEventFactory->createReturned($this->returnValueA)
        );
        $this->callAResponse = $this->callA->responseEvent();
        $this->callB = $this->callFactory->create(
            $this->callEventFactory->createCalled(array($this->thisValueB, 'methodA')),
            $this->callEventFactory->createReturned($this->returnValueB)
        );
        $this->callBResponse = $this->callB->responseEvent();
        $this->callC = $this->callFactory->create(
            $this->callEventFactory->createCalled(array($this->thisValueA, 'methodA'), $this->arguments),
            $this->callEventFactory->createThrew($this->exceptionA)
        );
        $this->callCResponse = $this->callC->responseEvent();
        $this->callD = $this->callFactory->create(
            $this->callEventFactory->createCalled('implode'),
            $this->callEventFactory->createThrew($this->exceptionB)
        );
        $this->callDResponse = $this->callD->responseEvent();
        $this->calls = array($this->callA, $this->callB, $this->callC, $this->callD);
        $this->wrappedCallA = $this->callVerifierFactory->adapt($this->callA);
        $this->wrappedCallB = $this->callVerifierFactory->adapt($this->callB);
        $this->wrappedCallC = $this->callVerifierFactory->adapt($this->callC);
        $this->wrappedCallD = $this->callVerifierFactory->adapt($this->callD);
        $this->wrappedCalls = array($this->wrappedCallA, $this->wrappedCallB, $this->wrappedCallC, $this->wrappedCallD);

        $this->callFactory->reset();
    }

    public function testConstructor()
    {
        $this->assertSame($this->spy, $this->subject->spy());
        $this->assertSame($this->matcherFactory, $this->subject->matcherFactory());
        $this->assertSame($this->matcherVerifier, $this->subject->matcherVerifier());
        $this->assertSame($this->callVerifierFactory, $this->subject->callVerifierFactory());
        $this->assertSame($this->assertionRecorder, $this->subject->assertionRecorder());
        $this->assertSame($this->assertionRenderer, $this->subject->assertionRenderer());
        $this->assertSame($this->invocableInspector, $this->subject->invocableInspector());
        $this->assertEquals(new Cardinality(1, null), $this->subject->cardinality());
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
        $this->assertSame(InvocableInspector::instance(), $this->subject->invocableInspector());
    }

    public function testProxyMethods()
    {
        $this->assertSame($this->callback, $this->subject->callback());
    }

    public function testSetCalls()
    {
        $this->subject->setCalls($this->calls);

        $this->assertSame($this->calls, $this->subject->spy()->recordedCalls());
    }

    public function testAddCall()
    {
        $this->subject->addCall($this->callA);

        $this->assertSame(array($this->callA), $this->subject->spy()->recordedCalls());

        $this->subject->addCall($this->callB);

        $this->assertSame(array($this->callA, $this->callB), $this->subject->spy()->recordedCalls());
    }

    public function testCalls()
    {
        $this->assertSame(array(), $this->subject->recordedCalls());

        $this->subject->setCalls($this->calls);

        $this->assertEquals($this->wrappedCalls, $this->subject->recordedCalls());
    }

    public function testInvokeMethods()
    {
        $verifier = $this->subject;
        $spy = $verifier->spy();
        $verifier->invokeWith(array(array('a')));
        $verifier->invoke(array('b', 'c'));
        $verifier(array('d'));
        $this->callFactory->reset();
        $expected = array(
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy->callback(), array(array('a'))),
                $this->callEventFactory->createReturned('a')
            ),
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy->callback(), array(array('b', 'c'))),
                $this->callEventFactory->createReturned('bc')
            ),
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy->callback(), array(array('d'))),
                $this->callEventFactory->createReturned('d')
            ),
        );

        $this->assertEquals($expected, $this->spy->recordedCalls());
    }

    public function testInvokeMethodsWithoutSubject()
    {
        $spy = new Spy(null, false, $this->callFactory);
        $verifier = new SpyVerifier($spy);
        $verifier->invokeWith(array('a'));
        $verifier->invoke('b', 'c');
        $verifier('d');
        $this->callFactory->reset();
        $expected = array(
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy->callback(), array('a')),
                $this->callEventFactory->createReturned()
            ),
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy->callback(), array('b', 'c')),
                $this->callEventFactory->createReturned()
            ),
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy->callback(), array('d')),
                $this->callEventFactory->createReturned()
            ),
        );

        $this->assertEquals($expected, $spy->recordedCalls());
    }

    public function testInvokeWithExceptionThrown()
    {
        $exceptions = array(new Exception(), new Exception(), new Exception());
        $callback = function () use (&$exceptions) {
            list(, $exception) = each($exceptions);
            throw $exception;
        };
        $spy = new Spy($callback, false, $this->callFactory);
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
        $this->callFactory->reset();
        $expected = array(
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy->callback(), array('a')),
                $this->callEventFactory->createThrew($exceptions[0])
            ),
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy->callback(), array('b', 'c')),
                $this->callEventFactory->createThrew($exceptions[1])
            ),
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy->callback(), array('d')),
                $this->callEventFactory->createThrew($exceptions[2])
            ),
        );

        $this->assertEquals($expected, $spy->recordedCalls());
    }

    public function testInvokeWithWithReferenceParameters()
    {
        $callback = function (&$argument) {
            $argument = 'x';
        };
        $spy = new Spy($callback, false, $this->callFactory);
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

    public function testCheckCalled()
    {
        $this->assertFalse((boolean) $this->subject->checkCalled());

        $this->subject->setCalls($this->calls);

        $this->assertTrue((boolean) $this->subject->checkCalled());
    }

    public function testCalled()
    {
        $this->subject->setCalls($this->calls);
        $expected = new EventCollection($this->calls);

        $this->assertEquals($expected, $this->subject->called());
    }

    public function testCalledFailure()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', 'Never called.');
        $this->subject->called();
    }

    // public function testCalledOnce()
    // {
    //     $this->assertFalse((boolean) $this->subject->calledOnce());

    //     $this->subject->addCall($this->callA);

    //     $this->assertTrue((boolean) $this->subject->calledOnce());

    //     $this->subject->addCall($this->callB);

    //     $this->assertFalse((boolean) $this->subject->calledOnce());
    // }

    // public function testAssertCalledOnce()
    // {
    //     $this->subject->addCall($this->callA);
    //     $expected = new EventCollection(array($this->callA));

    //     $this->assertEquals($expected, $this->subject->assertCalledOnce());
    // }

    // public function testAssertCalledOnceFailureWithNoCalls()
    // {
    //     $this->setExpectedException(
    //         'Eloquent\Phony\Assertion\Exception\AssertionException',
    //         'Expected 1 call. Called 0 time(s).'
    //     );
    //     $this->subject->assertCalledOnce();
    // }

    // public function testAssertCalledOnceFailureWithMultipleCalls()
    // {
    //     $this->subject->setCalls($this->calls);

    //     $this->setExpectedException(
    //         'Eloquent\Phony\Assertion\Exception\AssertionException',
    //         'Expected 1 call. Called 4 time(s).'
    //     );
    //     $this->subject->assertCalledOnce();
    // }

    // public function testCalledTimes()
    // {
    //     $this->assertTrue((boolean) $this->subject->calledTimes(0));
    //     $this->assertFalse((boolean) $this->subject->calledTimes(4));

    //     $this->subject->setCalls($this->calls);

    //     $this->assertFalse((boolean) $this->subject->calledTimes(0));
    //     $this->assertTrue((boolean) $this->subject->calledTimes(4));
    // }

    // public function testAssertCalledTimes()
    // {
    //     $this->subject->setCalls($this->calls);
    //     $expected = new EventCollection($this->calls);

    //     $this->assertEquals($expected, $this->subject->assertCalledTimes(4));
    // }

    // public function testAssertCalledTimesFailure()
    // {
    //     $this->subject->setCalls($this->calls);

    //     $this->setExpectedException(
    //         'Eloquent\Phony\Assertion\Exception\AssertionException',
    //         'Expected 2 call(s). Called 4 time(s).'
    //     );
    //     $this->subject->assertCalledTimes(2);
    // }

    public function calledWithData()
    {
        //                                    arguments                  calledWith calledWithExactly
        return array(
            'Exact arguments'        => array(array('a', 'b', 'c'),      true,      true),
            'First arguments'        => array(array('a', 'b'),           true,      false),
            'Single argument'        => array(array('a'),                true,      false),
            'Last arguments'         => array(array('b', 'c'),           false,     false),
            'Last argument'          => array(array('c'),                false,     false),
            'Extra arguments'        => array(array('a', 'b', 'c', 'd'), false,     false),
            'First argument differs' => array(array('d', 'b', 'c'),      false,     false),
            'Last argument differs'  => array(array('a', 'b', 'd'),      false,     false),
            'Unused argument'        => array(array('d'),                false,     false),
        );
    }

    /**
     * @dataProvider calledWithData
     */
    public function testCheckCalledWith(array $arguments, $calledWith, $calledWithExactly)
    {
        $this->subject->setCalls($this->calls);
        $matchers = $this->matcherFactory->adaptAll($arguments);

        $this->assertSame($calledWith, (boolean) call_user_func_array(array($this->subject, 'checkCalledWith'), $arguments));
        $this->assertSame($calledWith, (boolean) call_user_func_array(array($this->subject, 'checkCalledWith'), $matchers));
    }

    public function testCheckCalledWithWithEmptyArguments()
    {
        $this->subject->setCalls($this->calls);

        $this->assertTrue((boolean) $this->subject->checkCalledWith());
    }

    public function testCheckCalledWithWithNoCalls()
    {
        $this->assertFalse((boolean) $this->subject->checkCalledWith());
    }

    public function testCalledWith()
    {
        $this->subject->setCalls($this->calls);
        $expected = new EventCollection(array($this->callA, $this->callC));

        $this->assertEquals($expected, $this->subject->calledWith('a', 'b', 'c'));
        $this->assertEquals(
            $expected,
            $this->subject->calledWith($this->matchers[0], $this->matchers[1], $this->matchers[2])
        );
        $this->assertEquals($expected, $this->subject->calledWith('a', 'b'));
        $this->assertEquals($expected, $this->subject->calledWith($this->matchers[0], $this->matchers[1]));
        $this->assertEquals($expected, $this->subject->calledWith('a'));
        $this->assertEquals($expected, $this->subject->calledWith($this->matchers[0]));
        $this->assertEquals(new EventCollection($this->calls), $this->subject->calledWith());
    }

    public function testCalledWithFailure()
    {
        $this->subject->setCalls($this->calls);
        $expected = <<<'EOD'
Expected call with arguments like:
    <'b'>, <'c'>, <any>*
Calls:
    - 'a', 'b', 'c'
    - <none>
    - 'a', 'b', 'c'
    - <none>
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->calledWith('b', 'c');
    }

    public function testCalledWithFailureWithNoCalls()
    {
        $expected = <<<'EOD'
Expected call with arguments like:
    <'b'>, <'c'>, <any>*
Never called.
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->calledWith('b', 'c');
    }

//     public function testCalledOnceWith()
//     {
//         $this->assertFalse((boolean) $this->subject->calledOnceWith());

//         $this->subject->setCalls(array($this->callA, $this->callB));

//         $this->assertTrue((boolean) $this->subject->calledOnceWith('a', 'b', 'c'));
//         $this->assertTrue(
//             (boolean) $this->subject->calledOnceWith($this->matchers[0], $this->matchers[1], $this->matchers[2])
//         );
//         $this->assertTrue((boolean) $this->subject->calledOnceWith('a'));
//         $this->assertTrue((boolean) $this->subject->calledOnceWith($this->matchers[0]));
//         $this->assertFalse((boolean) $this->subject->calledOnceWith());
//         $this->assertFalse((boolean) $this->subject->calledOnceWith());
//     }

//     public function testAssertCalledOnceWith()
//     {
//         $this->subject->setCalls(array($this->callA, $this->callB));
//         $expected = new EventCollection(array($this->callA));

//         $this->assertEquals($expected, $this->subject->assertCalledOnceWith('a', 'b', 'c'));
//         $this->assertEquals(
//             $expected,
//             $this->subject->assertCalledOnceWith($this->matchers[0], $this->matchers[1], $this->matchers[2])
//         );
//         $this->assertEquals($expected, $this->subject->assertCalledOnceWith('a'));
//         $this->assertEquals($expected, $this->subject->assertCalledOnceWith($this->matchers[0]));
//     }

//     public function testAssertCalledOnceWithFailure()
//     {
//         $this->subject->setCalls($this->calls);
//         $expected = <<<'EOD'
// Expected 1 call with arguments like:
//     <'a'>, <'b'>, <'c'>, <any>*
// Actual calls:
//     - 'a', 'b', 'c'
//     - <none>
//     - 'a', 'b', 'c'
//     - <none>
// EOD;

//         $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
//         $this->subject->assertCalledOnceWith('a', 'b', 'c');
//     }

//     public function testAssertCalledOnceWithFailureWithNoCalls()
//     {
//         $expected = <<<'EOD'
// Expected arguments like:
//     <'a'>, <'b'>, <'c'>, <any>*
// Never called.
// EOD;

//         $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
//         $this->subject->assertCalledOnceWith('a', 'b', 'c');
//     }

//     public function testCalledTimesWith()
//     {
//         $this->subject->setCalls($this->calls);

//         $this->assertTrue((boolean) $this->subject->calledTimesWith(2, 'a', 'b', 'c'));
//         $this->assertTrue(
//             (boolean) $this->subject->calledTimesWith(2, $this->matchers[0], $this->matchers[1], $this->matchers[2])
//         );
//         $this->assertTrue((boolean) $this->subject->calledTimesWith(2, 'a'));
//         $this->assertTrue((boolean) $this->subject->calledTimesWith(2, $this->matchers[0]));
//         $this->assertTrue((boolean) $this->subject->calledTimesWith(4));
//         $this->assertTrue((boolean) $this->subject->calledTimesWith(4));
//         $this->assertFalse((boolean) $this->subject->calledTimesWith(1, 'a', 'b', 'c'));
//         $this->assertFalse(
//             (boolean) $this->subject->calledTimesWith(1, $this->matchers[0], $this->matchers[1], $this->matchers[2])
//         );
//         $this->assertFalse((boolean) $this->subject->calledTimesWith(1, 'a'));
//         $this->assertFalse((boolean) $this->subject->calledTimesWith(1, $this->matchers[0]));
//         $this->assertFalse((boolean) $this->subject->calledTimesWith(1));
//         $this->assertFalse((boolean) $this->subject->calledTimesWith(1));
//     }

//     public function testAssertCalledTimesWith()
//     {
//         $this->subject->setCalls($this->calls);
//         $expected = new EventCollection(array($this->callA, $this->callC));

//         $this->assertEquals($expected, $this->subject->assertCalledTimesWith(2, 'a', 'b', 'c'));
//         $this->assertEquals(
//             $expected,
//             $this->subject->assertCalledTimesWith(2, $this->matchers[0], $this->matchers[1], $this->matchers[2])
//         );
//         $this->assertEquals($expected, $this->subject->assertCalledTimesWith(2, 'a'));
//         $this->assertEquals($expected, $this->subject->assertCalledTimesWith(2, $this->matchers[0]));

//         $expected = new EventCollection($this->calls);

//         $this->assertEquals($expected, $this->subject->assertCalledTimesWith(4));
//         $this->assertEquals($expected, $this->subject->assertCalledTimesWith(4));
//     }

//     public function testAssertCalledTimesWithFailure()
//     {
//         $this->subject->setCalls($this->calls);
//         $expected = <<<'EOD'
// Expected 4 call(s) with arguments like:
//     <'a'>, <'b'>, <'c'>, <any>*
// Actual calls:
//     - 'a', 'b', 'c'
//     - <none>
//     - 'a', 'b', 'c'
//     - <none>
// EOD;

//         $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
//         $this->subject->assertCalledTimesWith(4, 'a', 'b', 'c');
//     }

//     public function testAssertCalledTimesWithFailureWithNoCalls()
//     {
//         $expected = <<<'EOD'
// Expected arguments like:
//     <'a'>, <'b'>, <'c'>, <any>*
// Never called.
// EOD;

//         $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
//         $this->subject->assertCalledTimesWith(4, 'a', 'b', 'c');
//     }

//     /**
//      * @dataProvider calledWithData
//      */
//     public function testAlwaysCalledWith(array $arguments, $calledWith, $calledWithExactly)
//     {
//         $this->subject->setCalls(array($this->callA, $this->callA));
//         $matchers = $this->matcherFactory->adaptAll($arguments);

//         $this->assertSame(
//             $calledWith,
//             (boolean) call_user_func_array(array($this->subject, 'alwaysCalledWith'), $arguments)
//         );
//         $this->assertSame(
//             $calledWith,
//             (boolean) call_user_func_array(array($this->subject, 'alwaysCalledWith'), $matchers)
//         );
//     }

//     /**
//      * @dataProvider calledWithData
//      */
//     public function testAlwaysCalledWithWithDifferingCalls(array $arguments, $calledWith, $calledWithExactly)
//     {
//         $this->subject->setCalls(array($this->callA, $this->callB));
//         $matchers = $this->matcherFactory->adaptAll($arguments);

//         $this->assertFalse((boolean) call_user_func_array(array($this->subject, 'alwaysCalledWith'), $arguments));
//         $this->assertFalse((boolean) call_user_func_array(array($this->subject, 'alwaysCalledWith'), $matchers));
//     }

//     public function testAlwaysCalledWithWithEmptyArguments()
//     {
//         $this->subject->setCalls($this->calls);

//         $this->assertTrue((boolean) $this->subject->alwaysCalledWith());
//     }

//     public function testAlwaysCalledWithWithNoCalls()
//     {
//         $this->assertFalse((boolean) $this->subject->alwaysCalledWith());
//     }

//     public function testAssertAlwaysCalledWith()
//     {
//         $this->subject->setCalls(array($this->callA, $this->callA));
//         $expected = new EventCollection(array($this->callA, $this->callA));

//         $this->assertEquals($expected, $this->subject->assertAlwaysCalledWith('a', 'b', 'c'));
//         $this->assertEquals(
//             $expected,
//             $this->subject->assertAlwaysCalledWith($this->matchers[0], $this->matchers[1], $this->matchers[2])
//         );
//         $this->assertEquals($expected, $this->subject->assertAlwaysCalledWith('a', 'b'));
//         $this->assertEquals($expected, $this->subject->assertAlwaysCalledWith($this->matchers[0], $this->matchers[1]));
//         $this->assertEquals($expected, $this->subject->assertAlwaysCalledWith('a'));
//         $this->assertEquals($expected, $this->subject->assertAlwaysCalledWith($this->matchers[0]));
//         $this->assertEquals($expected, $this->subject->assertAlwaysCalledWith());
//     }

//     public function testAssertAlwaysCalledWithFailure()
//     {
//         $this->subject->setCalls($this->calls);

//         $expected = <<<'EOD'
// Expected every call with arguments like:
//     <'a'>, <'b'>, <'c'>, <any>*
// Actual calls:
//     - 'a', 'b', 'c'
//     - <none>
//     - 'a', 'b', 'c'
//     - <none>
// EOD;
//         $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
//         $this->subject->assertAlwaysCalledWith('a', 'b', 'c');
//     }

//     public function testAssertAlwaysCalledWithFailureWithNoCalls()
//     {
//         $expected = <<<'EOD'
// Expected every call with arguments like:
//     <'a'>, <'b'>, <'c'>, <any>*
// Never called.
// EOD;
//         $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
//         $this->subject->assertAlwaysCalledWith('a', 'b', 'c');
//     }

    /**
     * @dataProvider calledWithData
     */
    public function testCheckCalledWithExactly(array $arguments, $calledWith, $calledWithExactly)
    {
        $this->subject->setCalls($this->calls);
        $matchers = $this->matcherFactory->adaptAll($arguments);

        $this->assertSame(
            $calledWithExactly,
            (boolean) call_user_func_array(array($this->subject, 'checkCalledWithExactly'), $arguments)
        );
        $this->assertSame(
            $calledWithExactly,
            (boolean) call_user_func_array(array($this->subject, 'checkCalledWithExactly'), $matchers)
        );
    }

    public function testCheckCalledWithExactlyWithEmptyArguments()
    {
        $this->subject->setCalls($this->calls);

        $this->assertTrue((boolean) $this->subject->checkCalledWithExactly());
    }

    public function testCheckCalledWithExactlyWithNoCalls()
    {
        $this->assertFalse((boolean) $this->subject->checkCalledWithExactly());
    }

    public function testCalledWithExactly()
    {
        $this->subject->setCalls($this->calls);
        $expected = new EventCollection(array($this->callA, $this->callC));

        $this->assertEquals($expected, $this->subject->calledWithExactly('a', 'b', 'c'));
        $this->assertEquals(
            $expected,
            $this->subject->calledWithExactly($this->matchers[0], $this->matchers[1], $this->matchers[2])
        );
    }

    public function testCalledWithExactlyFailure()
    {
        $this->subject->setCalls($this->calls);

        $expected = <<<'EOD'
Expected call with arguments like:
    <'b'>, <'c'>
Calls:
    - 'a', 'b', 'c'
    - <none>
    - 'a', 'b', 'c'
    - <none>
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->calledWithExactly('b', 'c');
    }

    public function testCalledWithExactlyFailureWithNoCalls()
    {
        $expected = <<<'EOD'
Expected call with arguments like:
    <'b'>, <'c'>
Never called.
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->calledWithExactly('b', 'c');
    }

//     public function testCalledOnceWithExactly()
//     {
//         $this->assertFalse((boolean) $this->subject->calledOnceWithExactly());

//         $this->subject->setCalls(array($this->callA, $this->callB, $this->callD));

//         $this->assertTrue((boolean) $this->subject->calledOnceWithExactly('a', 'b', 'c'));
//         $this->assertTrue(
//             (boolean) $this->subject->calledOnceWithExactly($this->matchers[0], $this->matchers[1], $this->matchers[2])
//         );
//         $this->assertFalse((boolean) $this->subject->calledOnceWithExactly());
//         $this->assertFalse((boolean) $this->subject->calledOnceWithExactly());
//     }

//     public function testAssertCalledOnceWithExactly()
//     {
//         $this->subject->setCalls(array($this->callA, $this->callB));
//         $expected = new EventCollection(array($this->callA));

//         $this->assertEquals($expected, $this->subject->assertCalledOnceWithExactly('a', 'b', 'c'));
//         $this->assertEquals(
//             $expected,
//             $this->subject->assertCalledOnceWithExactly($this->matchers[0], $this->matchers[1], $this->matchers[2])
//         );
//     }

//     public function testAssertCalledOnceWithExactlyFailure()
//     {
//         $this->subject->setCalls($this->calls);
//         $expected = <<<'EOD'
// Expected 1 call with arguments like:
//     <'a'>, <'b'>, <'c'>
// Actual calls:
//     - 'a', 'b', 'c'
//     - <none>
//     - 'a', 'b', 'c'
//     - <none>
// EOD;

//         $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
//         $this->subject->assertCalledOnceWithExactly('a', 'b', 'c');
//     }

//     public function testAssertCalledOnceWithExactlyFailureWithNoCalls()
//     {
//         $expected = <<<'EOD'
// Expected arguments like:
//     <'a'>, <'b'>, <'c'>
// Never called.
// EOD;

//         $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
//         $this->subject->assertCalledOnceWithExactly('a', 'b', 'c');
//     }

//     public function testCalledTimesWithExactly()
//     {
//         $this->subject->setCalls($this->calls);

//         $this->assertTrue((boolean) $this->subject->calledTimesWithExactly(2, 'a', 'b', 'c'));
//         $this->assertTrue(
//             (boolean) $this->subject
//                 ->calledTimesWithExactly(2, $this->matchers[0], $this->matchers[1], $this->matchers[2])
//         );
//         $this->assertTrue((boolean) $this->subject->calledTimesWithExactly(2));
//         $this->assertTrue((boolean) $this->subject->calledTimesWithExactly(2));
//         $this->assertFalse((boolean) $this->subject->calledTimesWithExactly(1, 'a', 'b', 'c'));
//         $this->assertFalse(
//             (boolean) $this->subject
//                 ->calledTimesWithExactly(1, $this->matchers[0], $this->matchers[1], $this->matchers[2])
//         );
//         $this->assertFalse((boolean) $this->subject->calledTimesWithExactly(1, 'a'));
//         $this->assertFalse((boolean) $this->subject->calledTimesWithExactly(1, $this->matchers[0]));
//         $this->assertFalse((boolean) $this->subject->calledTimesWithExactly(1));
//         $this->assertFalse((boolean) $this->subject->calledTimesWithExactly(1));
//     }

//     public function testAssertCalledTimesWithExactly()
//     {
//         $this->subject->setCalls($this->calls);
//         $expected = new EventCollection(array($this->callA, $this->callC));

//         $this->assertEquals($expected, $this->subject->assertCalledTimesWithExactly(2, 'a', 'b', 'c'));
//         $this->assertEquals(
//             $expected,
//             $this->subject->assertCalledTimesWithExactly(2, $this->matchers[0], $this->matchers[1], $this->matchers[2])
//         );

//         $expected = new EventCollection(array($this->callB, $this->callD));

//         $this->assertEquals($expected, $this->subject->assertCalledTimesWithExactly(2));
//         $this->assertEquals($expected, $this->subject->assertCalledTimesWithExactly(2));
//     }

//     public function testAssertCalledTimesWithExactlyFailure()
//     {
//         $this->subject->setCalls($this->calls);
//         $expected = <<<'EOD'
// Expected 4 call(s) with arguments like:
//     <'a'>, <'b'>, <'c'>
// Actual calls:
//     - 'a', 'b', 'c'
//     - <none>
//     - 'a', 'b', 'c'
//     - <none>
// EOD;

//         $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
//         $this->subject->assertCalledTimesWithExactly(4, 'a', 'b', 'c');
//     }

//     public function testAssertCalledTimesWithExactlyFailureWithNoCalls()
//     {
//         $expected = <<<'EOD'
// Expected arguments like:
//     <'a'>, <'b'>, <'c'>
// Never called.
// EOD;

//         $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
//         $this->subject->assertCalledTimesWithExactly(4, 'a', 'b', 'c');
//     }

//     /**
//      * @dataProvider calledWithData
//      */
//     public function testAlwaysCalledWithExactly(array $arguments, $calledWith, $calledWithExactly)
//     {
//         $this->subject->setCalls(array($this->callA, $this->callA));
//         $matchers = $this->matcherFactory->adaptAll($arguments);

//         $this->assertSame(
//             $calledWithExactly,
//             (boolean) call_user_func_array(array($this->subject, 'alwaysCalledWithExactly'), $arguments)
//         );
//         $this->assertSame(
//             $calledWithExactly,
//             (boolean) call_user_func_array(array($this->subject, 'alwaysCalledWithExactly'), $matchers)
//         );
//     }

//     /**
//      * @dataProvider calledWithData
//      */
//     public function testAlwaysCalledWithExactlyWithDifferingCalls(array $arguments, $calledWith, $calledWithExactly)
//     {
//         $this->subject->setCalls(array($this->callA, $this->callB));
//         $matchers = $this->matcherFactory->adaptAll($arguments);

//         $this->assertFalse(
//             (boolean) call_user_func_array(array($this->subject, 'alwaysCalledWithExactly'), $arguments)
//         );
//         $this->assertFalse((boolean) call_user_func_array(array($this->subject, 'alwaysCalledWithExactly'), $matchers));
//     }

//     public function testAlwaysCalledWithExactlyWithEmptyArguments()
//     {
//         $this->subject->setCalls($this->calls);

//         $this->assertFalse((boolean) $this->subject->alwaysCalledWithExactly());
//     }

//     public function testAlwaysCalledWithExactlyWithNoCalls()
//     {
//         $this->assertFalse((boolean) $this->subject->alwaysCalledWithExactly());
//     }

//     public function testAssertAlwaysCalledWithExactly()
//     {
//         $this->subject->setCalls(array($this->callA, $this->callA));
//         $expected = new EventCollection(array($this->callA, $this->callA));

//         $this->assertEquals($expected, $this->subject->assertAlwaysCalledWithExactly('a', 'b', 'c'));
//         $this->assertEquals(
//             $expected,
//             $this->subject->assertAlwaysCalledWithExactly($this->matchers[0], $this->matchers[1], $this->matchers[2])
//         );
//     }

//     public function testAssertAlwaysCalledWithExactlyFailure()
//     {
//         $this->subject->setCalls($this->calls);

//         $expected = <<<'EOD'
// Expected every call with arguments like:
//     <'a'>, <'b'>, <'c'>
// Actual calls:
//     - 'a', 'b', 'c'
//     - <none>
//     - 'a', 'b', 'c'
//     - <none>
// EOD;
//         $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
//         $this->subject->assertAlwaysCalledWithExactly('a', 'b', 'c');
//     }

//     public function testAssertAlwaysCalledWithExactlyFailureWithNoCalls()
//     {
//         $expected = <<<'EOD'
// Expected every call with arguments like:
//     <'a'>, <'b'>, <'c'>
// Never called.
// EOD;
//         $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
//         $this->subject->assertAlwaysCalledWithExactly('a', 'b', 'c');
//     }

//     /**
//      * @dataProvider calledWithData
//      */
//     public function testNeverCalledWith(array $arguments, $calledWith, $calledWithExactly)
//     {
//         $this->subject->setCalls($this->calls);
//         $matchers = $this->matcherFactory->adaptAll($arguments);

//         $this->assertSame(
//             !$calledWith,
//             (boolean) call_user_func_array(array($this->subject, 'neverCalledWith'), $arguments)
//         );
//         $this->assertSame(
//             !$calledWith,
//             (boolean) call_user_func_array(array($this->subject, 'neverCalledWith'), $matchers)
//         );
//     }

//     public function testNeverCalledWithWithEmptyArguments()
//     {
//         $this->subject->setCalls($this->calls);

//         $this->assertFalse((boolean) $this->subject->neverCalledWith());
//     }

//     public function testNeverCalledWithWithNoCalls()
//     {
//         $this->assertTrue((boolean) $this->subject->neverCalledWith());
//     }

//     public function testAssertNeverCalledWith()
//     {
//         $expected = new EventCollection();

//         $this->assertEquals($expected, $this->subject->assertNeverCalledWith());

//         $this->subject->setCalls($this->calls);

//         $this->assertEquals($expected, $this->subject->assertNeverCalledWith('b', 'c'));
//         $this->assertEquals($expected, $this->subject->assertNeverCalledWith($this->matchers[1], $this->matchers[2]));
//         $this->assertEquals($expected, $this->subject->assertNeverCalledWith('c'));
//         $this->assertEquals($expected, $this->subject->assertNeverCalledWith($this->matchers[2]));
//         $this->assertEquals($expected, $this->subject->assertNeverCalledWith('a', 'b', 'c', 'd'));
//         $this->assertEquals(
//             $expected,
//             $this->subject->assertNeverCalledWith(
//                 $this->matchers[0],
//                 $this->matchers[1],
//                 $this->matchers[2],
//                 $this->otherMatcher
//             )
//         );
//         $this->assertEquals($expected, $this->subject->assertNeverCalledWith('d', 'b', 'c'));
//         $this->assertEquals(
//             $expected,
//             $this->subject->assertNeverCalledWith($this->otherMatcher, $this->matchers[1], $this->matchers[2])
//         );
//         $this->assertEquals($expected, $this->subject->assertNeverCalledWith('a', 'b', 'd'));
//         $this->assertEquals(
//             $expected,
//             $this->subject->assertNeverCalledWith($this->matchers[0], $this->matchers[1], $this->otherMatcher)
//         );
//         $this->assertEquals($expected, $this->subject->assertNeverCalledWith('d'));
//         $this->assertEquals($expected, $this->subject->assertNeverCalledWith($this->otherMatcher));
//     }

//     public function testAssertNeverCalledWithFailure()
//     {
//         $this->subject->setCalls($this->calls);

//         $expected = <<<'EOD'
// Expected no call with arguments like:
//     <'a'>, <'b'>, <'c'>, <any>*
// Actual calls:
//     - 'a', 'b', 'c'
//     - <none>
//     - 'a', 'b', 'c'
//     - <none>
// EOD;
//         $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
//         $this->subject->assertNeverCalledWith('a', 'b', 'c');
//     }

//     /**
//      * @dataProvider calledWithData
//      */
//     public function testNeverCalledWithExactly(array $arguments, $calledWith, $calledWithExactly)
//     {
//         $this->subject->setCalls($this->calls);
//         $matchers = $this->matcherFactory->adaptAll($arguments);

//         $this->assertSame(
//             !$calledWithExactly,
//             (boolean) call_user_func_array(array($this->subject, 'neverCalledWithExactly'), $arguments)
//         );
//         $this->assertSame(
//             !$calledWithExactly,
//             (boolean) call_user_func_array(array($this->subject, 'neverCalledWithExactly'), $matchers)
//         );
//     }

//     public function testNeverCalledWithExactlyWithEmptyArguments()
//     {
//         $this->subject->setCalls($this->calls);

//         $this->assertFalse((boolean) $this->subject->neverCalledWithExactly());
//     }

//     public function testNeverCalledWithExactlyWithNoCalls()
//     {
//         $this->assertTrue((boolean) $this->subject->neverCalledWithExactly());
//     }

//     public function testAssertNeverCalledWithExactly()
//     {
//         $expected = new EventCollection();

//         $this->assertEquals($expected, $this->subject->assertNeverCalledWithExactly());

//         $this->subject->setCalls($this->calls);

//         $this->assertEquals($expected, $this->subject->assertNeverCalledWithExactly('a', 'b'));
//         $this->assertEquals(
//             $expected,
//             $this->subject->assertNeverCalledWithExactly($this->matchers[0], $this->matchers[1])
//         );
//         $this->assertEquals($expected, $this->subject->assertNeverCalledWithExactly('a'));
//         $this->assertEquals($expected, $this->subject->assertNeverCalledWithExactly($this->matchers[0]));
//         $this->assertEquals($expected, $this->subject->assertNeverCalledWithExactly('b', 'c'));
//         $this->assertEquals(
//             $expected,
//             $this->subject->assertNeverCalledWithExactly($this->matchers[1], $this->matchers[2])
//         );
//         $this->assertEquals($expected, $this->subject->assertNeverCalledWithExactly('c'));
//         $this->assertEquals($expected, $this->subject->assertNeverCalledWithExactly($this->matchers[2]));
//         $this->assertEquals($expected, $this->subject->assertNeverCalledWithExactly('a', 'b', 'c', 'd'));
//         $this->assertEquals(
//             $expected,
//             $this->subject->assertNeverCalledWithExactly(
//                 $this->matchers[0],
//                 $this->matchers[1],
//                 $this->matchers[2],
//                 $this->otherMatcher
//             )
//         );
//         $this->assertEquals($expected, $this->subject->assertNeverCalledWithExactly('d', 'b', 'c'));
//         $this->assertEquals(
//             $expected,
//             $this->subject->assertNeverCalledWithExactly($this->otherMatcher, $this->matchers[1], $this->matchers[2])
//         );
//         $this->assertEquals($expected, $this->subject->assertNeverCalledWithExactly('a', 'b', 'd'));
//         $this->assertEquals(
//             $expected,
//             $this->subject->assertNeverCalledWithExactly($this->matchers[0], $this->matchers[1], $this->otherMatcher)
//         );
//         $this->assertEquals($expected, $this->subject->assertNeverCalledWithExactly('d'));
//         $this->assertEquals($expected, $this->subject->assertNeverCalledWithExactly($this->otherMatcher));
//     }

//     public function testAssertNeverCalledWithExactlyFailure()
//     {
//         $this->subject->setCalls($this->calls);

//         $expected = <<<'EOD'
// Expected no call with arguments like:
//     <'a'>, <'b'>, <'c'>
// Actual calls:
//     - 'a', 'b', 'c'
//     - <none>
//     - 'a', 'b', 'c'
//     - <none>
// EOD;
//         $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
//         $this->subject->assertNeverCalledWithExactly('a', 'b', 'c');
//     }

//     public function testCheckCalledBefore()
//     {
//         $spyA = new Spy();
//         $spyA->setCalls(array($this->callA, $this->callC));
//         $spyB = new Spy();
//         $spyB->setCalls(array($this->callA));
//         $spyC = new Spy();

//         $this->assertFalse((boolean) $this->subject->checkCalledBefore($spyA));
//         $this->assertFalse((boolean) $this->subject->checkCalledBefore($spyB));
//         $this->assertFalse((boolean) $this->subject->checkCalledBefore($spyC));

//         $this->subject->setCalls(array($this->callB, $this->callD));

//         $this->assertTrue((boolean) $this->subject->checkCalledBefore($spyA));
//         $this->assertFalse((boolean) $this->subject->checkCalledBefore($spyB));
//         $this->assertFalse((boolean) $this->subject->checkCalledBefore($spyC));
//     }

//     public function testCalledBefore()
//     {
//         $this->subject->setCalls(array($this->callB, $this->callD));
//         $spy = new Spy();
//         $spy->setCalls(array($this->callA, $this->callC));
//         $expected = new EventCollection(array($this->callB));

//         $this->assertEquals($expected, $this->subject->calledBefore($spy));
//     }

//     public function testCalledBeforeFailure()
//     {
//         $this->subject->setCalls(array($this->callC, $this->callD));
//         $spy = new Spy();
//         $spy->setCalls(array($this->callA, $this->callB));
//         $spyVerifier = new SpyVerifier($spy);
//         $expected = <<<'EOD'
// Not called before supplied spy. Actual calls:
//     - Eloquent\Phony\Test\TestClass->methodA('a', 'b', 'c')
//     - Eloquent\Phony\Test\TestClass->methodA()
//     - Eloquent\Phony\Test\TestClass->methodA('a', 'b', 'c')
//     - implode()
// EOD;

//         $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
//         $this->subject->calledBefore($spyVerifier);
//     }

//     public function testCalledBeforeFailureNoCalls()
//     {
//         $spy = new Spy();
//         $spyVerifier = new SpyVerifier($spy);

//         $this->setExpectedException(
//             'Eloquent\Phony\Assertion\Exception\AssertionException',
//             "Not called before supplied spy. Never called."
//         );
//         $this->subject->calledBefore($spyVerifier);
//     }

//     public function testCalledBeforeFailureNoSuppliedSpyCalls()
//     {
//         $this->subject->setCalls($this->calls);
//         $spy = new Spy();
//         $spyVerifier = new SpyVerifier($spy);

//         $this->setExpectedException(
//             'Eloquent\Phony\Assertion\Exception\AssertionException',
//             "Not called before supplied spy. Supplied spy never called."
//         );
//         $this->subject->calledBefore($spyVerifier);
//     }

//     public function testCheckCalledAfter()
//     {
//         $spyA = new Spy();
//         $spyA->setCalls(array($this->callB, $this->callD));
//         $spyB = new Spy();
//         $spyB->setCalls(array($this->callD));
//         $spyC = new Spy();

//         $this->assertFalse((boolean) $this->subject->checkCalledAfter($spyA));
//         $this->assertFalse((boolean) $this->subject->checkCalledAfter($spyB));
//         $this->assertFalse((boolean) $this->subject->checkCalledAfter($spyC));

//         $this->subject->setCalls(array($this->callA, $this->callC));

//         $this->assertTrue((boolean) $this->subject->checkCalledAfter($spyA));
//         $this->assertFalse((boolean) $this->subject->checkCalledAfter($spyB));
//         $this->assertFalse((boolean) $this->subject->checkCalledAfter($spyC));
//     }

//     public function testCalledAfter()
//     {
//         $this->subject->setCalls(array($this->callA, $this->callD));
//         $spy = new Spy();
//         $spy->setCalls(array($this->callB, $this->callC));
//         $expected = new EventCollection(array($this->callD));

//         $this->assertEquals($expected, $this->subject->calledAfter($spy));
//     }

//     public function testCalledAfterFailure()
//     {
//         $this->subject->setCalls(array($this->callA, $this->callB));
//         $spy = new Spy();
//         $spy->setCalls(array($this->callC, $this->callD));
//         $spyVerifier = new SpyVerifier($spy);
//         $expected = <<<'EOD'
// Not called after supplied spy. Actual calls:
//     - Eloquent\Phony\Test\TestClass->methodA('a', 'b', 'c')
//     - Eloquent\Phony\Test\TestClass->methodA()
//     - Eloquent\Phony\Test\TestClass->methodA('a', 'b', 'c')
//     - implode()
// EOD;

//         $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
//         $this->subject->calledAfter($spyVerifier);
//     }

//     public function testCalledAfterFailureNoCalls()
//     {
//         $spy = new Spy();
//         $spyVerifier = new SpyVerifier($spy);

//         $this->setExpectedException(
//             'Eloquent\Phony\Assertion\Exception\AssertionException',
//             "Not called after supplied spy. Never called."
//         );
//         $this->subject->calledAfter($spyVerifier);
//     }

//     public function testCalledAfterFailureNoSuppliedSpyCalls()
//     {
//         $this->subject->setCalls($this->calls);
//         $spy = new Spy();
//         $spyVerifier = new SpyVerifier($spy);

//         $this->setExpectedException(
//             'Eloquent\Phony\Assertion\Exception\AssertionException',
//             "Not called after supplied spy. Supplied spy never called."
//         );
//         $this->subject->calledAfter($spyVerifier);
//     }

    public function testCheckCalledOn()
    {
        $this->assertFalse((boolean) $this->subject->checkCalledOn(null));
        $this->assertFalse((boolean) $this->subject->checkCalledOn($this->thisValueA));
        $this->assertFalse((boolean) $this->subject->checkCalledOn($this->thisValueB));
        $this->assertFalse((boolean) $this->subject->checkCalledOn(new EqualToMatcher($this->thisValueA)));
        $this->assertFalse((boolean) $this->subject->checkCalledOn((object) array()));

        $this->subject->setCalls($this->calls);

        $this->assertTrue((boolean) $this->subject->checkCalledOn(null));
        $this->assertTrue((boolean) $this->subject->checkCalledOn($this->thisValueA));
        $this->assertTrue((boolean) $this->subject->checkCalledOn($this->thisValueB));
        $this->assertTrue((boolean) $this->subject->checkCalledOn(new EqualToMatcher($this->thisValueA)));
        $this->assertFalse((boolean) $this->subject->checkCalledOn((object) array()));
    }

    public function testCalledOn()
    {
        $this->subject->setCalls($this->calls);

        $this->assertEquals(new EventCollection(array($this->callD)), $this->subject->calledOn(null));
        $this->assertEquals(
            new EventCollection(array($this->callA, $this->callC)),
            $this->subject->calledOn($this->thisValueA)
        );
        $this->assertEquals(
            new EventCollection(array($this->callB)),
            $this->subject->calledOn($this->thisValueB)
        );
        $this->assertEquals(
            new EventCollection(array($this->callA, $this->callB, $this->callC)),
            $this->subject->calledOn(new EqualToMatcher($this->thisValueA))
        );
    }

    public function testCalledOnFailure()
    {
        $this->subject->setCalls($this->calls);
        $expected = <<<'EOD'
Expected call on supplied object. Called on:
    - Eloquent\Phony\Test\TestClass Object ()
    - Eloquent\Phony\Test\TestClass Object ()
    - Eloquent\Phony\Test\TestClass Object ()
    - null
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->calledOn((object) array());
    }

    public function testCalledOnFailureWithNoCalls()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected call on supplied object. Never called.'
        );
        $this->subject->calledOn((object) array());
    }

    public function testCalledOnFailureWithMatcher()
    {
        $this->subject->setCalls($this->calls);
        $expected = <<<'EOD'
Expected call on object like <stdClass Object (...)>. Called on:
    - Eloquent\Phony\Test\TestClass Object ()
    - Eloquent\Phony\Test\TestClass Object ()
    - Eloquent\Phony\Test\TestClass Object ()
    - null
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->calledOn(new EqualToMatcher((object) array('property' => 'value')));
    }

    public function testCalledOnFailureWithMatcherWithNoCalls()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected call on object like <stdClass Object (...)>. Never called.'
        );
        $this->subject->calledOn(new EqualToMatcher((object) array('property' => 'value')));
    }

//     public function testAlwaysCalledOn()
//     {
//         $this->assertFalse((boolean) $this->subject->alwaysCalledOn(null));
//         $this->assertFalse((boolean) $this->subject->alwaysCalledOn($this->thisValueA));
//         $this->assertFalse((boolean) $this->subject->alwaysCalledOn($this->thisValueB));
//         $this->assertFalse((boolean) $this->subject->alwaysCalledOn(new EqualToMatcher($this->thisValueA)));
//         $this->assertFalse((boolean) $this->subject->alwaysCalledOn((object) array()));

//         $this->subject->setCalls($this->calls);

//         $this->assertFalse((boolean) $this->subject->alwaysCalledOn(null));
//         $this->assertFalse((boolean) $this->subject->alwaysCalledOn($this->thisValueA));
//         $this->assertFalse((boolean) $this->subject->alwaysCalledOn($this->thisValueB));
//         $this->assertFalse((boolean) $this->subject->alwaysCalledOn(new EqualToMatcher($this->thisValueA)));
//         $this->assertFalse((boolean) $this->subject->alwaysCalledOn((object) array()));

//         $this->subject->setCalls(array($this->callC, $this->callC));

//         $this->assertTrue((boolean) $this->subject->alwaysCalledOn($this->thisValueA));
//         $this->assertTrue((boolean) $this->subject->alwaysCalledOn(new EqualToMatcher($this->thisValueA)));
//         $this->assertFalse((boolean) $this->subject->alwaysCalledOn(null));
//         $this->assertFalse((boolean) $this->subject->alwaysCalledOn($this->thisValueB));
//         $this->assertFalse((boolean) $this->subject->alwaysCalledOn((object) array()));
//     }

//     public function testAssertAlwaysCalledOn()
//     {
//         $this->subject->setCalls(array($this->callC, $this->callC));
//         $expected = new EventCollection(array($this->callC, $this->callC));

//         $this->assertEquals($expected, $this->subject->assertAlwaysCalledOn($this->thisValueA));
//         $this->assertEquals($expected, $this->subject->assertAlwaysCalledOn(new EqualToMatcher($this->thisValueA)));
//     }

//     public function testAssertAlwaysCalledOnFailure()
//     {
//         $this->subject->setCalls($this->calls);
//         $expected = <<<'EOD'
// Not always called on expected object. Actual objects:
//     - Eloquent\Phony\Test\TestClass Object ()
//     - Eloquent\Phony\Test\TestClass Object ()
//     - Eloquent\Phony\Test\TestClass Object ()
//     - null
// EOD;

//         $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
//         $this->subject->assertAlwaysCalledOn($this->thisValueA);
//     }

//     public function testAssertAlwaysCalledOnFailureWithNoCalls()
//     {
//         $this->setExpectedException(
//             'Eloquent\Phony\Assertion\Exception\AssertionException',
//             'Not called on expected object. Never called.'
//         );
//         $this->subject->assertAlwaysCalledOn($this->thisValueA);
//     }

//     public function testAssertAlwaysCalledOnFailureWithMatcher()
//     {
//         $this->subject->setCalls($this->calls);
//         $expected = <<<'EOD'
// Not always called on object like <Eloquent\Phony\Test\TestClass Object ()>. Actual objects:
//     - Eloquent\Phony\Test\TestClass Object ()
//     - Eloquent\Phony\Test\TestClass Object ()
//     - Eloquent\Phony\Test\TestClass Object ()
//     - null
// EOD;

//         $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
//         $this->subject->assertAlwaysCalledOn(new EqualToMatcher($this->thisValueA));
//     }

//     public function testAssertAlwaysCalledOnFailureWithMatcherWithNoCalls()
//     {
//         $this->setExpectedException(
//             'Eloquent\Phony\Assertion\Exception\AssertionException',
//             'Not called on object like <Eloquent\Phony\Test\TestClass Object ()>. Never called.'
//         );
//         $this->subject->assertAlwaysCalledOn(new EqualToMatcher($this->thisValueA));
//     }

    public function testCheckReturned()
    {
        $this->assertFalse((boolean) $this->subject->checkReturned());
        $this->assertFalse((boolean) $this->subject->checkReturned(null));
        $this->assertFalse((boolean) $this->subject->checkReturned($this->returnValueA));
        $this->assertFalse((boolean) $this->subject->checkReturned($this->returnValueB));
        $this->assertFalse((boolean) $this->subject->checkReturned(new EqualToMatcher($this->returnValueA)));
        $this->assertFalse((boolean) $this->subject->checkReturned('z'));

        $this->subject->setCalls($this->calls);

        $this->assertTrue((boolean) $this->subject->checkReturned());
        $this->assertFalse((boolean) $this->subject->checkReturned(null));
        $this->assertTrue((boolean) $this->subject->checkReturned($this->returnValueA));
        $this->assertTrue((boolean) $this->subject->checkReturned($this->returnValueB));
        $this->assertTrue((boolean) $this->subject->checkReturned(new EqualToMatcher($this->returnValueA)));
        $this->assertFalse((boolean) $this->subject->checkReturned('z'));
    }

    public function testReturned()
    {
        $this->subject->setCalls($this->calls);

        $this->assertEquals(
            new EventCollection(array($this->callAResponse, $this->callBResponse)),
            $this->subject->returned()
        );
        $this->assertEquals(
            new EventCollection(array($this->callAResponse)),
            $this->subject->returned($this->returnValueA)
        );
        $this->assertEquals(
            new EventCollection(array($this->callBResponse)),
            $this->subject->returned($this->returnValueB)
        );
        $this->assertEquals(
            new EventCollection(array($this->callAResponse)),
            $this->subject->returned(new EqualToMatcher($this->returnValueA))
        );
    }

    public function testReturnedFailure()
    {
        $this->subject->setCalls($this->calls);

        $expected = <<<'EOD'
Expected return like <'z'>. Responded:
    - returned 'x'
    - returned 'y'
    - threw RuntimeException('You done goofed.')
    - threw RuntimeException('Consequences will never be the same.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->returned('z');
    }

    public function testReturnedFailureWithoutMatcher()
    {
        $this->subject->setCalls(array($this->callC, $this->callD));

        $expected = <<<'EOD'
Expected return. Responded:
    - threw RuntimeException('You done goofed.')
    - threw RuntimeException('Consequences will never be the same.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->returned();
    }

    public function testReturnedFailureWithNoCalls()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected return like <'x'>. Never called."
        );
        $this->subject->returned($this->returnValueA);
    }

//     public function testAlwaysReturned()
//     {
//         $this->assertFalse((boolean) $this->subject->alwaysReturned());
//         $this->assertFalse((boolean) $this->subject->alwaysReturned(null));
//         $this->assertFalse((boolean) $this->subject->alwaysReturned($this->returnValueA));
//         $this->assertFalse((boolean) $this->subject->alwaysReturned($this->returnValueB));
//         $this->assertFalse((boolean) $this->subject->alwaysReturned(new EqualToMatcher($this->returnValueA)));
//         $this->assertFalse((boolean) $this->subject->alwaysReturned('z'));

//         $this->subject->setCalls($this->calls);

//         $this->assertFalse((boolean) $this->subject->alwaysReturned());
//         $this->assertFalse((boolean) $this->subject->alwaysReturned(null));
//         $this->assertFalse((boolean) $this->subject->alwaysReturned($this->returnValueA));
//         $this->assertFalse((boolean) $this->subject->alwaysReturned($this->returnValueB));
//         $this->assertFalse((boolean) $this->subject->alwaysReturned(new EqualToMatcher($this->returnValueA)));
//         $this->assertFalse((boolean) $this->subject->alwaysReturned('z'));

//         $this->subject->setCalls(array($this->callA, $this->callA));

//         $this->assertTrue((boolean) $this->subject->alwaysReturned());
//         $this->assertTrue((boolean) $this->subject->alwaysReturned($this->returnValueA));
//         $this->assertTrue((boolean) $this->subject->alwaysReturned(new EqualToMatcher($this->returnValueA)));
//         $this->assertFalse((boolean) $this->subject->alwaysReturned(null));
//         $this->assertFalse((boolean) $this->subject->alwaysReturned($this->returnValueB));
//         $this->assertFalse((boolean) $this->subject->alwaysReturned('y'));
//     }

//     public function testAssertAlwaysReturned()
//     {
//         $this->subject->setCalls(array($this->callA, $this->callA));
//         $expected = new EventCollection(array($this->callAResponse, $this->callAResponse));

//         $this->assertEquals($expected, $this->subject->assertAlwaysReturned());
//         $this->assertEquals($expected, $this->subject->assertAlwaysReturned($this->returnValueA));
//         $this->assertEquals($expected, $this->subject->assertAlwaysReturned(new EqualToMatcher($this->returnValueA)));
//     }

//     public function testAssertAlwaysReturnedFailure()
//     {
//         $this->subject->setCalls($this->calls);

//         $expected = <<<'EOD'
// Expected every call with return value like <'x'>. Actually responded:
//     - returned 'x'
//     - returned 'y'
//     - threw RuntimeException('You done goofed.')
//     - threw RuntimeException('Consequences will never be the same.')
// EOD;

//         $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
//         $this->subject->assertAlwaysReturned($this->returnValueA);
//     }

//     public function testAssertAlwaysReturnedFailureWithNoMatcher()
//     {
//         $this->subject->setCalls($this->calls);

//         $expected = <<<'EOD'
// Expected every call to return. Actually responded:
//     - returned 'x'
//     - returned 'y'
//     - threw RuntimeException('You done goofed.')
//     - threw RuntimeException('Consequences will never be the same.')
// EOD;

//         $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
//         $this->subject->assertAlwaysReturned();
//     }

//     public function testAssertAlwaysReturnedFailureWithNoCalls()
//     {
//         $this->setExpectedException(
//             'Eloquent\Phony\Assertion\Exception\AssertionException',
//             "Expected spy to return. Never called."
//         );
//         $this->subject->assertAlwaysReturned($this->returnValueA);
//     }

    public function testCheckThrew()
    {
        $this->assertFalse((boolean) $this->subject->checkThrew());
        $this->assertFalse((boolean) $this->subject->checkThrew('Exception'));
        $this->assertFalse((boolean) $this->subject->checkThrew('RuntimeException'));
        $this->assertFalse((boolean) $this->subject->checkThrew($this->exceptionA));
        $this->assertFalse((boolean) $this->subject->checkThrew($this->exceptionB));
        $this->assertFalse((boolean) $this->subject->checkThrew(new EqualToMatcher($this->exceptionA)));
        $this->assertFalse((boolean) $this->subject->checkThrew('InvalidArgumentException'));
        $this->assertFalse((boolean) $this->subject->checkThrew(new Exception()));
        $this->assertFalse((boolean) $this->subject->checkThrew(new RuntimeException()));
        $this->assertFalse((boolean) $this->subject->checkThrew(new EqualToMatcher(null)));

        $this->subject->setCalls($this->calls);

        $this->assertTrue((boolean) $this->subject->checkThrew());
        $this->assertTrue((boolean) $this->subject->checkThrew('Exception'));
        $this->assertTrue((boolean) $this->subject->checkThrew('RuntimeException'));
        $this->assertTrue((boolean) $this->subject->checkThrew($this->exceptionA));
        $this->assertTrue((boolean) $this->subject->checkThrew($this->exceptionB));
        $this->assertTrue((boolean) $this->subject->checkThrew(new EqualToMatcher($this->exceptionA)));
        $this->assertFalse((boolean) $this->subject->checkThrew('InvalidArgumentException'));
        $this->assertFalse((boolean) $this->subject->checkThrew(new Exception()));
        $this->assertFalse((boolean) $this->subject->checkThrew(new RuntimeException()));
        $this->assertFalse((boolean) $this->subject->checkThrew(new EqualToMatcher(null)));
    }

    public function testCheckThrewFailureInvalidInput()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            "Unable to match exceptions against 111."
        );
        $this->subject->checkThrew(111);
    }

    public function testCheckThrewFailureInvalidInputObject()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            "Unable to match exceptions against stdClass Object ()."
        );
        $this->subject->checkThrew((object) array());
    }

    public function testThrew()
    {
        $this->subject->setCalls($this->calls);

        $this->assertEquals(
            new EventCollection(array($this->callCResponse, $this->callDResponse)),
            $this->subject->threw()
        );
        $this->assertEquals(
            new EventCollection(array($this->callCResponse, $this->callDResponse)),
            $this->subject->threw('Exception')
        );
        $this->assertEquals(
            new EventCollection(array($this->callCResponse, $this->callDResponse)),
            $this->subject->threw('RuntimeException')
        );
        $this->assertEquals(
            new EventCollection(array($this->callCResponse)),
            $this->subject->threw($this->exceptionA)
        );
        $this->assertEquals(
            new EventCollection(array($this->callDResponse)),
            $this->subject->threw($this->exceptionB)
        );
        $this->assertEquals(
            new EventCollection(array($this->callCResponse)),
            $this->subject->threw(new EqualToMatcher($this->exceptionA))
        );
    }

    public function testThrewFailureExpectingAny()
    {
        $this->subject->setCalls(array($this->callA, $this->callB));
        $expected = <<<'EOD'
Expected exception. Responded:
    - returned 'x'
    - returned 'y'
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->threw();
    }

    public function testThrewFailureExpectingAnyWithNoCalls()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected exception. Never called.'
        );
        $this->subject->threw();
    }

    public function testThrewFailureExpectingType()
    {
        $this->subject->setCalls(array($this->callC, $this->callD));
        $expected = <<<'EOD'
Expected 'Eloquent\Phony\Spy\Exception\UndefinedCallException' exception. Responded:
    - threw RuntimeException('You done goofed.')
    - threw RuntimeException('Consequences will never be the same.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->threw('Eloquent\Phony\Spy\Exception\UndefinedCallException');
    }

    public function testThrewFailureExpectingTypeWithNoCalls()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected 'Eloquent\Phony\Spy\Exception\UndefinedCallException' exception. Never called."
        );
        $this->subject->threw('Eloquent\Phony\Spy\Exception\UndefinedCallException');
    }

    public function testThrewFailureExpectingTypeWithNoExceptions()
    {
        $this->subject->setCalls(array($this->callA, $this->callB));
        $expected = <<<'EOD'
Expected 'Eloquent\Phony\Spy\Exception\UndefinedCallException' exception. Responded:
    - returned 'x'
    - returned 'y'
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->threw('Eloquent\Phony\Spy\Exception\UndefinedCallException');
    }

    public function testThrewFailureExpectingException()
    {
        $this->subject->setCalls(array($this->callC, $this->callD));
        $expected = <<<'EOD'
Expected exception equal to RuntimeException(). Responded:
    - threw RuntimeException('You done goofed.')
    - threw RuntimeException('Consequences will never be the same.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->threw(new RuntimeException());
    }

    public function testThrewFailureExpectingExceptionWithNoCalls()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected exception equal to RuntimeException(). Never called.'
        );
        $this->subject->threw(new RuntimeException());
    }

    public function testThrewFailureExpectingExceptionWithNoExceptions()
    {
        $this->subject->setCalls(array($this->callA, $this->callB));
        $expected = <<<'EOD'
Expected exception equal to RuntimeException(). Responded:
    - returned 'x'
    - returned 'y'
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->threw(new RuntimeException());
    }

    public function testThrewFailureExpectingMatcher()
    {
        $this->subject->setCalls(array($this->callC, $this->callD));
        $expected = <<<'EOD'
Expected exception like <RuntimeException Object (...)>. Responded:
    - threw RuntimeException('You done goofed.')
    - threw RuntimeException('Consequences will never be the same.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->threw(new EqualToMatcher(new RuntimeException()));
    }

    public function testThrewFailureExpectingMatcherWithNoCalls()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected exception like <RuntimeException Object (...)>. Never called.'
        );
        $this->subject->threw(new EqualToMatcher(new RuntimeException()));
    }

    public function testThrewFailureExpectingMatcherWithNoExceptions()
    {
        $this->subject->setCalls(array($this->callA, $this->callB));
        $expected = <<<'EOD'
Expected exception like <RuntimeException Object (...)>. Responded:
    - returned 'x'
    - returned 'y'
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->threw(new EqualToMatcher(new RuntimeException()));
    }

    public function testThrewFailureInvalidInput()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            "Unable to match exceptions against 111."
        );
        $this->subject->threw(111);
    }

    public function testThrewFailureInvalidInputObject()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            "Unable to match exceptions against stdClass Object ()."
        );
        $this->subject->threw((object) array());
    }

//     public function testAlwaysThrew()
//     {
//         $this->assertFalse((boolean) $this->subject->alwaysThrew());
//         $this->assertFalse((boolean) $this->subject->alwaysThrew('Exception'));
//         $this->assertFalse((boolean) $this->subject->alwaysThrew('RuntimeException'));
//         $this->assertFalse((boolean) $this->subject->alwaysThrew($this->exceptionA));
//         $this->assertFalse((boolean) $this->subject->alwaysThrew($this->exceptionB));
//         $this->assertFalse((boolean) $this->subject->alwaysThrew(new EqualToMatcher($this->exceptionA)));
//         $this->assertFalse((boolean) $this->subject->alwaysThrew('InvalidArgumentException'));
//         $this->assertFalse((boolean) $this->subject->alwaysThrew(new Exception()));
//         $this->assertFalse((boolean) $this->subject->alwaysThrew(new RuntimeException()));
//         $this->assertFalse((boolean) $this->subject->alwaysThrew(new EqualToMatcher(null)));
//         $this->assertFalse((boolean) $this->subject->alwaysThrew(111));

//         $this->subject->setCalls($this->calls);

//         $this->assertFalse((boolean) $this->subject->alwaysThrew());
//         $this->assertFalse((boolean) $this->subject->alwaysThrew('Exception'));
//         $this->assertFalse((boolean) $this->subject->alwaysThrew('RuntimeException'));
//         $this->assertFalse((boolean) $this->subject->alwaysThrew($this->exceptionA));
//         $this->assertFalse((boolean) $this->subject->alwaysThrew($this->exceptionB));
//         $this->assertFalse((boolean) $this->subject->alwaysThrew(new EqualToMatcher($this->exceptionA)));
//         $this->assertFalse((boolean) $this->subject->alwaysThrew('InvalidArgumentException'));
//         $this->assertFalse((boolean) $this->subject->alwaysThrew(new Exception()));
//         $this->assertFalse((boolean) $this->subject->alwaysThrew(new RuntimeException()));
//         $this->assertFalse((boolean) $this->subject->alwaysThrew(new EqualToMatcher(null)));
//         $this->assertFalse((boolean) $this->subject->alwaysThrew(111));

//         $this->subject->setCalls(array($this->callC, $this->callC));

//         $this->assertTrue((boolean) $this->subject->alwaysThrew());
//         $this->assertTrue((boolean) $this->subject->alwaysThrew('Exception'));
//         $this->assertTrue((boolean) $this->subject->alwaysThrew('RuntimeException'));
//         $this->assertTrue((boolean) $this->subject->alwaysThrew($this->exceptionA));
//         $this->assertFalse((boolean) $this->subject->alwaysThrew($this->exceptionB));
//         $this->assertTrue((boolean) $this->subject->alwaysThrew(new EqualToMatcher($this->exceptionA)));
//         $this->assertFalse((boolean) $this->subject->alwaysThrew('InvalidArgumentException'));
//         $this->assertFalse((boolean) $this->subject->alwaysThrew(new Exception()));
//         $this->assertFalse((boolean) $this->subject->alwaysThrew(new RuntimeException()));
//         $this->assertFalse((boolean) $this->subject->alwaysThrew(new EqualToMatcher(null)));
//         $this->assertFalse((boolean) $this->subject->alwaysThrew(111));

//         $this->subject->setCalls(array($this->callA, $this->callA));

//         $this->assertFalse((boolean) $this->subject->alwaysThrew(new EqualToMatcher(null)));
//     }

//     public function testAssertAlwaysThrew()
//     {
//         $this->subject->setCalls(array($this->callC, $this->callC));
//         $expected = new EventCollection(array($this->callCResponse, $this->callCResponse));

//         $this->assertEquals($expected, $this->subject->assertAlwaysThrew());
//         $this->assertEquals($expected, $this->subject->assertAlwaysThrew('Exception'));
//         $this->assertEquals($expected, $this->subject->assertAlwaysThrew('RuntimeException'));
//         $this->assertEquals($expected, $this->subject->assertAlwaysThrew($this->exceptionA));
//         $this->assertEquals($expected, $this->subject->assertAlwaysThrew(new EqualToMatcher($this->exceptionA)));
//     }

//     public function testAssertAlwaysThrewFailureExpectingAny()
//     {
//         $this->subject->setCalls($this->calls);
//         $expected = <<<'EOD'
// Expected every call to throw. Actually responded:
//     - returned 'x'
//     - returned 'y'
//     - threw RuntimeException('You done goofed.')
//     - threw RuntimeException('Consequences will never be the same.')
// EOD;

//         $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
//         $this->subject->assertAlwaysThrew();
//     }

//     public function testAssertAlwaysThrewFailureExpectingAnyButNothingThrown()
//     {
//         $this->subject->setCalls(array($this->callA, $this->callB));

//         $this->setExpectedException(
//             'Eloquent\Phony\Assertion\Exception\AssertionException',
//             'Nothing thrown in 2 call(s).'
//         );
//         $this->subject->assertAlwaysThrew();
//     }

//     public function testAssertAlwaysThrewFailureExpectingAnyWithNoCalls()
//     {
//         $this->setExpectedException(
//             'Eloquent\Phony\Assertion\Exception\AssertionException',
//             'Nothing thrown. Never called.'
//         );
//         $this->subject->assertAlwaysThrew();
//     }

//     public function testAssertAlwaysThrewFailureExpectingType()
//     {
//         $this->subject->setCalls(array($this->callC, $this->callD));
//         $expected = <<<'EOD'
// Expected every call to throw 'Eloquent\Phony\Spy\Exception\UndefinedCallException' exception. Actually responded:
//     - threw RuntimeException('You done goofed.')
//     - threw RuntimeException('Consequences will never be the same.')
// EOD;

//         $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
//         $this->subject->assertAlwaysThrew('Eloquent\Phony\Spy\Exception\UndefinedCallException');
//     }

//     public function testAssertAlwaysThrewFailureExpectingTypeWithNoCalls()
//     {
//         $this->setExpectedException(
//             'Eloquent\Phony\Assertion\Exception\AssertionException',
//             "Expected 'Eloquent\Phony\Spy\Exception\UndefinedCallException' exception. Never called."
//         );
//         $this->subject->assertAlwaysThrew('Eloquent\Phony\Spy\Exception\UndefinedCallException');
//     }

//     public function testAssertAlwaysThrewFailureExpectingTypeWithNoExceptions()
//     {
//         $this->subject->setCalls(array($this->callA, $this->callB));

//         $this->setExpectedException(
//             'Eloquent\Phony\Assertion\Exception\AssertionException',
//             "Expected 'Eloquent\Phony\Spy\Exception\UndefinedCallException' exception. Nothing thrown in 2 call(s)."
//         );
//         $this->subject->assertAlwaysThrew('Eloquent\Phony\Spy\Exception\UndefinedCallException');
//     }

//     public function testAssertAlwaysThrewFailureExpectingException()
//     {
//         $this->subject->setCalls(array($this->callC, $this->callD));
//         $expected = <<<'EOD'
// Expected every call to throw exception equal to RuntimeException(). Actually responded:
//     - threw RuntimeException('You done goofed.')
//     - threw RuntimeException('Consequences will never be the same.')
// EOD;

//         $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
//         $this->subject->assertAlwaysThrew(new RuntimeException());
//     }

//     public function testAssertAlwaysThrewFailureExpectingExceptionWithNoCalls()
//     {
//         $this->setExpectedException(
//             'Eloquent\Phony\Assertion\Exception\AssertionException',
//             'Expected exception equal to RuntimeException(). Never called.'
//         );
//         $this->subject->assertAlwaysThrew(new RuntimeException());
//     }

//     public function testAssertAlwaysThrewFailureExpectingExceptionWithNoExceptions()
//     {
//         $this->subject->setCalls(array($this->callA, $this->callB));

//         $this->setExpectedException(
//             'Eloquent\Phony\Assertion\Exception\AssertionException',
//             "Expected exception equal to RuntimeException(). Nothing thrown in 2 call(s)."
//         );
//         $this->subject->assertAlwaysThrew(new RuntimeException());
//     }

//     public function testAssertAlwaysThrewFailureExpectingMatcher()
//     {
//         $this->subject->setCalls(array($this->callC, $this->callD));
//         $expected = <<<'EOD'
// Expected every call to throw exception like <RuntimeException Object (...)>. Actually responded:
//     - threw RuntimeException('You done goofed.')
//     - threw RuntimeException('Consequences will never be the same.')
// EOD;

//         $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
//         $this->subject->assertAlwaysThrew(new EqualToMatcher(new RuntimeException()));
//     }

//     public function testAssertAlwaysThrewFailureExpectingMatcherWithNoCalls()
//     {
//         $this->setExpectedException(
//             'Eloquent\Phony\Assertion\Exception\AssertionException',
//             'Expected exception like <RuntimeException Object (...)>. Never called.'
//         );
//         $this->subject->assertAlwaysThrew(new EqualToMatcher(new RuntimeException()));
//     }

//     public function testAssertAlwaysThrewFailureExpectingMatcherWithNoExceptions()
//     {
//         $this->subject->setCalls(array($this->callA, $this->callB));

//         $this->setExpectedException(
//             'Eloquent\Phony\Assertion\Exception\AssertionException',
//             'Expected exception like <RuntimeException Object (...)>. ' .
//                 'Nothing thrown in 2 call(s).'
//         );
//         $this->subject->assertAlwaysThrew(new EqualToMatcher(new RuntimeException()));
//     }

//     public function testAssertAlwaysThrewFailureInvalidInput()
//     {
//         $this->setExpectedException(
//             'Eloquent\Phony\Assertion\Exception\AssertionException',
//             "Unable to match exceptions against stdClass Object ()."
//         );
//         $this->subject->assertAlwaysThrew((object) array());
//     }

    public function testCardinalityMethods()
    {
        $this->subject->never();

        $this->assertEquals(new Cardinality(0, 0), $this->subject->never()->cardinality());
        $this->assertEquals(new Cardinality(1, 1), $this->subject->once()->cardinality());
        $this->assertEquals(new Cardinality(2, 2), $this->subject->times(2)->cardinality());
        $this->assertEquals(new Cardinality(3), $this->subject->atLeast(3)->cardinality());
        $this->assertEquals(new Cardinality(null, 4), $this->subject->atMost(4)->cardinality());
        $this->assertEquals(new Cardinality(5, 6), $this->subject->between(5, 6)->cardinality());
        $this->assertEquals(new Cardinality(5, 6, true), $this->subject->between(5, 6)->always()->cardinality());
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
}
