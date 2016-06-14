<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Spy;

use Eloquent\Phony\Assertion\AssertionRenderer;
use Eloquent\Phony\Assertion\ExceptionAssertionRecorder;
use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Call\CallVerifierFactory;
use Eloquent\Phony\Difference\DifferenceEngine;
use Eloquent\Phony\Event\EventSequence;
use Eloquent\Phony\Exporter\InlineExporter;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Matcher\AnyMatcher;
use Eloquent\Phony\Matcher\MatcherFactory;
use Eloquent\Phony\Matcher\MatcherVerifier;
use Eloquent\Phony\Matcher\WildcardMatcher;
use Eloquent\Phony\Reflection\FeatureDetector;
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Test\TestCallFactory;
use Eloquent\Phony\Test\TestClassA;
use Eloquent\Phony\Verification\Cardinality;
use Eloquent\Phony\Verification\GeneratorVerifierFactory;
use Eloquent\Phony\Verification\TraversableVerifierFactory;
use Error;
use Exception;
use PHPUnit_Framework_TestCase;
use RuntimeException;

class SpyVerifierTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->callback = 'implode';
        $this->callFactory = new TestCallFactory();
        $this->invoker = Invoker::instance();
        $this->generatorSpyFactory = GeneratorSpyFactory::instance();
        $this->traversableSpyFactory = TraversableSpyFactory::instance();
        $this->label = 'label';
        $this->spy = new SpyData(
            $this->callback,
            $this->label,
            $this->callFactory,
            $this->invoker,
            $this->generatorSpyFactory,
            $this->traversableSpyFactory
        );

        $this->objectSequencer = new Sequencer();
        $this->exporter = new InlineExporter(1, $this->objectSequencer);
        $this->matcherFactory =
            new MatcherFactory(AnyMatcher::instance(), WildcardMatcher::instance(), $this->exporter);
        $this->matcherVerifier = new MatcherVerifier();
        $this->generatorVerifierFactory = GeneratorVerifierFactory::instance();
        $this->traversableVerifierFactory = TraversableVerifierFactory::instance();
        $this->callVerifierFactory = CallVerifierFactory::instance();
        $this->assertionRecorder = ExceptionAssertionRecorder::instance();
        $this->invocableInspector = new InvocableInspector();
        $this->featureDetector = FeatureDetector::instance();
        $this->differenceEngine = new DifferenceEngine($this->featureDetector);
        $this->differenceEngine->setUseColor(false);
        $this->assertionRenderer = new AssertionRenderer(
            $this->invocableInspector,
            $this->matcherVerifier,
            $this->exporter,
            $this->differenceEngine,
            $this->featureDetector
        );
        $this->assertionRenderer->setUseColor(false);
        $this->subject = new SpyVerifier(
            $this->spy,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->traversableVerifierFactory,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );

        $this->generatorVerifierFactory->setCallVerifierFactory($this->callVerifierFactory);
        $this->traversableVerifierFactory->setCallVerifierFactory($this->callVerifierFactory);

        $this->callEventFactory = $this->callFactory->eventFactory();

        $this->returnValueA = 'x';
        $this->returnValueB = 'y';
        $this->exceptionA = new RuntimeException('You done goofed.');
        $this->exceptionB = new RuntimeException('Consequences will never be the same.');
        $this->thisValueA = new TestClassA();
        $this->thisValueB = new TestClassA();
        $this->arguments = Arguments::create('a', 'b', 'c');
        $this->matchers = $this->matcherFactory->adaptAll($this->arguments->all());
        $this->otherMatcher = $this->matcherFactory->adapt('d');
        $this->callA = $this->callFactory->create(
            $this->callEventFactory->createCalled(array($this->thisValueA, 'testClassAMethodA'), $this->arguments),
            ($responseEvent = $this->callEventFactory->createReturned($this->returnValueA)),
            null,
            $responseEvent
        );
        $this->callAResponse = $this->callA->responseEvent();
        $this->callB = $this->callFactory->create(
            $this->callEventFactory->createCalled(array($this->thisValueB, 'testClassAMethodA')),
            ($responseEvent = $this->callEventFactory->createReturned($this->returnValueB)),
            null,
            $responseEvent
        );
        $this->callBResponse = $this->callB->responseEvent();
        $this->callC = $this->callFactory->create(
            $this->callEventFactory->createCalled(array($this->thisValueA, 'testClassAMethodA'), $this->arguments),
            ($responseEvent = $this->callEventFactory->createThrew($this->exceptionA)),
            null,
            $responseEvent
        );
        $this->callCResponse = $this->callC->responseEvent();
        $this->callD = $this->callFactory->create(
            $this->callEventFactory->createCalled('implode'),
            ($responseEvent = $this->callEventFactory->createThrew($this->exceptionB)),
            null,
            $responseEvent
        );
        $this->callDResponse = $this->callD->responseEvent();
        $this->callE = $this->callFactory->create($this->callEventFactory->createCalled('implode'));
        $this->calls = array($this->callA, $this->callB, $this->callC, $this->callD, $this->callE);
        $this->wrappedCallA = $this->callVerifierFactory->fromCall($this->callA);
        $this->wrappedCallB = $this->callVerifierFactory->fromCall($this->callB);
        $this->wrappedCallC = $this->callVerifierFactory->fromCall($this->callC);
        $this->wrappedCallD = $this->callVerifierFactory->fromCall($this->callD);
        $this->wrappedCallE = $this->callVerifierFactory->fromCall($this->callE);
        $this->wrappedCalls = array(
            $this->wrappedCallA,
            $this->wrappedCallB,
            $this->wrappedCallC,
            $this->wrappedCallD,
            $this->wrappedCallE,
        );

        $this->iteratorCalledEvent = $this->callEventFactory->createCalled();
        $this->returnedTraversableEvent =
            $this->callEventFactory->createReturned(array('m' => 'n', 'p' => 'q', 'r' => 's', 'u' => 'v'));
        $this->iteratorEventA = $this->callEventFactory->createProduced('m', 'n');
        $this->iteratorEventC = $this->callEventFactory->createProduced('p', 'q');
        $this->iteratorEventE = $this->callEventFactory->createProduced('r', 's');
        $this->iteratorEventG = $this->callEventFactory->createProduced('u', 'v');
        $this->iteratorEvents = array(
            $this->iteratorEventA,
            $this->iteratorEventC,
            $this->iteratorEventE,
            $this->iteratorEventG,
        );
        $this->traversableEndEvent = $this->callEventFactory->createConsumed();
        $this->iteratorCall = $this->callFactory->create(
            $this->iteratorCalledEvent,
            $this->returnedTraversableEvent,
            $this->iteratorEvents,
            $this->traversableEndEvent
        );
        $this->iteratorCallWithNoEnd = $this->callFactory->create(
            $this->iteratorCalledEvent,
            $this->returnedTraversableEvent,
            $this->iteratorEvents
        );

        $this->callFactory->reset();

        $this->featureDetector = new FeatureDetector();
    }

    public function testConstructor()
    {
        $this->assertSame($this->spy, $this->subject->spy());
        $this->assertEquals(new Cardinality(1, null), $this->subject->cardinality());
    }

    public function testProxyMethods()
    {
        $this->assertFalse($this->subject->isAnonymous());
        $this->assertSame($this->callback, $this->subject->callback());
        $this->assertSame($this->label, $this->subject->label());
    }

    public function testSetLabel()
    {
        $this->assertSame($this->subject, $this->subject->setLabel(null));
        $this->assertNull($this->subject->label());

        $this->subject->setLabel($this->label);

        $this->assertSame($this->label, $this->subject->label());
    }

    public function testSetUseGeneratorSpies()
    {
        $this->assertSame($this->subject, $this->subject->setUseGeneratorSpies(true));
        $this->assertTrue($this->subject->useGeneratorSpies());
    }

    public function testSetUseTraversableSpies()
    {
        $this->assertSame($this->subject, $this->subject->setUseTraversableSpies(true));
        $this->assertTrue($this->subject->useTraversableSpies());
    }

    public function testSetCalls()
    {
        $this->subject->setCalls($this->calls);

        $this->assertSame($this->calls, $this->subject->spy()->allCalls());
    }

    public function testAddCall()
    {
        $this->subject->addCall($this->callA);

        $this->assertSame(array($this->callA), $this->subject->spy()->allCalls());

        $this->subject->addCall($this->callB);

        $this->assertSame(array($this->callA, $this->callB), $this->subject->spy()->allCalls());
    }

    public function testHasEvents()
    {
        $this->assertFalse($this->subject->hasEvents());

        $this->subject->addCall($this->callA);

        $this->assertTrue($this->subject->hasEvents());
    }

    public function testHasCalls()
    {
        $this->assertFalse($this->subject->hasCalls());

        $this->subject->addCall($this->callA);

        $this->assertTrue($this->subject->hasCalls());
    }

    public function testEventCount()
    {
        $this->assertSame(0, $this->subject->eventCount());

        $this->subject->addCall($this->callA);

        $this->assertSame(1, $this->subject->eventCount());
    }

    public function testCallCount()
    {
        $this->assertSame(0, $this->subject->callCount());
        $this->assertSame(0, count($this->subject));

        $this->subject->addCall($this->callA);

        $this->assertSame(1, $this->subject->callCount());
        $this->assertSame(1, count($this->subject));
    }

    public function testAllEvents()
    {
        $this->assertSame(array(), $this->subject->allEvents());

        $this->subject->addCall($this->callA);

        $this->assertSame(array($this->callA), $this->subject->allEvents());
    }

    public function testAllCalls()
    {
        $this->assertSame(array(), $this->subject->allCalls());

        $this->subject->setCalls($this->calls);

        $this->assertEquals($this->wrappedCalls, $this->subject->allCalls());
        $this->assertEquals($this->wrappedCalls, iterator_to_array($this->subject));
    }

    public function testFirstEvent()
    {
        $this->subject->setCalls($this->calls);

        $this->assertSame($this->callA, $this->subject->firstEvent());
    }

    public function testFirstEventFailureUndefined()
    {
        $this->subject->setCalls(array());

        $this->setExpectedException('Eloquent\Phony\Event\Exception\UndefinedEventException');
        $this->subject->firstEvent();
    }

    public function testLastEvent()
    {
        $this->subject->setCalls($this->calls);

        $this->assertSame($this->callE, $this->subject->lastEvent());
    }

    public function testLastEventFailureUndefined()
    {
        $this->subject->setCalls(array());

        $this->setExpectedException('Eloquent\Phony\Event\Exception\UndefinedEventException');
        $this->subject->lastEvent();
    }

    public function testEventAt()
    {
        $this->subject->addCall($this->callA);

        $this->assertSame($this->callA, $this->subject->eventAt());
        $this->assertSame($this->callA, $this->subject->eventAt(0));
        $this->assertSame($this->callA, $this->subject->eventAt(-1));
    }

    public function testEventAtFailure()
    {
        $this->setExpectedException('Eloquent\Phony\Event\Exception\UndefinedEventException');
        $this->subject->eventAt();
    }

    public function testFirstCall()
    {
        $this->subject->setCalls($this->calls);

        $this->assertEquals($this->wrappedCallA, $this->subject->firstCall());
    }

    public function testFirstCallFailureUndefined()
    {
        $this->subject->setCalls(array());

        $this->setExpectedException('Eloquent\Phony\Call\Exception\UndefinedCallException');
        $this->subject->firstCall();
    }

    public function testLastCall()
    {
        $this->subject->setCalls($this->calls);

        $this->assertEquals($this->wrappedCallE, $this->subject->lastCall());
    }

    public function testLastCallFailureUndefined()
    {
        $this->subject->setCalls(array());

        $this->setExpectedException('Eloquent\Phony\Call\Exception\UndefinedCallException');
        $this->subject->lastCall();
    }

    public function testCallAt()
    {
        $this->subject->setCalls($this->calls);

        $this->assertEquals($this->wrappedCallA, $this->subject->callAt(0));
        $this->assertEquals($this->wrappedCallB, $this->subject->callAt(1));
    }

    public function testCallAtFailureUndefined()
    {
        $this->setExpectedException('Eloquent\Phony\Call\Exception\UndefinedCallException');
        $this->subject->callAt(0);
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
                $this->callEventFactory->createCalled($spy, Arguments::create(array('a'))),
                ($responseEvent = $this->callEventFactory->createReturned('a')),
                null,
                $responseEvent
            ),
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy, Arguments::create(array('b', 'c'))),
                ($responseEvent = $this->callEventFactory->createReturned('bc')),
                null,
                $responseEvent
            ),
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy, Arguments::create(array('d'))),
                ($responseEvent = $this->callEventFactory->createReturned('d')),
                null,
                $responseEvent
            ),
        );

        $this->assertEquals($expected, $this->spy->allCalls());
    }

    public function testInvokeMethodsWithoutSubject()
    {
        $spy = new SpyData(
            null,
            '111',
            $this->callFactory,
            $this->invoker,
            $this->generatorSpyFactory,
            $this->traversableSpyFactory
        );
        $verifier = new SpyVerifier(
            $spy,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->traversableVerifierFactory,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );
        $verifier->invokeWith(array('a'));
        $verifier->invoke('b', 'c');
        $verifier('d');
        $this->callFactory->reset();
        $expected = array(
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy, Arguments::create('a')),
                ($responseEvent = $this->callEventFactory->createReturned(null)),
                null,
                $responseEvent
            ),
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy, Arguments::create('b', 'c')),
                ($responseEvent = $this->callEventFactory->createReturned(null)),
                null,
                $responseEvent
            ),
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy, Arguments::create('d')),
                ($responseEvent = $this->callEventFactory->createReturned(null)),
                null,
                $responseEvent
            ),
        );

        $this->assertEquals($expected, $spy->allCalls());
    }

    public function testInvokeWithExceptionThrown()
    {
        $exceptions = array(new Exception(), new Exception(), new Exception());
        $callback = function () use (&$exceptions) {
            list(, $exception) = each($exceptions);
            throw $exception;
        };
        $spy = new SpyData(
            $callback,
            '111',
            $this->callFactory,
            $this->invoker,
            $this->generatorSpyFactory,
            $this->traversableSpyFactory
        );
        $verifier = new SpyVerifier(
            $spy,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->traversableVerifierFactory,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );
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
                $this->callEventFactory->createCalled($spy, Arguments::create('a')),
                ($responseEvent = $this->callEventFactory->createThrew($exceptions[0])),
                null,
                $responseEvent
            ),
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy, Arguments::create('b', 'c')),
                ($responseEvent = $this->callEventFactory->createThrew($exceptions[1])),
                null,
                $responseEvent
            ),
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy, Arguments::create('d')),
                ($responseEvent = $this->callEventFactory->createThrew($exceptions[2])),
                null,
                $responseEvent
            ),
        );

        $this->assertEquals($expected, $spy->allCalls());
    }

    public function testInvokeWithWithReferenceParameters()
    {
        $callback = function (&$argument) {
            $argument = 'x';
        };
        $spy = new SpyData(
            $callback,
            '111',
            $this->callFactory,
            $this->invoker,
            $this->generatorSpyFactory,
            $this->traversableSpyFactory
        );
        $verifier = new SpyVerifier(
            $spy,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->traversableVerifierFactory,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );
        $value = null;
        $arguments = array(&$value);
        $verifier->invokeWith($arguments);

        $this->assertSame('x', $value);
    }

    public function testStopRecording()
    {
        $callback = function () {
            return 'x';
        };
        $spy = new SpyData(
            $callback,
            '111',
            $this->callFactory,
            $this->invoker,
            $this->generatorSpyFactory,
            $this->traversableSpyFactory
        );
        $verifier = new SpyVerifier(
            $spy,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->traversableVerifierFactory,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );
        $verifier->stopRecording()->invokeWith();
        $this->callFactory->reset();

        $this->assertSame(array(), $spy->allCalls());
    }

    public function testStartRecording()
    {
        $callback = function () {
            return 'x';
        };
        $spy = new SpyData(
            $callback,
            '111',
            $this->callFactory,
            $this->invoker,
            $this->generatorSpyFactory,
            $this->traversableSpyFactory
        );
        $verifier = new SpyVerifier(
            $spy,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->traversableVerifierFactory,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );
        $verifier->stopRecording()->invoke('a');
        $verifier->startRecording()->invoke('b');
        $this->callFactory->reset();
        $expected = array(
            $this->callFactory->create(
                $this->callEventFactory->createCalled($spy, Arguments::create('b')),
                ($responseEvent = $this->callEventFactory->createReturned('x')),
                null,
                $responseEvent
            ),
        );

        $this->assertEquals($expected, $spy->allCalls());
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
        $expected = new EventSequence($this->calls);

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
        $expected = new EventSequence(array($this->callA));

        $this->assertEquals($expected, $this->subject->once()->called());
    }

    public function testCalledOnceFailureWithNoCalls()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->once()->called();
    }

    public function testCalledOnceFailureWithMultipleCalls()
    {
        $this->subject->setCalls($this->calls);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->once()->called();
    }

    public function testCheckCalledTimes()
    {
        $this->assertTrue((boolean) $this->subject->times(0)->checkCalled());
        $this->assertFalse((boolean) $this->subject->times(5)->checkCalled());

        $this->subject->setCalls($this->calls);

        $this->assertFalse((boolean) $this->subject->times(0)->checkCalled());
        $this->assertTrue((boolean) $this->subject->times(5)->checkCalled());
    }

    public function testCalledTimes()
    {
        $this->subject->setCalls($this->calls);
        $expected = new EventSequence($this->calls);

        $this->assertEquals($expected, $this->subject->times(5)->called());
    }

    public function testCalledTimesFailure()
    {
        $this->subject->setCalls($this->calls);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
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
        $expected = new EventSequence(array($this->callA, $this->callC));

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
            new EventSequence($this->calls),
            $this->subject->calledWith($this->matcherFactory->wildcard())
        );
    }

    public function testCalledWithFailure()
    {
        $this->subject->setCalls($this->calls);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->calledWith('b', 'c');
    }

    public function testCalledWithFailureWithNoMatchers()
    {
        $this->subject->setCalls(array($this->callA));
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->calledWith();
    }

    public function testCalledWithFailureMissingArguments()
    {
        $this->subject->setCalls($this->calls);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->calledWith('a', 'b', 'c', 'd', 'e');
    }

    public function testCalledWithFailureWithNoCalls()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->calledWith('b', 'c');
    }

    public function testCalledWithFailureWithNoCallsAndNoMatchers()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->calledWith();
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
        $expected = new EventSequence(array($this->callA));

        $this->assertEquals($expected, $this->subject->once()->calledWith('a', 'b', 'c'));
        $this->assertEquals(
            $expected,
            $this->subject->once()->calledWith($this->matchers[0], $this->matchers[1], $this->matchers[2])
        );
    }

    public function testCalledOnceWithFailure()
    {
        $this->subject->setCalls($this->calls);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->once()->calledWith('a', 'b', 'c');
    }

    public function testCalledOnceWithFailureWithNoCalls()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
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
        $this->assertTrue((boolean) $this->subject->times(5)->checkCalledWith($this->matcherFactory->wildcard()));
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
        $expected = new EventSequence(array($this->callA, $this->callC));

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

        $expected = new EventSequence($this->calls);

        $this->assertEquals($expected, $this->subject->times(5)->calledWith($this->matcherFactory->wildcard()));
        $this->assertEquals($expected, $this->subject->times(5)->calledWith($this->matcherFactory->wildcard()));
    }

    public function testCalledTimesWithFailure()
    {
        $this->subject->setCalls($this->calls);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->times(5)->calledWith('a', 'b', 'c');
    }

    public function testCalledTimesWithFailureWithNoMatchers()
    {
        $this->subject->setCalls($this->calls);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->times(2)->calledWith();
    }

    public function testCalledTimesWithFailureWithNoCalls()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->times(5)->calledWith('a', 'b', 'c');
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
        $expected = new EventSequence(array($this->callA, $this->callA));

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

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->always()->calledWith('a', 'b', 'c');
    }

    public function testAlwaysCalledWithFailureWithNoMatchers()
    {
        $this->subject->setCalls($this->calls);

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->always()->calledWith();
    }

    public function testAlwaysCalledWithFailureWithNoCalls()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
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
        $expected = new EventSequence(array());

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

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->never()->calledWith('a', 'b', 'c');
    }

    public function testNeverCalledWithFailureWithNoMatchers()
    {
        $this->subject->setCalls($this->calls);

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->never()->calledWith();
    }

    public function testNeverCalledWithFailureWithWildcard()
    {
        $this->subject->setCalls($this->calls);

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->never()->calledWith('*');
    }

    public function testCheckCalledOn()
    {
        $this->assertFalse((boolean) $this->subject->checkCalledOn(null));
        $this->assertFalse((boolean) $this->subject->checkCalledOn($this->thisValueA));
        $this->assertFalse((boolean) $this->subject->checkCalledOn($this->thisValueB));
        $this->assertFalse((boolean) $this->subject->checkCalledOn($this->matcherFactory->equalTo($this->thisValueA)));
        $this->assertFalse((boolean) $this->subject->checkCalledOn((object) array()));

        $this->subject->setCalls($this->calls);

        $this->assertTrue((boolean) $this->subject->checkCalledOn(null));
        $this->assertTrue((boolean) $this->subject->checkCalledOn($this->thisValueA));
        $this->assertTrue((boolean) $this->subject->checkCalledOn($this->thisValueB));
        $this->assertTrue((boolean) $this->subject->checkCalledOn($this->matcherFactory->equalTo($this->thisValueA)));
        $this->assertFalse((boolean) $this->subject->checkCalledOn((object) array()));
    }

    public function testCalledOn()
    {
        $this->subject->setCalls($this->calls);

        $this->assertEquals(new EventSequence(array($this->callD, $this->callE)), $this->subject->calledOn(null));
        $this->assertEquals(
            new EventSequence(array($this->callA, $this->callC)),
            $this->subject->calledOn($this->thisValueA)
        );
        $this->assertEquals(new EventSequence(array($this->callB)), $this->subject->calledOn($this->thisValueB));
        $this->assertEquals(
            new EventSequence(array($this->callA, $this->callB, $this->callC)),
            $this->subject->calledOn($this->matcherFactory->equalTo($this->thisValueA))
        );
    }

    public function testCalledOnFailure()
    {
        $this->subject->setCalls($this->calls);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->calledOn((object) array());
    }

    public function testCalledOnFailureWithNoCalls()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->calledOn((object) array());
    }

    public function testCalledOnFailureWithMatcher()
    {
        $this->subject->setCalls($this->calls);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->calledOn($this->matcherFactory->equalTo((object) array('property' => 'value')));
    }

    public function testCalledOnFailureWithMatcherWithNoCalls()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->calledOn($this->matcherFactory->equalTo((object) array('property' => 'value')));
    }

    public function testCheckAlwaysCalledOn()
    {
        $this->assertFalse((boolean) $this->subject->always()->checkCalledOn(null));
        $this->assertFalse((boolean) $this->subject->always()->checkCalledOn($this->thisValueA));
        $this->assertFalse((boolean) $this->subject->always()->checkCalledOn($this->thisValueB));
        $this->assertFalse(
            (boolean) $this->subject->always()->checkCalledOn($this->matcherFactory->equalTo($this->thisValueA))
        );
        $this->assertFalse((boolean) $this->subject->always()->checkCalledOn((object) array()));

        $this->subject->setCalls($this->calls);

        $this->assertFalse((boolean) $this->subject->always()->checkCalledOn(null));
        $this->assertFalse((boolean) $this->subject->always()->checkCalledOn($this->thisValueA));
        $this->assertFalse((boolean) $this->subject->always()->checkCalledOn($this->thisValueB));
        $this->assertFalse(
            (boolean) $this->subject->always()->checkCalledOn($this->matcherFactory->equalTo($this->thisValueA))
        );
        $this->assertFalse((boolean) $this->subject->always()->checkCalledOn((object) array()));

        $this->subject->setCalls(array($this->callC, $this->callC));

        $this->assertTrue((boolean) $this->subject->always()->checkCalledOn($this->thisValueA));
        $this->assertTrue(
            (boolean) $this->subject->always()->checkCalledOn($this->matcherFactory->equalTo($this->thisValueA))
        );
        $this->assertFalse((boolean) $this->subject->always()->checkCalledOn(null));
        $this->assertFalse((boolean) $this->subject->always()->checkCalledOn($this->thisValueB));
        $this->assertFalse((boolean) $this->subject->always()->checkCalledOn((object) array()));
    }

    public function testAlwaysCalledOn()
    {
        $this->subject->setCalls(array($this->callC, $this->callC));
        $expected = new EventSequence(array($this->callC, $this->callC));

        $this->assertEquals($expected, $this->subject->always()->calledOn($this->thisValueA));
        $this->assertEquals(
            $expected,
            $this->subject->always()->calledOn($this->matcherFactory->equalTo($this->thisValueA))
        );
    }

    public function testAlwaysCalledOnFailure()
    {
        $this->subject->setCalls($this->calls);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->always()->calledOn($this->thisValueA);
    }

    public function testAlwaysCalledOnFailureWithNoCalls()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->always()->calledOn($this->thisValueA);
    }

    public function testAlwaysCalledOnFailureWithMatcher()
    {
        $this->subject->setCalls($this->calls);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->always()->calledOn($this->matcherFactory->equalTo($this->thisValueA));
    }

    public function testAlwaysCalledOnFailureWithMatcherWithNoCalls()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->always()->calledOn($this->matcherFactory->equalTo($this->thisValueA));
    }

    public function testCheckResponded()
    {
        $this->assertFalse((boolean) $this->subject->checkResponded());
        $this->assertTrue((boolean) $this->subject->never()->checkResponded());

        $this->subject->addCall($this->callE);

        $this->assertFalse((boolean) $this->subject->checkResponded());
        $this->assertTrue((boolean) $this->subject->never()->checkResponded());

        $this->subject->setCalls($this->calls);

        $this->assertTrue((boolean) $this->subject->checkResponded());
        $this->assertFalse((boolean) $this->subject->never()->checkResponded());

        $this->subject->setCalls(array($this->iteratorCall));

        $this->assertTrue((boolean) $this->subject->checkResponded());
    }

    public function testResponded()
    {
        $this->assertEquals(new EventSequence(array()), $this->subject->never()->responded());

        $this->subject->setCalls($this->calls);

        $this->assertEquals(
            new EventSequence(
                array(
                    $this->callA->responseEvent(),
                    $this->callB->responseEvent(),
                    $this->callC->responseEvent(),
                    $this->callD->responseEvent(),
                )
            ),
            $this->subject->responded()
        );

        $this->subject->setCalls(array($this->iteratorCall));

        $this->assertEquals(
            new EventSequence(array($this->iteratorCall->responseEvent())),
            $this->subject->responded()
        );
    }

    public function testRespondedFailure()
    {
        $this->subject->setCalls(array($this->callE, $this->callE));
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->responded();
    }

    public function testRespondedFailureWithNoCalls()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->responded();
    }

    public function testCheckAlwaysResponded()
    {
        $this->assertFalse((boolean) $this->subject->always()->checkResponded());

        $this->subject->setCalls($this->calls);

        $this->assertFalse((boolean) $this->subject->always()->checkResponded());

        $this->subject->setCalls(array($this->callA, $this->callB));

        $this->assertTrue((boolean) $this->subject->always()->checkResponded());
    }

    public function testAlwaysResponded()
    {
        $this->subject->setCalls(array($this->callA, $this->callB));
        $expected = new EventSequence(array($this->callA->responseEvent(), $this->callB->responseEvent()));

        $this->assertEquals($expected, $this->subject->always()->responded());
    }

    public function testAlwaysRespondedFailure()
    {
        $this->subject->setCalls($this->calls);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->always()->responded();
    }

    public function testAlwaysRespondedFailureWithNoCalls()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->always()->responded();
    }

    public function testCheckCompleted()
    {
        $this->assertFalse((boolean) $this->subject->checkCompleted());
        $this->assertTrue((boolean) $this->subject->never()->checkCompleted());

        $this->subject->addCall($this->callE);

        $this->assertFalse((boolean) $this->subject->checkCompleted());
        $this->assertTrue((boolean) $this->subject->never()->checkCompleted());

        $this->subject->addCall($this->iteratorCallWithNoEnd);

        $this->assertFalse((boolean) $this->subject->checkCompleted());
        $this->assertTrue((boolean) $this->subject->never()->checkCompleted());

        $this->subject->setCalls($this->calls);

        $this->assertTrue((boolean) $this->subject->checkCompleted());
        $this->assertFalse((boolean) $this->subject->never()->checkCompleted());

        $this->subject->setCalls(array($this->iteratorCall));

        $this->assertTrue((boolean) $this->subject->checkCompleted());
    }

    public function testCompleted()
    {
        $this->assertEquals(new EventSequence(array()), $this->subject->never()->completed());

        $this->subject->setCalls($this->calls);

        $this->assertEquals(
            new EventSequence(
                array(
                    $this->callA->endEvent(),
                    $this->callB->endEvent(),
                    $this->callC->endEvent(),
                    $this->callD->endEvent(),
                )
            ),
            $this->subject->completed()
        );

        $this->subject->setCalls(array($this->iteratorCall));

        $this->assertEquals(new EventSequence(array($this->iteratorCall->endEvent())), $this->subject->completed());
    }

    public function testCompletedFailure()
    {
        $this->subject->setCalls(array($this->iteratorCallWithNoEnd, $this->iteratorCallWithNoEnd));
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->completed();
    }

    public function testCompletedFailureWithNoCalls()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->completed();
    }

    public function testCheckAlwaysCompleted()
    {
        $this->assertFalse((boolean) $this->subject->always()->checkCompleted());

        $this->subject->setCalls($this->calls);

        $this->assertFalse((boolean) $this->subject->always()->checkCompleted());

        $this->subject->setCalls(array($this->callA, $this->iteratorCall));

        $this->assertTrue((boolean) $this->subject->always()->checkCompleted());
    }

    public function testAlwaysCompleted()
    {
        $this->subject->setCalls(array($this->callA, $this->iteratorCall));
        $expected = new EventSequence(array($this->callA->endEvent(), $this->iteratorCall->endEvent()));

        $this->assertEquals($expected, $this->subject->always()->completed());
    }

    public function testAlwaysCompletedFailure()
    {
        $this->subject->setCalls($this->calls);
        $this->subject->addCall($this->iteratorCall);

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->always()->completed();
    }

    public function testAlwaysCompletedFailureWithNoCalls()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->always()->completed();
    }

    public function testCheckReturned()
    {
        $this->assertFalse((boolean) $this->subject->checkReturned());
        $this->assertFalse((boolean) $this->subject->checkReturned(null));
        $this->assertFalse((boolean) $this->subject->checkReturned($this->returnValueA));
        $this->assertFalse((boolean) $this->subject->checkReturned($this->returnValueB));
        $this->assertFalse(
            (boolean) $this->subject->checkReturned($this->matcherFactory->equalTo($this->returnValueA))
        );
        $this->assertFalse((boolean) $this->subject->checkReturned('z'));

        $this->subject->setCalls($this->calls);

        $this->assertTrue((boolean) $this->subject->checkReturned());
        $this->assertFalse((boolean) $this->subject->checkReturned(null));
        $this->assertTrue((boolean) $this->subject->checkReturned($this->returnValueA));
        $this->assertTrue((boolean) $this->subject->checkReturned($this->returnValueB));
        $this->assertTrue((boolean) $this->subject->checkReturned($this->matcherFactory->equalTo($this->returnValueA)));
        $this->assertFalse((boolean) $this->subject->checkReturned('z'));
    }

    public function testReturned()
    {
        $this->subject->setCalls($this->calls);

        $this->assertEquals(
            new EventSequence(array($this->callAResponse, $this->callBResponse)),
            $this->subject->returned()
        );
        $this->assertEquals(
            new EventSequence(array($this->callAResponse)),
            $this->subject->returned($this->returnValueA)
        );
        $this->assertEquals(
            new EventSequence(array($this->callBResponse)),
            $this->subject->returned($this->returnValueB)
        );
        $this->assertEquals(
            new EventSequence(array($this->callAResponse)),
            $this->subject->returned($this->matcherFactory->equalTo($this->returnValueA))
        );
    }

    public function testReturnedFailure()
    {
        $this->subject->setCalls($this->calls);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->returned('z');
    }

    public function testReturnedFailureWithoutMatcher()
    {
        $this->subject->setCalls(array($this->callC, $this->callD));
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->returned();
    }

    public function testReturnedFailureWithNoCalls()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->returned($this->returnValueA);
    }

    public function testCheckAlwaysReturned()
    {
        $this->assertFalse((boolean) $this->subject->always()->checkReturned());
        $this->assertFalse((boolean) $this->subject->always()->checkReturned(null));
        $this->assertFalse((boolean) $this->subject->always()->checkReturned($this->returnValueA));
        $this->assertFalse((boolean) $this->subject->always()->checkReturned($this->returnValueB));
        $this->assertFalse(
            (boolean) $this->subject->always()->checkReturned($this->matcherFactory->equalTo($this->returnValueA))
        );
        $this->assertFalse((boolean) $this->subject->always()->checkReturned('z'));

        $this->subject->setCalls($this->calls);

        $this->assertFalse((boolean) $this->subject->always()->checkReturned());
        $this->assertFalse((boolean) $this->subject->always()->checkReturned(null));
        $this->assertFalse((boolean) $this->subject->always()->checkReturned($this->returnValueA));
        $this->assertFalse((boolean) $this->subject->always()->checkReturned($this->returnValueB));
        $this->assertFalse(
            (boolean) $this->subject->always()->checkReturned($this->matcherFactory->equalTo($this->returnValueA))
        );
        $this->assertFalse((boolean) $this->subject->always()->checkReturned('z'));

        $this->subject->setCalls(array($this->callA, $this->callA));

        $this->assertTrue((boolean) $this->subject->always()->checkReturned());
        $this->assertTrue((boolean) $this->subject->always()->checkReturned($this->returnValueA));
        $this->assertTrue(
            (boolean) $this->subject->always()->checkReturned($this->matcherFactory->equalTo($this->returnValueA))
        );
        $this->assertFalse((boolean) $this->subject->always()->checkReturned(null));
        $this->assertFalse((boolean) $this->subject->always()->checkReturned($this->returnValueB));
        $this->assertFalse((boolean) $this->subject->always()->checkReturned('y'));
    }

    public function testAlwaysReturned()
    {
        $this->subject->setCalls(array($this->callA, $this->callA));
        $expected = new EventSequence(array($this->callAResponse, $this->callAResponse));

        $this->assertEquals($expected, $this->subject->always()->returned());
        $this->assertEquals($expected, $this->subject->always()->returned($this->returnValueA));
        $this->assertEquals(
            $expected,
            $this->subject->always()->returned($this->matcherFactory->equalTo($this->returnValueA))
        );
    }

    public function testAlwaysReturnedFailure()
    {
        $this->subject->setCalls($this->calls);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->always()->returned($this->returnValueA);
    }

    public function testAlwaysReturnedFailureWithNoMatcher()
    {
        $this->subject->setCalls($this->calls);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->always()->returned();
    }

    public function testAlwaysReturnedFailureWithNoCalls()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->always()->returned($this->returnValueA);
    }

    public function testCheckThrew()
    {
        $this->assertFalse((boolean) $this->subject->checkThrew());
        $this->assertFalse((boolean) $this->subject->checkThrew('Exception'));
        $this->assertFalse((boolean) $this->subject->checkThrew('RuntimeException'));
        $this->assertFalse((boolean) $this->subject->checkThrew($this->exceptionA));
        $this->assertFalse((boolean) $this->subject->checkThrew($this->exceptionB));
        $this->assertFalse((boolean) $this->subject->checkThrew($this->matcherFactory->equalTo($this->exceptionA)));
        $this->assertFalse((boolean) $this->subject->checkThrew('InvalidArgumentException'));
        $this->assertFalse((boolean) $this->subject->checkThrew(new Exception()));
        $this->assertFalse((boolean) $this->subject->checkThrew(new RuntimeException()));
        $this->assertFalse((boolean) $this->subject->checkThrew($this->matcherFactory->equalTo(null)));

        $this->subject->setCalls($this->calls);

        $this->assertTrue((boolean) $this->subject->checkThrew());
        $this->assertTrue((boolean) $this->subject->checkThrew('Exception'));
        $this->assertTrue((boolean) $this->subject->checkThrew('RuntimeException'));
        $this->assertTrue((boolean) $this->subject->checkThrew($this->exceptionA));
        $this->assertTrue((boolean) $this->subject->checkThrew($this->exceptionB));
        $this->assertTrue((boolean) $this->subject->checkThrew($this->matcherFactory->equalTo($this->exceptionA)));
        $this->assertFalse((boolean) $this->subject->checkThrew('InvalidArgumentException'));
        $this->assertFalse((boolean) $this->subject->checkThrew(new Exception()));
        $this->assertFalse((boolean) $this->subject->checkThrew(new RuntimeException()));
        $this->assertFalse((boolean) $this->subject->checkThrew($this->matcherFactory->equalTo(null)));
    }

    public function testCheckThrewFailureInvalidInput()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Unable to match exceptions against 111.'
        );
        $this->subject->checkThrew(111);
    }

    public function testCheckThrewFailureInvalidInputObject()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Unable to match exceptions against #0{}.'
        );
        $this->subject->checkThrew((object) array());
    }

    public function testThrew()
    {
        $this->subject->setCalls($this->calls);

        $this->assertEquals(
            new EventSequence(array($this->callCResponse, $this->callDResponse)),
            $this->subject->threw()
        );
        $this->assertEquals(
            new EventSequence(array($this->callCResponse, $this->callDResponse)),
            $this->subject->threw('Exception')
        );
        $this->assertEquals(
            new EventSequence(array($this->callCResponse, $this->callDResponse)),
            $this->subject->threw('RuntimeException')
        );
        $this->assertEquals(
            new EventSequence(array($this->callCResponse)),
            $this->subject->threw($this->exceptionA)
        );
        $this->assertEquals(
            new EventSequence(array($this->callDResponse)),
            $this->subject->threw($this->exceptionB)
        );
        $this->assertEquals(
            new EventSequence(array($this->callCResponse)),
            $this->subject->threw($this->matcherFactory->equalTo($this->exceptionA))
        );
    }

    public function testThrewWithEngineErrorException()
    {
        if (!$this->featureDetector->isSupported('error.exception.engine')) {
            $this->markTestSkipped('Requires engine error exceptions.');
        }

        $this->exceptionA = new Error('You done goofed.');
        $this->exceptionB = new Error('Consequences will never be the same.');
        $this->callC = $this->callFactory->create(
            $this->callEventFactory->createCalled(array($this->thisValueA, 'testClassAMethodA'), $this->arguments),
            ($responseEvent = $this->callEventFactory->createThrew($this->exceptionA)),
            null,
            $responseEvent
        );
        $this->callCResponse = $this->callC->responseEvent();
        $this->callD = $this->callFactory->create(
            $this->callEventFactory->createCalled('implode'),
            ($responseEvent = $this->callEventFactory->createThrew($this->exceptionB)),
            null,
            $responseEvent
        );
        $this->callDResponse = $this->callD->responseEvent();
        $this->calls = array($this->callA, $this->callB, $this->callC, $this->callD);
        $this->wrappedCallC = $this->callVerifierFactory->fromCall($this->callC);
        $this->wrappedCallD = $this->callVerifierFactory->fromCall($this->callD);
        $this->wrappedCalls = array($this->wrappedCallA, $this->wrappedCallB, $this->wrappedCallC, $this->wrappedCallD);
        $this->subject->setCalls($this->calls);

        $this->assertEquals(
            new EventSequence(array($this->callCResponse, $this->callDResponse)),
            $this->subject->threw()
        );
    }

    public function testThrewFailureExpectingAny()
    {
        $this->subject->setCalls(array($this->callA, $this->callB));
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->threw();
    }

    public function testThrewFailureExpectingAnyWithNoCalls()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->threw();
    }

    public function testThrewFailureExpectingType()
    {
        $this->subject->setCalls(array($this->callC, $this->callD));
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->threw('Eloquent\Phony\Call\Exception\UndefinedCallException');
    }

    public function testThrewFailureExpectingTypeWithNoCalls()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->threw('Eloquent\Phony\Call\Exception\UndefinedCallException');
    }

    public function testThrewFailureExpectingTypeWithNoExceptions()
    {
        $this->subject->setCalls(array($this->callA, $this->callB));
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->threw('Eloquent\Phony\Call\Exception\UndefinedCallException');
    }

    public function testThrewFailureExpectingException()
    {
        $this->subject->setCalls(array($this->callC, $this->callD));
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->threw(new RuntimeException());
    }

    public function testThrewFailureExpectingExceptionWithNoCalls()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->threw(new RuntimeException());
    }

    public function testThrewFailureExpectingExceptionWithNoExceptions()
    {
        $this->subject->setCalls(array($this->callA, $this->callB));
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->threw(new RuntimeException());
    }

    public function testThrewFailureExpectingMatcher()
    {
        $this->subject->setCalls(array($this->callC, $this->callD));
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->threw($this->matcherFactory->equalTo(new RuntimeException()));
    }

    public function testThrewFailureExpectingMatcherWithNoCalls()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->threw($this->matcherFactory->equalTo(new RuntimeException()));
    }

    public function testThrewFailureExpectingMatcherWithNoExceptions()
    {
        $this->subject->setCalls(array($this->callA, $this->callB));
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->threw($this->matcherFactory->equalTo(new RuntimeException()));
    }

    public function testThrewFailureInvalidInput()
    {
        $this->setExpectedException('InvalidArgumentException', 'Unable to match exceptions against 111.');
        $this->subject->threw(111);
    }

    public function testThrewFailureInvalidInputObject()
    {
        $this->setExpectedException('InvalidArgumentException', 'Unable to match exceptions against #0{}.');
        $this->subject->threw((object) array());
    }

    public function testCheckAlwaysThrew()
    {
        $this->assertFalse((boolean) $this->subject->always()->checkThrew());
        $this->assertFalse((boolean) $this->subject->always()->checkThrew('Exception'));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew('RuntimeException'));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew($this->exceptionA));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew($this->exceptionB));
        $this->assertFalse(
            (boolean) $this->subject->always()->checkThrew($this->matcherFactory->equalTo($this->exceptionA))
        );
        $this->assertFalse((boolean) $this->subject->always()->checkThrew('InvalidArgumentException'));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew(new Exception()));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew(new RuntimeException()));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew($this->matcherFactory->equalTo(null)));

        $this->subject->setCalls($this->calls);

        $this->assertFalse((boolean) $this->subject->always()->checkThrew());
        $this->assertFalse((boolean) $this->subject->always()->checkThrew('Exception'));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew('RuntimeException'));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew($this->exceptionA));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew($this->exceptionB));
        $this->assertFalse(
            (boolean) $this->subject->always()->checkThrew($this->matcherFactory->equalTo($this->exceptionA))
        );
        $this->assertFalse((boolean) $this->subject->always()->checkThrew('InvalidArgumentException'));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew(new Exception()));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew(new RuntimeException()));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew($this->matcherFactory->equalTo(null)));

        $this->subject->setCalls(array($this->callC, $this->callC));

        $this->assertTrue((boolean) $this->subject->always()->checkThrew());
        $this->assertTrue((boolean) $this->subject->always()->checkThrew('Exception'));
        $this->assertTrue((boolean) $this->subject->always()->checkThrew('RuntimeException'));
        $this->assertTrue((boolean) $this->subject->always()->checkThrew($this->exceptionA));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew($this->exceptionB));
        $this->assertTrue(
            (boolean) $this->subject->always()->checkThrew($this->matcherFactory->equalTo($this->exceptionA))
        );
        $this->assertFalse((boolean) $this->subject->always()->checkThrew('InvalidArgumentException'));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew(new Exception()));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew(new RuntimeException()));
        $this->assertFalse((boolean) $this->subject->always()->checkThrew($this->matcherFactory->equalTo(null)));

        $this->subject->setCalls(array($this->callA, $this->callA));

        $this->assertFalse((boolean) $this->subject->always()->checkThrew($this->matcherFactory->equalTo(null)));
    }

    public function testAlwaysThrew()
    {
        $this->subject->setCalls(array($this->callC, $this->callC));
        $expected = new EventSequence(array($this->callCResponse, $this->callCResponse));

        $this->assertEquals($expected, $this->subject->always()->threw());
        $this->assertEquals($expected, $this->subject->always()->threw('Exception'));
        $this->assertEquals($expected, $this->subject->always()->threw('RuntimeException'));
        $this->assertEquals($expected, $this->subject->always()->threw($this->exceptionA));
        $this->assertEquals(
            $expected,
            $this->subject->always()->threw($this->matcherFactory->equalTo($this->exceptionA))
        );
    }

    public function testAlwaysThrewFailureExpectingAny()
    {
        $this->subject->setCalls($this->calls);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->always()->threw();
    }

    public function testAlwaysThrewFailureExpectingAnyButNothingThrown()
    {
        $this->subject->setCalls(array($this->callA, $this->callB));
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->always()->threw();
    }

    public function testAlwaysThrewFailureExpectingAnyWithNoCalls()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->always()->threw();
    }

    public function testAlwaysThrewFailureExpectingType()
    {
        $this->subject->setCalls(array($this->callC, $this->callD));
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->always()->threw('Eloquent\Phony\Call\Exception\UndefinedCallException');
    }

    public function testAlwaysThrewFailureExpectingTypeWithNoCalls()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->always()->threw('Eloquent\Phony\Call\Exception\UndefinedCallException');
    }

    public function testAlwaysThrewFailureExpectingTypeWithNoExceptions()
    {
        $this->subject->setCalls(array($this->callA, $this->callB));
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->always()->threw('Eloquent\Phony\Call\Exception\UndefinedCallException');
    }

    public function testAlwaysThrewFailureExpectingException()
    {
        $this->subject->setCalls(array($this->callC, $this->callD));
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->always()->threw(new RuntimeException());
    }

    public function testAlwaysThrewFailureExpectingExceptionWithNoCalls()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->always()->threw(new RuntimeException());
    }

    public function testAlwaysThrewFailureExpectingExceptionWithNoExceptions()
    {
        $this->subject->setCalls(array($this->callA, $this->callB));
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->always()->threw(new RuntimeException());
    }

    public function testAlwaysThrewFailureExpectingMatcher()
    {
        $this->subject->setCalls(array($this->callC, $this->callD));
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->always()->threw($this->matcherFactory->equalTo(new RuntimeException()));
    }

    public function testAlwaysThrewFailureExpectingMatcherWithNoCalls()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->always()->threw($this->matcherFactory->equalTo(new RuntimeException()));
    }

    public function testAlwaysThrewFailureExpectingMatcherWithNoExceptions()
    {
        $this->subject->setCalls(array($this->callA, $this->callB));
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->always()->threw($this->matcherFactory->equalTo(new RuntimeException()));
    }

    public function testCheckTraversed()
    {
        $this->assertFalse((boolean) $this->subject->checkTraversed());
        $this->assertTrue((boolean) $this->subject->never()->checkTraversed());

        $this->subject->setCalls($this->calls);

        $this->assertFalse((boolean) $this->subject->checkTraversed());
        $this->assertTrue((boolean) $this->subject->never()->checkTraversed());

        $this->subject->addCall($this->iteratorCall);

        $this->assertTrue((boolean) $this->subject->checkTraversed());
        $this->assertTrue((boolean) $this->subject->once()->checkTraversed());
    }

    public function testTraversed()
    {
        $this->assertEquals(
            $this->traversableVerifierFactory->create($this->spy, array()),
            $this->subject->never()->traversed()
        );

        $this->subject->addCall($this->iteratorCall);

        $this->assertEquals(
            $this->traversableVerifierFactory->create($this->spy, array($this->iteratorCall)),
            $this->subject->traversed()
        );
    }

    public function testTraversedFailure()
    {
        $this->subject->setCalls($this->calls);
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->traversed();
    }

    public function testTraversedFailureWithNoCalls()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->traversed();
    }

    public function testCheckAlwaysTraversed()
    {
        $this->assertFalse((boolean) $this->subject->always()->checkTraversed());

        $this->subject->setCalls($this->calls);

        $this->assertFalse((boolean) $this->subject->always()->checkTraversed());

        $this->subject->setCalls(array($this->iteratorCall, $this->iteratorCall));

        $this->assertTrue((boolean) $this->subject->always()->checkTraversed());
    }

    public function testAlwaysTraversed()
    {
        $this->subject->setCalls(array($this->iteratorCall, $this->iteratorCall));
        $expected =
            $this->traversableVerifierFactory->create($this->spy, array($this->iteratorCall, $this->iteratorCall));

        $this->assertEquals($expected, $this->subject->always()->traversed());
    }

    public function testAlwaysTraversedFailure()
    {
        $this->subject->setCalls($this->calls);

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->always()->traversed();
    }

    public function testAlwaysTraversedFailureWithNoCalls()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->always()->traversed();
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
