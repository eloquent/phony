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
use Eloquent\Phony\Clock\TestClock;
use Eloquent\Phony\Integration\Phpunit\PhpunitMatcherDriver;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Test\TestAssertionRecorder;
use Exception;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionFunction;
use RuntimeException;

class SpyVerifierTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->spySubject = function () {
            return '= ' .implode(', ', func_get_args());
        };
        $this->reflector = new ReflectionFunction($this->spySubject);
        $this->clock = new TestClock();
        $this->spy = new Spy($this->spySubject, $this->reflector, null, $this->clock);
        $this->matcherFactory = new MatcherFactory(array(new PhpunitMatcherDriver()));
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

        $this->returnValueA = 'returnValueA';
        $this->returnValueB = 'returnValueB';
        $this->exceptionA = new RuntimeException('You done goofed.');
        $this->exceptionB = new RuntimeException('Consequences will never be the same.');
        $this->thisValueA = (object) array();
        $this->thisValueB = (object) array();
        $this->arguments = array('argumentA', 'argumentB', 'argumentC');
        $this->argumentMatchers = $this->matcherFactory->adaptAll($this->arguments);
        $this->callA = new Call($this->reflector, $this->arguments, null, 0, 1.11, 2.22);
        $this->callB = new Call($this->reflector, array(), null, 1, 3.33, 4.44);
        $this->callC = new Call($this->reflector, array(), $this->returnValueA, 2, 5.55, 6.66, $this->exceptionA, $this->thisValueA);
        $this->callD = new Call($this->reflector, array(), $this->returnValueB, 3, 7.77, 8.88, $this->exceptionB, $this->thisValueB);
        $this->calls = array($this->callA, $this->callB, $this->callC, $this->callD);
        $this->wrappedCallA = $this->callVerifierFactory->adapt($this->callA);
        $this->wrappedCallB = $this->callVerifierFactory->adapt($this->callB);
        $this->wrappedCallC = $this->callVerifierFactory->adapt($this->callC);
        $this->wrappedCallD = $this->callVerifierFactory->adapt($this->callD);
        $this->wrappedCalls = array($this->wrappedCallA, $this->wrappedCallB, $this->wrappedCallC, $this->wrappedCallD);
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
        $this->assertSame($this->reflector, $this->subject->reflector());
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

    public function testInvoke()
    {
        $spy = $this->subject;
        $spy('argumentA');
        $spy('argumentB', 'argumentC');
        $reflector = $spy->reflector();
        $thisValue = $this->thisValue($this->spySubject);
        $expected = array(
            new Call($reflector, array('argumentA'), '= argumentA', 0, 0.123, 1.123, null, $thisValue),
            new Call(
                $reflector,
                array('argumentB', 'argumentC'),
                '= argumentB, argumentC',
                1,
                2.123,
                3.123,
                null,
                $thisValue
            ),
        );

        $this->assertEquals($expected, $spy->spy()->calls());
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
    - Eloquent\Phony\Spy\{closure}('argumentA', 'argumentB', 'argumentC')
    - Eloquent\Phony\Spy\{closure}()
    - Eloquent\Phony\Spy\{closure}()
    - Eloquent\Phony\Spy\{closure}()
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
    - Eloquent\Phony\Spy\{closure}('argumentA', 'argumentB', 'argumentC')
    - Eloquent\Phony\Spy\{closure}()
    - Eloquent\Phony\Spy\{closure}()
    - Eloquent\Phony\Spy\{closure}()
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertCalledAfter($spyVerifier);
    }

    public function calledWithData()
    {
        //                                    arguments                                                  calledWith calledWithExactly
        return array(
            'Exact arguments'        => array(array('argumentA', 'argumentB', 'argumentC'),              true,      true),
            'First arguments'        => array(array('argumentA', 'argumentB'),                           true,      false),
            'Single argument'        => array(array('argumentA'),                                        true,      false),
            'Last arguments'         => array(array('argumentB', 'argumentC'),                           false,     false),
            'Last argument'          => array(array('argumentC'),                                        false,     false),
            'Extra arguments'        => array(array('argumentA', 'argumentB', 'argumentC', 'argumentD'), false,     false),
            'First argument differs' => array(array('argumentD', 'argumentB', 'argumentC'),              false,     false),
            'Last argument differs'  => array(array('argumentA', 'argumentB', 'argumentD'),              false,     false),
            'Unused argument'        => array(array('argumentD'),                                        false,     false),
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

        $this->assertNull($this->subject->assertCalledWith('argumentA', 'argumentB', 'argumentC'));
        $this->assertNull(
            $this->subject
                ->assertCalledWith($this->argumentMatchers[0], $this->argumentMatchers[1], $this->argumentMatchers[2])
        );
        $this->assertNull($this->subject->assertCalledWith('argumentA', 'argumentB'));
        $this->assertNull($this->subject->assertCalledWith($this->argumentMatchers[0], $this->argumentMatchers[1]));
        $this->assertNull($this->subject->assertCalledWith('argumentA'));
        $this->assertNull($this->subject->assertCalledWith($this->argumentMatchers[0]));
        $this->assertNull($this->subject->assertCalledWith());
        $this->assertSame(7, $this->assertionRecorder->successCount());
    }

    public function testAssertCalledWithFailure()
    {
        $this->subject->setCalls($this->calls);

        $expected = <<<'EOD'
Expected arguments like:
    <'argumentB'>, <'argumentC'>, <any>*
Actual calls:
    - 'argumentA', 'argumentB', 'argumentC'
    - <none>
    - <none>
    - <none>
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertCalledWith('argumentB', 'argumentC');
    }

    public function testAssertCalledWithFailureWithNoCalls()
    {
        $expected = <<<'EOD'
Expected arguments like:
    <'argumentB'>, <'argumentC'>, <any>*
Never called.
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertCalledWith('argumentB', 'argumentC');
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

        $this->assertNull($this->subject->assertAlwaysCalledWith('argumentA', 'argumentB', 'argumentC'));
        $this->assertNull(
            $this->subject->assertAlwaysCalledWith(
                $this->argumentMatchers[0],
                $this->argumentMatchers[1],
                $this->argumentMatchers[2]
            )
        );
        $this->assertNull($this->subject->assertAlwaysCalledWith('argumentA', 'argumentB'));
        $this->assertNull(
            $this->subject->assertAlwaysCalledWith($this->argumentMatchers[0], $this->argumentMatchers[1])
        );
        $this->assertNull($this->subject->assertAlwaysCalledWith('argumentA'));
        $this->assertNull($this->subject->assertAlwaysCalledWith($this->argumentMatchers[0]));
        $this->assertNull($this->subject->assertAlwaysCalledWith());
        $this->assertSame(7, $this->assertionRecorder->successCount());
    }

    public function testAssertAlwaysCalledWithFailure()
    {
        $this->subject->setCalls($this->calls);

        $expected = <<<'EOD'
Expected every call with arguments like:
    <'argumentA'>, <'argumentB'>, <'argumentC'>, <any>*
Actual calls:
    - 'argumentA', 'argumentB', 'argumentC'
    - <none>
    - <none>
    - <none>
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertAlwaysCalledWith('argumentA', 'argumentB', 'argumentC');
    }

    public function testAssertAlwaysCalledWithFailureWithNoCalls()
    {
        $expected = <<<'EOD'
Expected every call with arguments like:
    <'argumentA'>, <'argumentB'>, <'argumentC'>, <any>*
Never called.
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertAlwaysCalledWith('argumentA', 'argumentB', 'argumentC');
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

        $this->assertNull($this->subject->assertCalledWithExactly('argumentA', 'argumentB', 'argumentC'));
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
    <'argumentB'>, <'argumentC'>
Actual calls:
    - 'argumentA', 'argumentB', 'argumentC'
    - <none>
    - <none>
    - <none>
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertCalledWithExactly('argumentB', 'argumentC');
    }

    public function testAssertCalledWithExactlyFailureWithNoCalls()
    {
        $expected = <<<'EOD'
Expected arguments like:
    <'argumentB'>, <'argumentC'>
Never called.
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertCalledWithExactly('argumentB', 'argumentC');
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

        $this->assertNull($this->subject->assertAlwaysCalledWithExactly('argumentA', 'argumentB', 'argumentC'));
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
    <'argumentA'>, <'argumentB'>, <'argumentC'>
Actual calls:
    - 'argumentA', 'argumentB', 'argumentC'
    - <none>
    - <none>
    - <none>
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertAlwaysCalledWithExactly('argumentA', 'argumentB', 'argumentC');
    }

    public function testAssertAlwaysCalledWithExactlyFailureWithNoCalls()
    {
        $expected = <<<'EOD'
Expected every call with arguments like:
    <'argumentA'>, <'argumentB'>, <'argumentC'>
Never called.
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertAlwaysCalledWithExactly('argumentA', 'argumentB', 'argumentC');
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

        $this->assertNull($this->subject->assertNeverCalledWith('argumentB', 'argumentC'));
        $this->assertNull(
            $this->subject->assertNeverCalledWith($this->argumentMatchers[1], $this->argumentMatchers[2])
        );
        $this->assertNull($this->subject->assertNeverCalledWith('argumentC'));
        $this->assertNull($this->subject->assertNeverCalledWith($this->argumentMatchers[2]));
        $this->assertNull($this->subject->assertNeverCalledWith('argumentA', 'argumentB', 'argumentC', 'argumentD'));
        $this->assertNull(
            $this->subject->assertNeverCalledWith(
                $this->argumentMatchers[0],
                $this->argumentMatchers[1],
                $this->argumentMatchers[2],
                $this->matcherFactory->adapt('argumentD')
            )
        );
        $this->assertNull($this->subject->assertNeverCalledWith('argumentD', 'argumentB', 'argumentC'));
        $this->assertNull(
            $this->subject->assertNeverCalledWith(
                $this->matcherFactory->adapt('argumentD'),
                $this->argumentMatchers[1],
                $this->argumentMatchers[2]
            )
        );
        $this->assertNull($this->subject->assertNeverCalledWith('argumentA', 'argumentB', 'argumentD'));
        $this->assertNull(
            $this->subject->assertNeverCalledWith(
                $this->argumentMatchers[0],
                $this->argumentMatchers[1],
                $this->matcherFactory->adapt('argumentD')
            )
        );
        $this->assertNull($this->subject->assertNeverCalledWith('argumentD'));
        $this->assertNull($this->subject->assertNeverCalledWith($this->matcherFactory->adapt('argumentD')));
        $this->assertSame(13, $this->assertionRecorder->successCount());
    }

    public function testAssertNeverCalledWithFailure()
    {
        $this->subject->setCalls($this->calls);

        $expected = <<<'EOD'
Expected no call with arguments like:
    <'argumentA'>, <'argumentB'>, <'argumentC'>, <any>*
Actual calls:
    - 'argumentA', 'argumentB', 'argumentC'
    - <none>
    - <none>
    - <none>
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertNeverCalledWith('argumentA', 'argumentB', 'argumentC');
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

        $this->assertNull($this->subject->assertNeverCalledWithExactly('argumentA', 'argumentB'));
        $this->assertNull(
            $this->subject->assertNeverCalledWithExactly($this->argumentMatchers[0], $this->argumentMatchers[1])
        );
        $this->assertNull($this->subject->assertNeverCalledWithExactly('argumentA'));
        $this->assertNull($this->subject->assertNeverCalledWithExactly($this->argumentMatchers[0]));
        $this->assertNull($this->subject->assertNeverCalledWithExactly('argumentB', 'argumentC'));
        $this->assertNull(
            $this->subject->assertNeverCalledWithExactly($this->argumentMatchers[1], $this->argumentMatchers[2])
        );
        $this->assertNull($this->subject->assertNeverCalledWithExactly('argumentC'));
        $this->assertNull($this->subject->assertNeverCalledWithExactly($this->argumentMatchers[2]));
        $this->assertNull(
            $this->subject->assertNeverCalledWithExactly('argumentA', 'argumentB', 'argumentC', 'argumentD')
        );
        $this->assertNull(
            $this->subject->assertNeverCalledWithExactly(
                $this->argumentMatchers[0],
                $this->argumentMatchers[1],
                $this->argumentMatchers[2],
                $this->matcherFactory->adapt('argumentD')
            )
        );
        $this->assertNull($this->subject->assertNeverCalledWithExactly('argumentD', 'argumentB', 'argumentC'));
        $this->assertNull(
            $this->subject->assertNeverCalledWithExactly(
                $this->matcherFactory->adapt('argumentD'),
                $this->argumentMatchers[1],
                $this->argumentMatchers[2]
            )
        );
        $this->assertNull($this->subject->assertNeverCalledWithExactly('argumentA', 'argumentB', 'argumentD'));
        $this->assertNull(
            $this->subject->assertNeverCalledWithExactly(
                $this->argumentMatchers[0],
                $this->argumentMatchers[1],
                $this->matcherFactory->adapt('argumentD')
            )
        );
        $this->assertNull($this->subject->assertNeverCalledWithExactly('argumentD'));
        $this->assertNull($this->subject->assertNeverCalledWithExactly($this->matcherFactory->adapt('argumentD')));
        $this->assertSame(17, $this->assertionRecorder->successCount());
    }

    public function testAssertNeverCalledWithExactlyFailure()
    {
        $this->subject->setCalls($this->calls);

        $expected = <<<'EOD'
Expected no call with arguments like:
    <'argumentA'>, <'argumentB'>, <'argumentC'>
Actual calls:
    - 'argumentA', 'argumentB', 'argumentC'
    - <none>
    - <none>
    - <none>
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertNeverCalledWithExactly('argumentA', 'argumentB', 'argumentC');
    }

    public function testCalledOn()
    {
        $this->assertFalse($this->subject->calledOn(null));
        $this->assertFalse($this->subject->calledOn($this->thisValueA));
        $this->assertFalse($this->subject->calledOn($this->thisValueB));
        $this->assertFalse($this->subject->calledOn($this->identicalTo($this->thisValueA)));
        $this->assertFalse($this->subject->calledOn((object) array()));

        $this->subject->setCalls($this->calls);

        $this->assertTrue($this->subject->calledOn(null));
        $this->assertTrue($this->subject->calledOn($this->thisValueA));
        $this->assertTrue($this->subject->calledOn($this->thisValueB));
        $this->assertTrue($this->subject->calledOn($this->identicalTo($this->thisValueA)));
        $this->assertFalse($this->subject->calledOn((object) array()));
    }

    public function testAssertCalledOn()
    {
        $this->subject->setCalls($this->calls);

        $this->assertNull($this->subject->assertCalledOn(null));
        $this->assertNull($this->subject->assertCalledOn($this->thisValueA));
        $this->assertNull($this->subject->assertCalledOn($this->thisValueB));
        $this->assertNull($this->subject->assertCalledOn($this->identicalTo($this->thisValueA)));
        $this->assertSame(4, $this->assertionRecorder->successCount());
    }

    public function testAssertCalledOnFailure()
    {
        $this->subject->setCalls($this->calls);
        $expected = <<<'EOD'
Not called on expected object. Actual objects:
    - null
    - null
    - stdClass Object ()
    - stdClass Object ()
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
Not called on object like <is identical to an object of class "stdClass">. Actual objects:
    - null
    - null
    - stdClass Object ()
    - stdClass Object ()
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertCalledOn($this->identicalTo((object) array()));
    }

    public function testAssertCalledOnFailureWithMatcherWithNoCalls()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Not called on object like <is identical to an object of class "stdClass">. Never called.'
        );
        $this->subject->assertCalledOn($this->identicalTo((object) array()));
    }

    public function testAlwaysCalledOn()
    {
        $this->assertFalse($this->subject->alwaysCalledOn(null));
        $this->assertFalse($this->subject->alwaysCalledOn($this->thisValueA));
        $this->assertFalse($this->subject->alwaysCalledOn($this->thisValueB));
        $this->assertFalse($this->subject->alwaysCalledOn($this->identicalTo($this->thisValueA)));
        $this->assertFalse($this->subject->alwaysCalledOn((object) array()));

        $this->subject->setCalls($this->calls);

        $this->assertFalse($this->subject->alwaysCalledOn(null));
        $this->assertFalse($this->subject->alwaysCalledOn($this->thisValueA));
        $this->assertFalse($this->subject->alwaysCalledOn($this->thisValueB));
        $this->assertFalse($this->subject->alwaysCalledOn($this->identicalTo($this->thisValueA)));
        $this->assertFalse($this->subject->alwaysCalledOn((object) array()));

        $this->subject->setCalls(array($this->callC, $this->callC));

        $this->assertTrue($this->subject->alwaysCalledOn($this->thisValueA));
        $this->assertTrue($this->subject->alwaysCalledOn($this->identicalTo($this->thisValueA)));
        $this->assertFalse($this->subject->alwaysCalledOn(null));
        $this->assertFalse($this->subject->alwaysCalledOn($this->thisValueB));
        $this->assertFalse($this->subject->alwaysCalledOn((object) array()));
    }

    public function testAssertAlwaysCalledOn()
    {
        $this->subject->setCalls(array($this->callC, $this->callC));

        $this->assertNull($this->subject->assertAlwaysCalledOn($this->thisValueA));
        $this->assertNull($this->subject->assertAlwaysCalledOn($this->identicalTo($this->thisValueA)));
        $this->assertSame(2, $this->assertionRecorder->successCount());
    }

    public function testAssertAlwaysCalledOnFailure()
    {
        $this->subject->setCalls($this->calls);
        $expected = <<<'EOD'
Not always called on expected object. Actual objects:
    - null
    - null
    - stdClass Object ()
    - stdClass Object ()
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
Not always called on object like <is identical to an object of class "stdClass">. Actual objects:
    - null
    - null
    - stdClass Object ()
    - stdClass Object ()
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertAlwaysCalledOn($this->identicalTo($this->thisValueA));
    }

    public function testAssertAlwaysCalledOnFailureWithMatcherWithNoCalls()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Not called on object like <is identical to an object of class "stdClass">. Never called.'
        );
        $this->subject->assertAlwaysCalledOn($this->identicalTo($this->thisValueA));
    }

    public function testReturned()
    {
        $this->assertFalse($this->subject->returned(null));
        $this->assertFalse($this->subject->returned($this->returnValueA));
        $this->assertFalse($this->subject->returned($this->returnValueB));
        $this->assertFalse($this->subject->returned($this->identicalTo($this->returnValueA)));
        $this->assertFalse($this->subject->returned('anotherValue'));

        $this->subject->setCalls($this->calls);

        $this->assertTrue($this->subject->returned(null));
        $this->assertTrue($this->subject->returned($this->returnValueA));
        $this->assertTrue($this->subject->returned($this->returnValueB));
        $this->assertTrue($this->subject->returned($this->identicalTo($this->returnValueA)));
        $this->assertFalse($this->subject->returned('anotherValue'));
    }

    public function testAssertReturned()
    {
        $this->subject->setCalls($this->calls);

        $this->assertNull($this->subject->assertReturned(null));
        $this->assertNull($this->subject->assertReturned($this->returnValueA));
        $this->assertNull($this->subject->assertReturned($this->returnValueB));
        $this->assertNull($this->subject->assertReturned($this->identicalTo($this->returnValueA)));
        $this->assertSame(4, $this->assertionRecorder->successCount());
    }

    public function testAssertReturnedFailure()
    {
        $this->subject->setCalls($this->calls);

        $expected = <<<'EOD'
Expected return value like <'anotherValue'>. Actually returned:
    - null
    - null
    - 'returnValueA'
    - 'returnValueB'
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertReturned('anotherValue');
    }

    public function testAssertReturnedFailureWithNoCalls()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected return value like <'returnValueA'>. Never called."
        );
        $this->subject->assertReturned($this->returnValueA);
    }

    public function testAlwaysReturned()
    {
        $this->assertFalse($this->subject->alwaysReturned(null));
        $this->assertFalse($this->subject->alwaysReturned($this->returnValueA));
        $this->assertFalse($this->subject->alwaysReturned($this->returnValueB));
        $this->assertFalse($this->subject->alwaysReturned($this->identicalTo($this->returnValueA)));
        $this->assertFalse($this->subject->alwaysReturned('anotherValue'));

        $this->subject->setCalls($this->calls);

        $this->assertFalse($this->subject->alwaysReturned(null));
        $this->assertFalse($this->subject->alwaysReturned($this->returnValueA));
        $this->assertFalse($this->subject->alwaysReturned($this->returnValueB));
        $this->assertFalse($this->subject->alwaysReturned($this->identicalTo($this->returnValueA)));
        $this->assertFalse($this->subject->alwaysReturned('anotherValue'));

        $this->subject->setCalls(array($this->callC, $this->callC));

        $this->assertTrue($this->subject->alwaysReturned($this->returnValueA));
        $this->assertTrue($this->subject->alwaysReturned($this->identicalTo($this->returnValueA)));
        $this->assertFalse($this->subject->alwaysReturned(null));
        $this->assertFalse($this->subject->alwaysReturned($this->returnValueB));
        $this->assertFalse($this->subject->alwaysReturned('anotherValue'));
    }

    public function testAssertAlwaysReturned()
    {
        $this->subject->setCalls(array($this->callC, $this->callC));

        $this->assertNull($this->subject->assertAlwaysReturned($this->returnValueA));
        $this->assertNull($this->subject->assertAlwaysReturned($this->identicalTo($this->returnValueA)));
        $this->assertSame(2, $this->assertionRecorder->successCount());
    }

    public function testAssertAlwaysReturnedFailure()
    {
        $this->subject->setCalls($this->calls);

        $expected = <<<'EOD'
Expected every call with return value like <'returnValueA'>. Actually returned:
    - null
    - null
    - 'returnValueA'
    - 'returnValueB'
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertAlwaysReturned($this->returnValueA);
    }

    public function testAssertAlwaysReturnedFailureWithNoCalls()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected return value like <'returnValueA'>. Never called."
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
        $this->assertFalse($this->subject->threw($this->identicalTo($this->exceptionA)));
        $this->assertFalse($this->subject->threw('InvalidArgumentException'));
        $this->assertFalse($this->subject->threw(new Exception()));
        $this->assertFalse($this->subject->threw(new RuntimeException()));
        $this->assertFalse($this->subject->threw($this->isNull()));
        $this->assertFalse($this->subject->threw(111));

        $this->subject->setCalls($this->calls);

        $this->assertTrue($this->subject->threw());
        $this->assertTrue($this->subject->threw('Exception'));
        $this->assertTrue($this->subject->threw('RuntimeException'));
        $this->assertTrue($this->subject->threw($this->exceptionA));
        $this->assertTrue($this->subject->threw($this->exceptionB));
        $this->assertTrue($this->subject->threw($this->identicalTo($this->exceptionA)));
        $this->assertFalse($this->subject->threw('InvalidArgumentException'));
        $this->assertFalse($this->subject->threw(new Exception()));
        $this->assertFalse($this->subject->threw(new RuntimeException()));
        $this->assertTrue($this->subject->threw($this->isNull()));
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
        $this->assertNull($this->subject->assertThrew($this->identicalTo($this->exceptionA)));
        $this->assertNull($this->subject->assertThrew($this->isNull()));
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
Expected exception like <is identical to an object of class "RuntimeException">. Actually threw:
    - RuntimeException('You done goofed.')
    - RuntimeException('Consequences will never be the same.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertThrew($this->identicalTo(new RuntimeException('You done goofed.')));
    }

    public function testAssertThrewFailureExpectingMatcherWithNoCalls()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected exception like <is identical to an object of class "RuntimeException">. Never called.'
        );
        $this->subject->assertThrew($this->identicalTo(new RuntimeException('You done goofed.')));
    }

    public function testAssertThrewFailureExpectingMatcherWithNoExceptions()
    {
        $this->subject->setCalls(array($this->callA, $this->callB));

        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected exception like <is identical to an object of class "RuntimeException">. ' .
                'Nothing thrown in 2 call(s).'
        );
        $this->subject->assertThrew($this->identicalTo(new RuntimeException('You done goofed.')));
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
        $this->assertFalse($this->subject->alwaysThrew($this->identicalTo($this->exceptionA)));
        $this->assertFalse($this->subject->alwaysThrew('InvalidArgumentException'));
        $this->assertFalse($this->subject->alwaysThrew(new Exception()));
        $this->assertFalse($this->subject->alwaysThrew(new RuntimeException()));
        $this->assertFalse($this->subject->alwaysThrew($this->isNull()));
        $this->assertFalse($this->subject->alwaysThrew(111));

        $this->subject->setCalls($this->calls);

        $this->assertFalse($this->subject->alwaysThrew());
        $this->assertFalse($this->subject->alwaysThrew('Exception'));
        $this->assertFalse($this->subject->alwaysThrew('RuntimeException'));
        $this->assertFalse($this->subject->alwaysThrew($this->exceptionA));
        $this->assertFalse($this->subject->alwaysThrew($this->exceptionB));
        $this->assertFalse($this->subject->alwaysThrew($this->identicalTo($this->exceptionA)));
        $this->assertFalse($this->subject->alwaysThrew('InvalidArgumentException'));
        $this->assertFalse($this->subject->alwaysThrew(new Exception()));
        $this->assertFalse($this->subject->alwaysThrew(new RuntimeException()));
        $this->assertFalse($this->subject->alwaysThrew($this->isNull()));
        $this->assertFalse($this->subject->alwaysThrew(111));

        $this->subject->setCalls(array($this->callC, $this->callC));

        $this->assertTrue($this->subject->alwaysThrew());
        $this->assertTrue($this->subject->alwaysThrew('Exception'));
        $this->assertTrue($this->subject->alwaysThrew('RuntimeException'));
        $this->assertTrue($this->subject->alwaysThrew($this->exceptionA));
        $this->assertFalse($this->subject->alwaysThrew($this->exceptionB));
        $this->assertTrue($this->subject->alwaysThrew($this->identicalTo($this->exceptionA)));
        $this->assertFalse($this->subject->alwaysThrew('InvalidArgumentException'));
        $this->assertFalse($this->subject->alwaysThrew(new Exception()));
        $this->assertFalse($this->subject->alwaysThrew(new RuntimeException()));
        $this->assertFalse($this->subject->alwaysThrew($this->isNull()));
        $this->assertFalse($this->subject->alwaysThrew(111));

        $this->subject->setCalls(array($this->callA, $this->callA));

        $this->assertTrue($this->subject->alwaysThrew($this->isNull()));
    }

    public function testAssertAlwaysThrew()
    {
        $this->subject->setCalls(array($this->callC, $this->callC));

        $this->assertNull($this->subject->assertAlwaysThrew());
        $this->assertNull($this->subject->assertAlwaysThrew('Exception'));
        $this->assertNull($this->subject->assertAlwaysThrew('RuntimeException'));
        $this->assertNull($this->subject->assertAlwaysThrew($this->exceptionA));
        $this->assertNull($this->subject->assertAlwaysThrew($this->identicalTo($this->exceptionA)));

        $this->subject->setCalls(array($this->callA, $this->callA));

        $this->assertNull($this->subject->assertAlwaysThrew($this->isNull()));
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
Expected every call to throw exception equal to RuntimeException('You done goofed.'). Actually threw:
    - RuntimeException('You done goofed.')
    - RuntimeException('Consequences will never be the same.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertAlwaysThrew(new RuntimeException('You done goofed.'));
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
Expected every call to throw exception like <is identical to an object of class "RuntimeException">. Actually threw:
    - RuntimeException('You done goofed.')
    - RuntimeException('Consequences will never be the same.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->assertAlwaysThrew($this->identicalTo(new RuntimeException('You done goofed.')));
    }

    public function testAssertAlwaysThrewFailureExpectingMatcherWithNoCalls()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected exception like <is identical to an object of class "RuntimeException">. Never called.'
        );
        $this->subject->assertAlwaysThrew($this->identicalTo(new RuntimeException('You done goofed.')));
    }

    public function testAssertAlwaysThrewFailureExpectingMatcherWithNoExceptions()
    {
        $this->subject->setCalls(array($this->callA, $this->callB));

        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected exception like <is identical to an object of class "RuntimeException">. ' .
                'Nothing thrown in 2 call(s).'
        );
        $this->subject->assertAlwaysThrew($this->identicalTo(new RuntimeException('You done goofed.')));
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
        $this->assertSame(-1, SpyVerifier::compareCallOrder($this->callA, $this->callB));
        $this->assertSame(1, SpyVerifier::compareCallOrder($this->callB, $this->callA));
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
