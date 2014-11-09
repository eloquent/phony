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
use Eloquent\Phony\Call\Event\CallEventCollection;
use Eloquent\Phony\Call\Factory\CallVerifierFactory;
use Eloquent\Phony\Cardinality\Cardinality;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Matcher\EqualToMatcher;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Test\TestCallFactory;
use Eloquent\Phony\Test\TestClassA;
use Exception;
use PHPUnit_Framework_TestCase;
use RuntimeException;

class SpyVerifierTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->callback = 'implode';
        $this->callFactory = new TestCallFactory();
        $this->label = 'label';
        $this->spy = new Spy($this->callback, false, false, $this->label, $this->callFactory);

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
        $this->thisValueA = new TestClassA();
        $this->thisValueB = new TestClassA();
        $this->arguments = array('a', 'b', 'c');
        $this->matchers = $this->matcherFactory->adaptAll($this->arguments);
        $this->otherMatcher = $this->matcherFactory->adapt('d');
        $this->callA = $this->callFactory->create(
            $this->callEventFactory->createCalled(array($this->thisValueA, 'testClassAMethodA'), $this->arguments),
            $this->callEventFactory->createReturned($this->returnValueA)
        );
        $this->callAResponse = $this->callA->responseEvent();
        $this->callB = $this->callFactory->create(
            $this->callEventFactory->createCalled(array($this->thisValueB, 'testClassAMethodA')),
            $this->callEventFactory->createReturned($this->returnValueB)
        );
        $this->callBResponse = $this->callB->responseEvent();
        $this->callC = $this->callFactory->create(
            $this->callEventFactory->createCalled(array($this->thisValueA, 'testClassAMethodA'), $this->arguments),
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
        $this->assertFalse($this->subject->isAnonymous());
        $this->assertSame($this->callback, $this->subject->callback());
        $this->assertSame($this->label, $this->subject->label());
    }

    public function testSetLabel()
    {
        $this->subject->setLabel(null);

        $this->assertNull($this->subject->label());

        $this->subject->setLabel($this->label);

        $this->assertSame($this->label, $this->subject->label());
    }

    public function testSetUseTraversableSpies()
    {
        $this->subject->setUseTraversableSpies(true);

        $this->assertTrue($this->subject->useTraversableSpies());
    }

    public function testSetUseGeneratorSpies()
    {
        $this->subject->setUseGeneratorSpies(true);

        $this->assertTrue($this->subject->useGeneratorSpies());
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
                $this->callEventFactory->createCalled($spy, array(array('a'))),
                $this->callEventFactory->createReturned('a')
            ),
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy, array(array('b', 'c'))),
                $this->callEventFactory->createReturned('bc')
            ),
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy, array(array('d'))),
                $this->callEventFactory->createReturned('d')
            ),
        );

        $this->assertEquals($expected, $this->spy->recordedCalls());
    }

    public function testInvokeMethodsWithoutSubject()
    {
        $spy = new Spy(null, false, false, 111, $this->callFactory);
        $verifier = new SpyVerifier($spy);
        $verifier->invokeWith(array('a'));
        $verifier->invoke('b', 'c');
        $verifier('d');
        $this->callFactory->reset();
        $expected = array(
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy, array('a')),
                $this->callEventFactory->createReturned()
            ),
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy, array('b', 'c')),
                $this->callEventFactory->createReturned()
            ),
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy, array('d')),
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
        $spy = new Spy($callback, false, false, 111, $this->callFactory);
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
                $this->callEventFactory->createCalled($spy, array('a')),
                $this->callEventFactory->createThrew($exceptions[0])
            ),
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy, array('b', 'c')),
                $this->callEventFactory->createThrew($exceptions[1])
            ),
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy, array('d')),
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
        $spy = new Spy($callback, false, false, 111, $this->callFactory);
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
        $expected = new CallEventCollection($this->calls);

        $this->assertEquals($expected, $this->subject->called());
    }

    public function testCalledFailure()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', 'Never called.');
        $this->subject->called();
    }

    public function testCheckCalledOnce()
    {
        $this->assertFalse((boolean) $this->subject->once()->checkCalled());

        $this->subject->addCall($this->callA);

        $this->assertTrue((boolean) $this->subject->once()->checkCalled());

        $this->subject->addCall($this->callB);

        $this->assertFalse((boolean) $this->subject->once()->checkCalled());
    }

    public function testCalledOnce()
    {
        $this->subject->addCall($this->callA);
        $expected = new CallEventCollection(array($this->callA));

        $this->assertEquals($expected, $this->subject->once()->called());
    }

    public function testCalledOnceFailureWithNoCalls()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected call, exactly 1 time. Never called.'
        );
        $this->subject->once()->called();
    }

    public function testCalledOnceFailureWithMultipleCalls()
    {
        $this->subject->setCalls($this->calls);
        $expected = <<<'EOD'
Expected call, exactly 1 time. Calls:
    - Eloquent\Phony\Test\TestClassA->testClassAMethodA('a', 'b', 'c')
    - Eloquent\Phony\Test\TestClassA->testClassAMethodA()
    - Eloquent\Phony\Test\TestClassA->testClassAMethodA('a', 'b', 'c')
    - implode()
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->once()->called();
    }

    public function testCheckCalledTimes()
    {
        $this->assertTrue((boolean) $this->subject->times(0)->checkCalled());
        $this->assertFalse((boolean) $this->subject->times(4)->checkCalled());

        $this->subject->setCalls($this->calls);

        $this->assertFalse((boolean) $this->subject->times(0)->checkCalled());
        $this->assertTrue((boolean) $this->subject->times(4)->checkCalled());
    }

    public function testCalledTimes()
    {
        $this->subject->setCalls($this->calls);
        $expected = new CallEventCollection($this->calls);

        $this->assertEquals($expected, $this->subject->times(4)->called());
    }

    public function testCalledTimesFailure()
    {
        $this->subject->setCalls($this->calls);
        $expected = <<<'EOD'
Expected call, exactly 2 times. Calls:
    - Eloquent\Phony\Test\TestClassA->testClassAMethodA('a', 'b', 'c')
    - Eloquent\Phony\Test\TestClassA->testClassAMethodA()
    - Eloquent\Phony\Test\TestClassA->testClassAMethodA('a', 'b', 'c')
    - implode()
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->times(2)->called();
    }

    public function calledWithData()
    {
        //                                    arguments                  calledWith calledWithWildcard
        return array(
            'Exact arguments'        => array(array('a', 'b', 'c'),      true,      true),
            'First arguments'        => array(array('a', 'b'),           false,      true),
            'Single argument'        => array(array('a'),                false,      true),
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
    public function testCheckCalledWith(array $arguments, $calledWith, $calledWithWildcard)
    {
        $this->subject->setCalls($this->calls);
        $matchers = $this->matcherFactory->adaptAll($arguments);

        $this->assertSame(
            $calledWith,
            (boolean) call_user_func_array(array($this->subject, 'checkCalledWith'), $arguments)
        );
        $this->assertSame(
            $calledWith,
            (boolean) call_user_func_array(array($this->subject, 'checkCalledWith'), $matchers)
        );

        $arguments[] = $this->matcherFactory->wildcard();
        $matchers[] = $this->matcherFactory->wildcard();

        $this->assertSame(
            $calledWithWildcard,
            (boolean) call_user_func_array(array($this->subject, 'checkCalledWith'), $arguments)
        );
        $this->assertSame(
            $calledWithWildcard,
            (boolean) call_user_func_array(array($this->subject, 'checkCalledWith'), $matchers)
        );
    }

    public function testCheckCalledWithWithWildcardOnly()
    {
        $this->subject->setCalls($this->calls);

        $this->assertTrue((boolean) $this->subject->checkCalledWith($this->matcherFactory->wildcard()));
    }

    public function testCheckCalledWithWithWildcardOnlyWithNoCalls()
    {
        $this->assertFalse((boolean) $this->subject->checkCalledWith($this->matcherFactory->wildcard()));
    }

    public function testCalledWith()
    {
        $this->subject->setCalls($this->calls);
        $expected = new CallEventCollection(array($this->callA, $this->callC));

        $this->assertEquals($expected, $this->subject->calledWith('a', 'b', 'c'));
        $this->assertEquals(
            $expected,
            $this->subject->calledWith($this->matchers[0], $this->matchers[1], $this->matchers[2])
        );
        $this->assertEquals($expected, $this->subject->calledWith('a', 'b', $this->matcherFactory->wildcard()));
        $this->assertEquals(
            $expected,
            $this->subject->calledWith($this->matchers[0], $this->matchers[1], $this->matcherFactory->wildcard())
        );
        $this->assertEquals($expected, $this->subject->calledWith('a', $this->matcherFactory->wildcard()));
        $this->assertEquals(
            $expected,
            $this->subject->calledWith($this->matchers[0], $this->matcherFactory->wildcard())
        );
        $this->assertEquals(
            new CallEventCollection($this->calls),
            $this->subject->calledWith($this->matcherFactory->wildcard())
        );
    }

    public function testCalledWithFailure()
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
        $this->subject->calledWith('b', 'c');
    }

    public function testCalledWithFailureWithNoCalls()
    {
        $expected = <<<'EOD'
Expected call with arguments like:
    <'b'>, <'c'>
Never called.
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->calledWith('b', 'c');
    }

    public function testCheckCalledOnceWith()
    {
        $this->assertFalse((boolean) $this->subject->once()->checkCalledWith());

        $this->subject->setCalls(array($this->callA, $this->callB));

        $this->assertTrue((boolean) $this->subject->once()->checkCalledWith('a', 'b', 'c'));
        $this->assertTrue(
            (boolean) $this->subject->once()
                ->checkCalledWith($this->matchers[0], $this->matchers[1], $this->matchers[2])
        );
        $this->assertFalse((boolean) $this->subject->once()->checkCalledWith($this->matcherFactory->wildcard()));
        $this->assertFalse((boolean) $this->subject->once()->checkCalledWith($this->matcherFactory->wildcard()));
    }

    public function testCalledOnceWith()
    {
        $this->subject->setCalls(array($this->callA, $this->callB));
        $expected = new CallEventCollection(array($this->callA));

        $this->assertEquals($expected, $this->subject->once()->calledWith('a', 'b', 'c'));
        $this->assertEquals(
            $expected,
            $this->subject->once()->calledWith($this->matchers[0], $this->matchers[1], $this->matchers[2])
        );
    }

    public function testCalledOnceWithFailure()
    {
        $this->subject->setCalls($this->calls);
        $expected = <<<'EOD'
Expected call, exactly 1 time with arguments like:
    <'a'>, <'b'>, <'c'>
Calls:
    - 'a', 'b', 'c'
    - <none>
    - 'a', 'b', 'c'
    - <none>
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->once()->calledWith('a', 'b', 'c');
    }

    public function testCalledOnceWithFailureWithNoCalls()
    {
        $expected = <<<'EOD'
Expected call, exactly 1 time with arguments like:
    <'a'>, <'b'>, <'c'>
Never called.
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->once()->calledWith('a', 'b', 'c');
    }

    public function testCheckCalledTimesWith()
    {
        $this->subject->setCalls($this->calls);

        $this->assertTrue((boolean) $this->subject->times(2)->checkCalledWith('a', 'b', 'c'));
        $this->assertTrue(
            (boolean) $this->subject->times(2)
                ->checkCalledWith($this->matchers[0], $this->matchers[1], $this->matchers[2])
        );
        $this->assertTrue((boolean) $this->subject->times(2)->checkCalledWith('a', $this->matcherFactory->wildcard()));
        $this->assertTrue(
            (boolean) $this->subject->times(2)->checkCalledWith($this->matchers[0], $this->matcherFactory->wildcard())
        );
        $this->assertTrue((boolean) $this->subject->times(4)->checkCalledWith($this->matcherFactory->wildcard()));
        $this->assertFalse((boolean) $this->subject->times(1)->checkCalledWith('a', 'b', 'c'));
        $this->assertFalse(
            (boolean) $this->subject->times(1)
                ->checkCalledWith($this->matchers[0], $this->matchers[1], $this->matchers[2])
        );
        $this->assertFalse((boolean) $this->subject->times(1)->checkCalledWith('a'));
        $this->assertFalse((boolean) $this->subject->times(1)->checkCalledWith($this->matchers[0]));
        $this->assertFalse((boolean) $this->subject->times(1)->checkCalledWith($this->matcherFactory->wildcard()));
        $this->assertFalse((boolean) $this->subject->times(1)->checkCalledWith($this->matcherFactory->wildcard()));
    }

    public function testCalledTimesWith()
    {
        $this->subject->setCalls($this->calls);
        $expected = new CallEventCollection(array($this->callA, $this->callC));

        $this->assertEquals($expected, $this->subject->times(2)->calledWith('a', 'b', 'c'));
        $this->assertEquals(
            $expected,
            $this->subject->times(2)->calledWith($this->matchers[0], $this->matchers[1], $this->matchers[2])
        );
        $this->assertEquals($expected, $this->subject->times(2)->calledWith('a', $this->matcherFactory->wildcard()));
        $this->assertEquals(
            $expected,
            $this->subject->times(2)->calledWith($this->matchers[0], $this->matcherFactory->wildcard())
        );

        $expected = new CallEventCollection($this->calls);

        $this->assertEquals($expected, $this->subject->times(4)->calledWith($this->matcherFactory->wildcard()));
        $this->assertEquals($expected, $this->subject->times(4)->calledWith($this->matcherFactory->wildcard()));
    }

    public function testCalledTimesWithFailure()
    {
        $this->subject->setCalls($this->calls);
        $expected = <<<'EOD'
Expected call, exactly 4 times with arguments like:
    <'a'>, <'b'>, <'c'>
Calls:
    - 'a', 'b', 'c'
    - <none>
    - 'a', 'b', 'c'
    - <none>
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->times(4)->calledWith('a', 'b', 'c');
    }

    public function testCalledTimesWithFailureWithNoCalls()
    {
        $expected = <<<'EOD'
Expected call, exactly 4 times with arguments like:
    <'a'>, <'b'>, <'c'>
Never called.
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->times(4)->calledWith('a', 'b', 'c');
    }

    /**
     * @dataProvider calledWithData
     */
    public function testCheckAlwaysCalledWith(array $arguments, $calledWith, $calledWithWildcard)
    {
        $this->subject->setCalls(array($this->callA, $this->callA));
        $matchers = $this->matcherFactory->adaptAll($arguments);

        $this->assertSame(
            $calledWith,
            (boolean) call_user_func_array(array($this->subject->always(), 'checkCalledWith'), $arguments)
        );
        $this->assertSame(
            $calledWith,
            (boolean) call_user_func_array(array($this->subject->always(), 'checkCalledWith'), $matchers)
        );

        $arguments[] = $this->matcherFactory->wildcard();
        $matchers[] = $this->matcherFactory->wildcard();

        $this->assertSame(
            $calledWithWildcard,
            (boolean) call_user_func_array(array($this->subject->always(), 'checkCalledWith'), $arguments)
        );
        $this->assertSame(
            $calledWithWildcard,
            (boolean) call_user_func_array(array($this->subject->always(), 'checkCalledWith'), $matchers)
        );
    }

    /**
     * @dataProvider calledWithData
     */
    public function testCheckAlwaysCalledWithWithDifferingCalls(array $arguments, $calledWith, $calledWithWildcard)
    {
        $this->subject->setCalls(array($this->callA, $this->callB));
        $matchers = $this->matcherFactory->adaptAll($arguments);
        $arguments[] = $this->matcherFactory->wildcard();
        $matchers[] = $this->matcherFactory->wildcard();

        $this->assertFalse(
            (boolean) call_user_func_array(array($this->subject->always(), 'checkCalledWith'), $arguments)
        );
        $this->assertFalse(
            (boolean) call_user_func_array(array($this->subject->always(), 'checkCalledWith'), $matchers)
        );
    }

    public function testCheckAlwaysCalledWithWithWildcardOnly()
    {
        $this->subject->setCalls($this->calls);

        $this->assertTrue((boolean) $this->subject->always()->checkCalledWith($this->matcherFactory->wildcard()));
    }

    public function testCheckAlwaysCalledWithWithWildcardOnlyWithNoCalls()
    {
        $this->assertFalse((boolean) $this->subject->always()->checkCalledWith($this->matcherFactory->wildcard()));
    }

    public function testAlwaysCalledWith()
    {
        $this->subject->setCalls(array($this->callA, $this->callA));
        $expected = new CallEventCollection(array($this->callA, $this->callA));

        $this->assertEquals($expected, $this->subject->always()->calledWith('a', 'b', 'c'));
        $this->assertEquals(
            $expected,
            $this->subject->always()->calledWith($this->matchers[0], $this->matchers[1], $this->matchers[2])
        );
        $this->assertEquals(
            $expected,
            $this->subject->always()->calledWith('a', 'b', $this->matcherFactory->wildcard())
        );
        $this->assertEquals(
            $expected,
            $this->subject->always()
                ->calledWith($this->matchers[0], $this->matchers[1], $this->matcherFactory->wildcard())
        );
        $this->assertEquals($expected, $this->subject->always()->calledWith('a', $this->matcherFactory->wildcard()));
        $this->assertEquals(
            $expected,
            $this->subject->always()->calledWith($this->matchers[0], $this->matcherFactory->wildcard())
        );
        $this->assertEquals($expected, $this->subject->always()->calledWith($this->matcherFactory->wildcard()));
    }

    public function testAlwaysCalledWithFailure()
    {
        $this->subject->setCalls($this->calls);

        $expected = <<<'EOD'
Expected every call with arguments like:
    <'a'>, <'b'>, <'c'>
Calls:
    - 'a', 'b', 'c'
    - <none>
    - 'a', 'b', 'c'
    - <none>
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->always()->calledWith('a', 'b', 'c');
    }

    public function testAlwaysCalledWithFailureWithNoCalls()
    {
        $expected = <<<'EOD'
Expected every call with arguments like:
    <'a'>, <'b'>, <'c'>
Never called.
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->always()->calledWith('a', 'b', 'c');
    }

    /**
     * @dataProvider calledWithData
     */
    public function testCheckNeverCalledWith(array $arguments, $calledWith, $calledWithWildcard)
    {
        $this->subject->setCalls($this->calls);
        $matchers = $this->matcherFactory->adaptAll($arguments);

        $this->assertSame(
            !$calledWith,
            (boolean) call_user_func_array(array($this->subject->never(), 'checkCalledWith'), $arguments)
        );
        $this->assertSame(
            !$calledWith,
            (boolean) call_user_func_array(array($this->subject->never(), 'checkCalledWith'), $matchers)
        );
    }

    public function testCheckNeverCalledWithWithEmptyArguments()
    {
        $this->subject->setCalls($this->calls);

        $this->assertFalse((boolean) $this->subject->never()->checkCalledWith());
    }

    public function testCheckNeverCalledWithWithNoCalls()
    {
        $this->assertTrue((boolean) $this->subject->never()->checkCalledWith());
    }

    public function testNeverCalledWith()
    {
        $expected = new CallEventCollection();

        $this->assertEquals($expected, $this->subject->never()->calledWith());

        $this->subject->setCalls($this->calls);

        $this->assertEquals($expected, $this->subject->never()->calledWith('b', 'c'));
        $this->assertEquals($expected, $this->subject->never()->calledWith($this->matchers[1], $this->matchers[2]));
        $this->assertEquals($expected, $this->subject->never()->calledWith('c'));
        $this->assertEquals($expected, $this->subject->never()->calledWith($this->matchers[2]));
        $this->assertEquals($expected, $this->subject->never()->calledWith('a', 'b', 'c', 'd'));
        $this->assertEquals(
            $expected,
            $this->subject->never()
                ->calledWith($this->matchers[0], $this->matchers[1], $this->matchers[2], $this->otherMatcher)
        );
        $this->assertEquals($expected, $this->subject->never()->calledWith('d', 'b', 'c'));
        $this->assertEquals(
            $expected,
            $this->subject->never()->calledWith($this->otherMatcher, $this->matchers[1], $this->matchers[2])
        );
        $this->assertEquals($expected, $this->subject->never()->calledWith('a', 'b', 'd'));
        $this->assertEquals(
            $expected,
            $this->subject->never()->calledWith($this->matchers[0], $this->matchers[1], $this->otherMatcher)
        );
        $this->assertEquals($expected, $this->subject->never()->calledWith('d'));
        $this->assertEquals($expected, $this->subject->never()->calledWith($this->otherMatcher));
    }

    public function testNeverCalledWithFailure()
    {
        $this->subject->setCalls($this->calls);

        $expected = <<<'EOD'
Expected no call with arguments like:
    <'a'>, <'b'>, <'c'>
Calls:
    - 'a', 'b', 'c'
    - <none>
    - 'a', 'b', 'c'
    - <none>
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->never()->calledWith('a', 'b', 'c');
    }

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

        $this->assertEquals(new CallEventCollection(array($this->callD)), $this->subject->calledOn(null));
        $this->assertEquals(
            new CallEventCollection(array($this->callA, $this->callC)),
            $this->subject->calledOn($this->thisValueA)
        );
        $this->assertEquals(new CallEventCollection(array($this->callB)), $this->subject->calledOn($this->thisValueB));
        $this->assertEquals(
            new CallEventCollection(array($this->callA, $this->callB, $this->callC)),
            $this->subject->calledOn(new EqualToMatcher($this->thisValueA))
        );
    }

    public function testCalledOnFailure()
    {
        $this->subject->setCalls($this->calls);
        $expected = <<<'EOD'
Expected call on supplied object. Called on:
    - Eloquent\Phony\Test\TestClassA Object (...)
    - Eloquent\Phony\Test\TestClassA Object (...)
    - Eloquent\Phony\Test\TestClassA Object (...)
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
    - Eloquent\Phony\Test\TestClassA Object (...)
    - Eloquent\Phony\Test\TestClassA Object (...)
    - Eloquent\Phony\Test\TestClassA Object (...)
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

    public function testCheckAlwaysCalledOn()
    {
        $this->assertFalse((boolean) $this->subject->always()->checkCalledOn(null));
        $this->assertFalse((boolean) $this->subject->always()->checkCalledOn($this->thisValueA));
        $this->assertFalse((boolean) $this->subject->always()->checkCalledOn($this->thisValueB));
        $this->assertFalse((boolean) $this->subject->always()->checkCalledOn(new EqualToMatcher($this->thisValueA)));
        $this->assertFalse((boolean) $this->subject->always()->checkCalledOn((object) array()));

        $this->subject->setCalls($this->calls);

        $this->assertFalse((boolean) $this->subject->always()->checkCalledOn(null));
        $this->assertFalse((boolean) $this->subject->always()->checkCalledOn($this->thisValueA));
        $this->assertFalse((boolean) $this->subject->always()->checkCalledOn($this->thisValueB));
        $this->assertFalse((boolean) $this->subject->always()->checkCalledOn(new EqualToMatcher($this->thisValueA)));
        $this->assertFalse((boolean) $this->subject->always()->checkCalledOn((object) array()));

        $this->subject->setCalls(array($this->callC, $this->callC));

        $this->assertTrue((boolean) $this->subject->always()->checkCalledOn($this->thisValueA));
        $this->assertTrue((boolean) $this->subject->always()->checkCalledOn(new EqualToMatcher($this->thisValueA)));
        $this->assertFalse((boolean) $this->subject->always()->checkCalledOn(null));
        $this->assertFalse((boolean) $this->subject->always()->checkCalledOn($this->thisValueB));
        $this->assertFalse((boolean) $this->subject->always()->checkCalledOn((object) array()));
    }

    public function testAlwaysCalledOn()
    {
        $this->subject->setCalls(array($this->callC, $this->callC));
        $expected = new CallEventCollection(array($this->callC, $this->callC));

        $this->assertEquals($expected, $this->subject->always()->calledOn($this->thisValueA));
        $this->assertEquals($expected, $this->subject->always()->calledOn(new EqualToMatcher($this->thisValueA)));
    }

    public function testAlwaysCalledOnFailure()
    {
        $this->subject->setCalls($this->calls);
        $expected = <<<'EOD'
Expected every call on supplied object. Called on:
    - Eloquent\Phony\Test\TestClassA Object (...)
    - Eloquent\Phony\Test\TestClassA Object (...)
    - Eloquent\Phony\Test\TestClassA Object (...)
    - null
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->always()->calledOn($this->thisValueA);
    }

    public function testAlwaysCalledOnFailureWithNoCalls()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected every call on supplied object. Never called.'
        );
        $this->subject->always()->calledOn($this->thisValueA);
    }

    public function testAlwaysCalledOnFailureWithMatcher()
    {
        $this->subject->setCalls($this->calls);
        $expected = <<<'EOD'
Expected every call on object like <Eloquent\Phony\Test\TestClassA Object (...)>. Called on:
    - Eloquent\Phony\Test\TestClassA Object (...)
    - Eloquent\Phony\Test\TestClassA Object (...)
    - Eloquent\Phony\Test\TestClassA Object (...)
    - null
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->always()->calledOn(new EqualToMatcher($this->thisValueA));
    }

    public function testAlwaysCalledOnFailureWithMatcherWithNoCalls()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected every call on object like <Eloquent\Phony\Test\TestClassA Object (...)>. Never called.'
        );
        $this->subject->always()->calledOn(new EqualToMatcher($this->thisValueA));
    }

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
            new CallEventCollection(array($this->callAResponse, $this->callBResponse)),
            $this->subject->returned()
        );
        $this->assertEquals(
            new CallEventCollection(array($this->callAResponse)),
            $this->subject->returned($this->returnValueA)
        );
        $this->assertEquals(
            new CallEventCollection(array($this->callBResponse)),
            $this->subject->returned($this->returnValueB)
        );
        $this->assertEquals(
            new CallEventCollection(array($this->callAResponse)),
            $this->subject->returned(new EqualToMatcher($this->returnValueA))
        );
    }

    public function testReturnedFailure()
    {
        $this->subject->setCalls($this->calls);

        $expected = <<<'EOD'
Expected call to return like <'z'>. Responded:
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
Expected call to return. Responded:
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
            "Expected call to return like <'x'>. Never called."
        );
        $this->subject->returned($this->returnValueA);
    }

    public function testCheckAlwaysReturned()
    {
        $this->assertFalse((boolean) $this->subject->always()->checkReturned());
        $this->assertFalse((boolean) $this->subject->always()->checkReturned(null));
        $this->assertFalse((boolean) $this->subject->always()->checkReturned($this->returnValueA));
        $this->assertFalse((boolean) $this->subject->always()->checkReturned($this->returnValueB));
        $this->assertFalse((boolean) $this->subject->always()->checkReturned(new EqualToMatcher($this->returnValueA)));
        $this->assertFalse((boolean) $this->subject->always()->checkReturned('z'));

        $this->subject->setCalls($this->calls);

        $this->assertFalse((boolean) $this->subject->always()->checkReturned());
        $this->assertFalse((boolean) $this->subject->always()->checkReturned(null));
        $this->assertFalse((boolean) $this->subject->always()->checkReturned($this->returnValueA));
        $this->assertFalse((boolean) $this->subject->always()->checkReturned($this->returnValueB));
        $this->assertFalse((boolean) $this->subject->always()->checkReturned(new EqualToMatcher($this->returnValueA)));
        $this->assertFalse((boolean) $this->subject->always()->checkReturned('z'));

        $this->subject->setCalls(array($this->callA, $this->callA));

        $this->assertTrue((boolean) $this->subject->always()->checkReturned());
        $this->assertTrue((boolean) $this->subject->always()->checkReturned($this->returnValueA));
        $this->assertTrue((boolean) $this->subject->always()->checkReturned(new EqualToMatcher($this->returnValueA)));
        $this->assertFalse((boolean) $this->subject->always()->checkReturned(null));
        $this->assertFalse((boolean) $this->subject->always()->checkReturned($this->returnValueB));
        $this->assertFalse((boolean) $this->subject->always()->checkReturned('y'));
    }

    public function testAlwaysReturned()
    {
        $this->subject->setCalls(array($this->callA, $this->callA));
        $expected = new CallEventCollection(array($this->callAResponse, $this->callAResponse));

        $this->assertEquals($expected, $this->subject->always()->returned());
        $this->assertEquals($expected, $this->subject->always()->returned($this->returnValueA));
        $this->assertEquals($expected, $this->subject->always()->returned(new EqualToMatcher($this->returnValueA)));
    }

    public function testAlwaysReturnedFailure()
    {
        $this->subject->setCalls($this->calls);

        $expected = <<<'EOD'
Expected every call to return like <'x'>. Responded:
    - returned 'x'
    - returned 'y'
    - threw RuntimeException('You done goofed.')
    - threw RuntimeException('Consequences will never be the same.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->always()->returned($this->returnValueA);
    }

    public function testAlwaysReturnedFailureWithNoMatcher()
    {
        $this->subject->setCalls($this->calls);

        $expected = <<<'EOD'
Expected every call to return. Responded:
    - returned 'x'
    - returned 'y'
    - threw RuntimeException('You done goofed.')
    - threw RuntimeException('Consequences will never be the same.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->always()->returned();
    }

    public function testAlwaysReturnedFailureWithNoCalls()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected every call to return like <'x'>. Never called."
        );
        $this->subject->always()->returned($this->returnValueA);
    }

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
            new CallEventCollection(array($this->callCResponse, $this->callDResponse)),
            $this->subject->threw()
        );
        $this->assertEquals(
            new CallEventCollection(array($this->callCResponse, $this->callDResponse)),
            $this->subject->threw('Exception')
        );
        $this->assertEquals(
            new CallEventCollection(array($this->callCResponse, $this->callDResponse)),
            $this->subject->threw('RuntimeException')
        );
        $this->assertEquals(
            new CallEventCollection(array($this->callCResponse)),
            $this->subject->threw($this->exceptionA)
        );
        $this->assertEquals(
            new CallEventCollection(array($this->callDResponse)),
            $this->subject->threw($this->exceptionB)
        );
        $this->assertEquals(
            new CallEventCollection(array($this->callCResponse)),
            $this->subject->threw(new EqualToMatcher($this->exceptionA))
        );
    }

    public function testThrewFailureExpectingAny()
    {
        $this->subject->setCalls(array($this->callA, $this->callB));
        $expected = <<<'EOD'
Expected call to throw. Responded:
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
            'Expected call to throw. Never called.'
        );
        $this->subject->threw();
    }

    public function testThrewFailureExpectingType()
    {
        $this->subject->setCalls(array($this->callC, $this->callD));
        $expected = <<<'EOD'
Expected call to throw 'Eloquent\Phony\Spy\Exception\UndefinedCallException' exception. Responded:
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
            "Expected call to throw 'Eloquent\Phony\Spy\Exception\UndefinedCallException' exception. Never called."
        );
        $this->subject->threw('Eloquent\Phony\Spy\Exception\UndefinedCallException');
    }

    public function testThrewFailureExpectingTypeWithNoExceptions()
    {
        $this->subject->setCalls(array($this->callA, $this->callB));
        $expected = <<<'EOD'
Expected call to throw 'Eloquent\Phony\Spy\Exception\UndefinedCallException' exception. Responded:
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
Expected call to throw exception equal to RuntimeException(). Responded:
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
            'Expected call to throw exception equal to RuntimeException(). Never called.'
        );
        $this->subject->threw(new RuntimeException());
    }

    public function testThrewFailureExpectingExceptionWithNoExceptions()
    {
        $this->subject->setCalls(array($this->callA, $this->callB));
        $expected = <<<'EOD'
Expected call to throw exception equal to RuntimeException(). Responded:
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
Expected call to throw exception like <RuntimeException Object (...)>. Responded:
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
            'Expected call to throw exception like <RuntimeException Object (...)>. Never called.'
        );
        $this->subject->threw(new EqualToMatcher(new RuntimeException()));
    }

    public function testThrewFailureExpectingMatcherWithNoExceptions()
    {
        $this->subject->setCalls(array($this->callA, $this->callB));
        $expected = <<<'EOD'
Expected call to throw exception like <RuntimeException Object (...)>. Responded:
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

    public function testCheckAlwaysThrew()
    {
        $this->assertFalse((boolean) $this->subject->always()->checkThrew());
        $this->assertFalse((boolean) $this->subject->always()->checkThrew('Exception'));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew('RuntimeException'));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew($this->exceptionA));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew($this->exceptionB));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew(new EqualToMatcher($this->exceptionA)));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew('InvalidArgumentException'));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew(new Exception()));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew(new RuntimeException()));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew(new EqualToMatcher(null)));

        $this->subject->setCalls($this->calls);

        $this->assertFalse((boolean) $this->subject->always()->checkThrew());
        $this->assertFalse((boolean) $this->subject->always()->checkThrew('Exception'));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew('RuntimeException'));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew($this->exceptionA));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew($this->exceptionB));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew(new EqualToMatcher($this->exceptionA)));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew('InvalidArgumentException'));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew(new Exception()));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew(new RuntimeException()));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew(new EqualToMatcher(null)));

        $this->subject->setCalls(array($this->callC, $this->callC));

        $this->assertTrue((boolean) $this->subject->always()->checkThrew());
        $this->assertTrue((boolean) $this->subject->always()->checkThrew('Exception'));
        $this->assertTrue((boolean) $this->subject->always()->checkThrew('RuntimeException'));
        $this->assertTrue((boolean) $this->subject->always()->checkThrew($this->exceptionA));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew($this->exceptionB));
        $this->assertTrue((boolean) $this->subject->always()->checkThrew(new EqualToMatcher($this->exceptionA)));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew('InvalidArgumentException'));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew(new Exception()));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew(new RuntimeException()));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew(new EqualToMatcher(null)));

        $this->subject->setCalls(array($this->callA, $this->callA));

        $this->assertFalse((boolean) $this->subject->always()->checkThrew(new EqualToMatcher(null)));
    }

    public function testAlwaysThrew()
    {
        $this->subject->setCalls(array($this->callC, $this->callC));
        $expected = new CallEventCollection(array($this->callCResponse, $this->callCResponse));

        $this->assertEquals($expected, $this->subject->always()->threw());
        $this->assertEquals($expected, $this->subject->always()->threw('Exception'));
        $this->assertEquals($expected, $this->subject->always()->threw('RuntimeException'));
        $this->assertEquals($expected, $this->subject->always()->threw($this->exceptionA));
        $this->assertEquals($expected, $this->subject->always()->threw(new EqualToMatcher($this->exceptionA)));
    }

    public function testAlwaysThrewFailureExpectingAny()
    {
        $this->subject->setCalls($this->calls);
        $expected = <<<'EOD'
Expected every call to throw. Responded:
    - returned 'x'
    - returned 'y'
    - threw RuntimeException('You done goofed.')
    - threw RuntimeException('Consequences will never be the same.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->always()->threw();
    }

    public function testAlwaysThrewFailureExpectingAnyButNothingThrown()
    {
        $this->subject->setCalls(array($this->callA, $this->callB));
        $expected = <<<'EOD'
Expected every call to throw. Responded:
    - returned 'x'
    - returned 'y'
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->always()->threw();
    }

    public function testAlwaysThrewFailureExpectingAnyWithNoCalls()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected every call to throw. Never called.'
        );
        $this->subject->always()->threw();
    }

    public function testAlwaysThrewFailureExpectingType()
    {
        $this->subject->setCalls(array($this->callC, $this->callD));
        $expected = <<<'EOD'
Expected every call to throw 'Eloquent\Phony\Spy\Exception\UndefinedCallException' exception. Responded:
    - threw RuntimeException('You done goofed.')
    - threw RuntimeException('Consequences will never be the same.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->always()->threw('Eloquent\Phony\Spy\Exception\UndefinedCallException');
    }

    public function testAlwaysThrewFailureExpectingTypeWithNoCalls()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected every call to throw 'Eloquent\Phony\Spy\Exception\UndefinedCallException' exception. " .
                "Never called."
        );
        $this->subject->always()->threw('Eloquent\Phony\Spy\Exception\UndefinedCallException');
    }

    public function testAlwaysThrewFailureExpectingTypeWithNoExceptions()
    {
        $this->subject->setCalls(array($this->callA, $this->callB));
        $expected = <<<'EOD'
Expected every call to throw 'Eloquent\Phony\Spy\Exception\UndefinedCallException' exception. Responded:
    - returned 'x'
    - returned 'y'
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->always()->threw('Eloquent\Phony\Spy\Exception\UndefinedCallException');
    }

    public function testAlwaysThrewFailureExpectingException()
    {
        $this->subject->setCalls(array($this->callC, $this->callD));
        $expected = <<<'EOD'
Expected every call to throw exception equal to RuntimeException(). Responded:
    - threw RuntimeException('You done goofed.')
    - threw RuntimeException('Consequences will never be the same.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->always()->threw(new RuntimeException());
    }

    public function testAlwaysThrewFailureExpectingExceptionWithNoCalls()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected every call to throw exception equal to RuntimeException(). Never called.'
        );
        $this->subject->always()->threw(new RuntimeException());
    }

    public function testAlwaysThrewFailureExpectingExceptionWithNoExceptions()
    {
        $this->subject->setCalls(array($this->callA, $this->callB));
        $expected = <<<'EOD'
Expected every call to throw exception equal to RuntimeException(). Responded:
    - returned 'x'
    - returned 'y'
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->always()->threw(new RuntimeException());
    }

    public function testAlwaysThrewFailureExpectingMatcher()
    {
        $this->subject->setCalls(array($this->callC, $this->callD));
        $expected = <<<'EOD'
Expected every call to throw exception like <RuntimeException Object (...)>. Responded:
    - threw RuntimeException('You done goofed.')
    - threw RuntimeException('Consequences will never be the same.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->always()->threw(new EqualToMatcher(new RuntimeException()));
    }

    public function testAlwaysThrewFailureExpectingMatcherWithNoCalls()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected every call to throw exception like <RuntimeException Object (...)>. Never called.'
        );
        $this->subject->always()->threw(new EqualToMatcher(new RuntimeException()));
    }

    public function testAlwaysThrewFailureExpectingMatcherWithNoExceptions()
    {
        $this->subject->setCalls(array($this->callA, $this->callB));
        $expected = <<<'EOD'
Expected every call to throw exception like <RuntimeException Object (...)>. Responded:
    - returned 'x'
    - returned 'y'
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->always()->threw(new EqualToMatcher(new RuntimeException()));
    }

    public function testCardinalityMethods()
    {
        $this->subject->never();

        $this->assertEquals(new Cardinality(0, 0), $this->subject->never()->cardinality());
        $this->assertEquals(new Cardinality(1, 1), $this->subject->once()->cardinality());
        $this->assertEquals(new Cardinality(2, 2), $this->subject->times(2)->cardinality());
        $this->assertEquals(new Cardinality(2, 2), $this->subject->twice()->cardinality());
        $this->assertEquals(new Cardinality(3, 3), $this->subject->thrice()->cardinality());
        $this->assertEquals(new Cardinality(3), $this->subject->atLeast(3)->cardinality());
        $this->assertEquals(new Cardinality(null, 4), $this->subject->atMost(4)->cardinality());
        $this->assertEquals(new Cardinality(5, 6), $this->subject->between(5, 6)->cardinality());
        $this->assertEquals(new Cardinality(5, 6, true), $this->subject->between(5, 6)->always()->cardinality());
    }
}
