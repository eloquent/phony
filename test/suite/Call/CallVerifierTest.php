<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call;

use Eloquent\Phony\Assertion\AssertionRenderer;
use Eloquent\Phony\Assertion\ExceptionAssertionRecorder;
use Eloquent\Phony\Call\Event\CalledEvent;
use Eloquent\Phony\Call\Event\ReturnedEvent;
use Eloquent\Phony\Call\Event\ThrewEvent;
use Eloquent\Phony\Event\EventSequence;
use Eloquent\Phony\Exporter\InlineExporter;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Matcher\MatcherFactory;
use Eloquent\Phony\Matcher\MatcherVerifier;
use Eloquent\Phony\Reflection\FeatureDetector;
use Eloquent\Phony\Test\TestCallFactory;
use Eloquent\Phony\Verification\Cardinality;
use Eloquent\Phony\Verification\GeneratorVerifierFactory;
use Eloquent\Phony\Verification\TraversableVerifierFactory;
use Error;
use Exception;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use RuntimeException;

class CallVerifierTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $exporterReflector = new ReflectionClass('Eloquent\Phony\Exporter\InlineExporter');
        $property = $exporterReflector->getProperty('incrementIds');
        $property->setAccessible(true);
        $property->setValue(InlineExporter::instance(), false);

        $this->callFactory = new TestCallFactory();
        $this->callEventFactory = $this->callFactory->eventFactory();
        $this->callEventFactory->sequencer()->set(111);
        $this->thisValue = (object) array();
        $this->callback = array($this->thisValue, 'implode');
        $this->arguments = new Arguments(array('a', 'b', 'c'));
        $this->returnValue = 'abc';
        $this->calledEvent = $this->callEventFactory->createCalled($this->callback, $this->arguments);
        $this->returnedEvent = $this->callEventFactory->createReturned($this->returnValue);
        $this->call = $this->callFactory->create($this->calledEvent, $this->returnedEvent, null, $this->returnedEvent);

        $this->matcherFactory = MatcherFactory::instance();
        $this->matcherVerifier = new MatcherVerifier();
        $this->generatorVerifierFactory = GeneratorVerifierFactory::instance();
        $this->traversableVerifierFactory = TraversableVerifierFactory::instance();
        $this->assertionRecorder = ExceptionAssertionRecorder::instance();
        $this->assertionRenderer = AssertionRenderer::instance();
        $this->invocableInspector = new InvocableInspector();
        $this->subject = new CallVerifier(
            $this->call,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->traversableVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );

        $this->callVerifierFactory = CallVerifierFactory::instance();
        $this->generatorVerifierFactory->setCallVerifierFactory($this->callVerifierFactory);
        $this->traversableVerifierFactory->setCallVerifierFactory($this->callVerifierFactory);

        $this->duration = $this->returnedEvent->time() - $this->calledEvent->time();
        $this->argumentCount = count($this->arguments);
        $this->matchers = $this->matcherFactory->adaptAll($this->arguments->all());
        $this->otherMatcher = $this->matcherFactory->adapt('d');
        $this->events = array($this->calledEvent, $this->returnedEvent);

        $this->exception = new RuntimeException('You done goofed.');
        $this->threwEvent = $this->callEventFactory->createThrew($this->exception);
        $this->callWithException =
            $this->callFactory->create($this->calledEvent, $this->threwEvent, null, $this->threwEvent);
        $this->subjectWithException = new CallVerifier(
            $this->callWithException,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->traversableVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );

        $this->calledEventWithNoArguments = $this->callEventFactory->createCalled($this->callback);
        $this->callWithNoArguments = $this->callFactory
            ->create($this->calledEventWithNoArguments, $this->returnedEvent, null, $this->returnedEvent);
        $this->subjectWithNoArguments = new CallVerifier(
            $this->callWithNoArguments,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->traversableVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );

        $this->callWithNoResponse = $this->callFactory->create($this->calledEvent);
        $this->subjectWithNoResponse = new CallVerifier(
            $this->callWithNoResponse,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->traversableVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );

        $this->callEventFactory->sequencer()->reset();
        $this->earlyCall = $this->callFactory->create();
        $this->callEventFactory->sequencer()->set(222);
        $this->lateCall = $this->callFactory->create();

        $this->assertionResult = new EventSequence(array($this->call));
        $this->returnedAssertionResult = new EventSequence(array($this->call->responseEvent()));
        $this->threwAssertionResult = new EventSequence(array($this->callWithException->responseEvent()));
        $this->emptyAssertionResult = new EventSequence(array());

        $this->returnedTraversableEvent =
            $this->callEventFactory->createReturned(array('m' => 'n', 'p' => 'q', 'r' => 's', 'u' => 'v'));
        $this->iteratorEventA = $this->callEventFactory->createProduced('m', 'n');
        $this->iteratorEventB = $this->callEventFactory->createProduced('p', 'q');
        $this->iteratorEventE = $this->callEventFactory->createProduced('r', 's');
        $this->iteratorEventG = $this->callEventFactory->createProduced('u', 'v');
        $this->iteratorEvents =
            array($this->iteratorEventA, $this->iteratorEventB, $this->iteratorEventE, $this->iteratorEventG);
        $this->traversableEndEvent = $this->callEventFactory->createConsumed();
        $this->traversableCall = $this->callFactory->create(
            $this->calledEvent,
            $this->returnedTraversableEvent,
            $this->iteratorEvents,
            $this->traversableEndEvent
        );
        $this->traversableCallEvents = array(
            $this->calledEvent,
            $this->returnedTraversableEvent,
            $this->iteratorEventA,
            $this->iteratorEventB,
            $this->traversableEndEvent,
        );
        $this->traversableSubject = new CallVerifier(
            $this->traversableCall,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->traversableVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );

        $this->featureDetector = new FeatureDetector();
    }

    protected function tearDown()
    {
        $exporterReflector = new ReflectionClass('Eloquent\Phony\Exporter\InlineExporter');
        $property = $exporterReflector->getProperty('incrementIds');
        $property->setAccessible(true);
        $property->setValue(InlineExporter::instance(), true);
    }

    public function testProxyMethods()
    {
        $this->assertSame($this->calledEvent, $this->subject->eventAt(0));
        $this->assertSame($this->call, $this->subject->firstCall());
        $this->assertSame($this->call, $this->subject->lastCall());
        $this->assertSame($this->call, $this->subject->callAt(0));
        $this->assertTrue($this->subject->hasCalls());
        $this->assertSame(2, $this->subject->eventCount());
        $this->assertSame(1, $this->subject->callCount());
        $this->assertSame(2, count($this->subject));
        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertSame($this->returnedEvent, $this->subject->responseEvent());
        $this->assertSame(array(), $this->subject->traversableEvents());
        $this->assertSame($this->returnedEvent, $this->subject->endEvent());
        $this->assertTrue($this->subject->hasEvents());
        $this->assertSame($this->calledEvent, $this->subject->firstEvent());
        $this->assertSame($this->returnedEvent, $this->subject->lastEvent());
        $this->assertSame($this->events, $this->subject->allEvents());
        $this->assertSame(array($this->call), $this->subject->allCalls());
        $this->assertTrue($this->subject->hasResponded());
        $this->assertFalse($this->subject->isTraversable());
        $this->assertFalse($this->subject->isGenerator());
        $this->assertTrue($this->subject->hasCompleted());
        $this->assertSame($this->callback, $this->subject->callback());
        $this->assertSame($this->arguments, $this->subject->arguments());
        $this->assertSame('a', $this->subject->argument());
        $this->assertSame('a', $this->subject->argument(0));
        $this->assertSame('b', $this->subject->argument(1));
        $this->assertSame('c', $this->subject->argument(-1));
        $this->assertSame('b', $this->subject->argument(-2));
        $this->assertSame($this->returnValue, $this->subject->returnValue());
        $this->assertSame($this->exception, $this->subjectWithException->exception());
        $this->assertSame(array(null, $this->returnValue), $this->subject->response());
        $this->assertSame(array($this->exception, null), $this->subjectWithException->response());
        $this->assertSame($this->calledEvent->sequenceNumber(), $this->subject->sequenceNumber());
        $this->assertSame($this->calledEvent->time(), $this->subject->time());
        $this->assertSame($this->returnedEvent->time(), $this->subject->responseTime());
        $this->assertSame($this->returnedEvent->time(), $this->subject->endTime());
    }

    public function testIteration()
    {
        $this->assertSame(array($this->call), iterator_to_array($this->subject));
    }

    public function testAddTraversableEvent()
    {
        $returnedEvent = $this->callEventFactory->createReturned(array('a' => 'b', 'c' => 'd'));
        $traversableEventA = $this->callEventFactory->createProduced('a', 'b');
        $traversableEventB = $this->callEventFactory->createProduced('c', 'd');
        $traversableEvents = array($traversableEventA, $traversableEventB);
        $this->call = $this->callFactory->create($this->calledEvent, $returnedEvent);
        $this->subject = new CallVerifier(
            $this->call,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->traversableVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );
        $this->subject->addTraversableEvent($traversableEventA);
        $this->subject->addTraversableEvent($traversableEventB);

        $this->assertSame($traversableEvents, $this->subject->traversableEvents());
        $this->assertSame($this->call, $traversableEventA->call());
        $this->assertSame($this->call, $traversableEventB->call());
    }

    public function testSetResponseEvent()
    {
        $this->subjectWithNoResponse->setResponseEvent($this->returnedEvent);

        $this->assertSame($this->returnedEvent, $this->subjectWithNoResponse->responseEvent());
        $this->assertNull($this->subjectWithNoResponse->endEvent());
    }

    public function testSetEndEvent()
    {
        $this->subjectWithNoResponse->setEndEvent($this->returnedEvent);

        $this->assertSame($this->returnedEvent, $this->subjectWithNoResponse->endEvent());
        $this->assertSame($this->returnedEvent, $this->subjectWithNoResponse->responseEvent());
    }

    public function testDuration()
    {
        $this->assertEquals($this->duration, $this->subject->duration());
        $this->assertNull($this->subjectWithNoResponse->duration());
    }

    public function testResponseDuration()
    {
        $this->assertEquals($this->duration, $this->subject->responseDuration());
        $this->assertNull($this->subjectWithNoResponse->responseDuration());
    }

    public function testArgumentCount()
    {
        $this->assertSame($this->argumentCount, $this->subject->argumentCount());
    }

    public function calledWithData()
    {
        //                                    arguments                  calledWith calledWithWildcard
        return array(
            'Exact arguments'        => array(array('a', 'b', 'c'),      true,      true),
            'First arguments'        => array(array('a', 'b'),           false,     true),
            'Single argument'        => array(array('a'),                false,     true),
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
        $matchers = $this->matcherFactory->adaptAll($arguments);

        $this->assertSame(
            $calledWith,
            (boolean) call_user_func_array(array($this->subject, 'checkCalledWith'), $arguments)
        );
        $this->assertSame(
            $calledWith,
            (boolean) call_user_func_array(array($this->subject, 'checkCalledWith'), $matchers)
        );
        $this->assertSame(
            !$calledWith,
            (boolean) call_user_func_array(array($this->subject->never(), 'checkCalledWith'), $arguments)
        );
        $this->assertSame(
            !$calledWith,
            (boolean) call_user_func_array(array($this->subject->never(), 'checkCalledWith'), $matchers)
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
        $this->assertSame(
            !$calledWithWildcard,
            (boolean) call_user_func_array(array($this->subject->never(), 'checkCalledWith'), $arguments)
        );
        $this->assertSame(
            !$calledWithWildcard,
            (boolean) call_user_func_array(array($this->subject->never(), 'checkCalledWith'), $matchers)
        );
    }

    public function testCheckCalledWithWithWildcardOnly()
    {
        $this->assertTrue((boolean) $this->subject->checkCalledWith($this->matcherFactory->wildcard()));
    }

    public function testCalledWith()
    {
        $this->assertEquals($this->assertionResult, $this->subject->calledWith('a', 'b', 'c'));
        $this->assertEquals(
            $this->assertionResult,
            $this->subject->calledWith($this->matchers[0], $this->matchers[1], $this->matchers[2])
        );
        $this->assertEquals(
            $this->assertionResult,
            $this->subject->calledWith('a', 'b', $this->matcherFactory->wildcard())
        );
        $this->assertEquals(
            $this->assertionResult,
            $this->subject->calledWith($this->matchers[0], $this->matchers[1], $this->matcherFactory->wildcard())
        );
        $this->assertEquals($this->assertionResult, $this->subject->calledWith('a', $this->matcherFactory->wildcard()));
        $this->assertEquals(
            $this->assertionResult,
            $this->subject->calledWith($this->matchers[0], $this->matcherFactory->wildcard())
        );
        $this->assertEquals($this->assertionResult, $this->subject->calledWith($this->matcherFactory->wildcard()));

        $this->assertEquals($this->emptyAssertionResult, $this->subject->never()->calledWith('b', 'c'));
    }

    public function testCalledWithFailure()
    {
        $expected = <<<'EOD'
Expected call with arguments like:
    "b", "c"
Arguments:
    "a", "b", "c"
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->calledWith('b', 'c');
    }

    public function testCalledWithFailureNever()
    {
        $expected = <<<'EOD'
Expected no call with arguments like:
    "a", <any>*
Arguments:
    "a", "b", "c"
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->never()->calledWith('a', $this->matcherFactory->wildcard());
    }

    public function testCalledWithFailureWithNoArguments()
    {
        $expected = <<<'EOD'
Expected call with arguments like:
    "b", "c"
Arguments:
    <none>
EOD;
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subjectWithNoArguments->calledWith('b', 'c');
    }

    public function testCalledWithFailureInvalidCardinality()
    {
        $this->setExpectedException('Eloquent\Phony\Verification\Exception\InvalidSingularCardinalityException');
        $this->subject->times(2)->calledWith('a');
    }

    public function testCheckCalledOn()
    {
        $this->assertTrue((boolean) $this->subject->checkCalledOn($this->thisValue));
        $this->assertTrue((boolean) $this->subject->checkCalledOn($this->matcherFactory->equalTo($this->thisValue)));
        $this->assertTrue((boolean) $this->subject->never()->checkCalledOn((object) array('property' => 'value')));
        $this->assertFalse((boolean) $this->subject->checkCalledOn((object) array('property' => 'value')));
        $this->assertFalse((boolean) $this->subject->never()->checkCalledOn($this->thisValue));
    }

    public function testCalledOn()
    {
        $this->assertEquals($this->assertionResult, $this->subject->calledOn($this->thisValue));
        $this->assertEquals($this->assertionResult, $this->subject->calledOn($this->matcherFactory->equalTo($this->thisValue)));
        $this->assertEquals(
            $this->emptyAssertionResult,
            $this->subject->never()->calledOn((object) array('property' => 'value'))
        );
    }

    public function testCalledOnFailure()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Not called on supplied object. Object was #0{}.'
        );
        $this->subject->calledOn((object) array());
    }

    public function testCalledOnFailureNever()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Called on supplied object. Object was #0{}.'
        );
        $this->subject->never()->calledOn($this->thisValue);
    }

    public function testCalledOnFailureWithMatcher()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Not called on object like #0{property: "value"}. Object was #0{}.'
        );
        $this->subject->calledOn($this->matcherFactory->equalTo((object) array('property' => 'value')));
    }

    public function testCalledOnFailureWithMatcherNever()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Called on object like #0{}. Object was #0{}.'
        );
        $this->subject->never()->calledOn($this->matcherFactory->equalTo($this->thisValue));
    }

    public function testCheckReturned()
    {
        $this->assertTrue((boolean) $this->subject->checkReturned());
        $this->assertTrue((boolean) $this->subject->checkReturned($this->returnValue));
        $this->assertTrue((boolean) $this->subject->checkReturned($this->matcherFactory->adapt($this->returnValue)));
        $this->assertTrue((boolean) $this->subject->never()->checkReturned(null));
        $this->assertTrue((boolean) $this->subject->never()->checkReturned('y'));
        $this->assertTrue((boolean) $this->subject->never()->checkReturned($this->matcherFactory->adapt('y')));
        $this->assertTrue((boolean) $this->subjectWithException->never()->checkReturned());
        $this->assertFalse((boolean) $this->subject->never()->checkReturned());
        $this->assertFalse((boolean) $this->subject->checkReturned(null));
        $this->assertFalse((boolean) $this->subject->checkReturned('y'));
        $this->assertFalse((boolean) $this->subject->checkReturned($this->matcherFactory->adapt('y')));
        $this->assertFalse((boolean) $this->subjectWithException->checkReturned());
        $this->assertFalse((boolean) $this->subjectWithNoResponse->checkReturned());
    }

    public function testReturned()
    {
        $this->assertEquals($this->returnedAssertionResult, $this->subject->returned());
        $this->assertEquals($this->returnedAssertionResult, $this->subject->returned($this->returnValue));
        $this->assertEquals(
            $this->returnedAssertionResult,
            $this->subject->returned($this->matcherFactory->adapt($this->returnValue))
        );

        $this->assertEquals($this->emptyAssertionResult, $this->subject->never()->returned(null));
        $this->assertEquals($this->emptyAssertionResult, $this->subject->never()->returned('y'));
        $this->assertEquals(
            $this->emptyAssertionResult,
            $this->subject->never()->returned($this->matcherFactory->adapt('y'))
        );
    }

    public function testReturnedFailure()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected return like "x". Returned "abc".'
        );
        $this->subject->returned('x');
    }

    public function testReturnedFailureNever()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected no return like "abc". Returned "abc".'
        );
        $this->subject->never()->returned('abc');
    }

    public function testReturnedFailureNeverWithoutMatcher()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected no return. Returned "abc".'
        );
        $this->subject->never()->returned();
    }

    public function testReturnedFailureWithException()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected return like "x". Threw RuntimeException("You done goofed.").'
        );
        $this->subjectWithException->returned('x');
    }

    public function testReturnedFailureWithExceptionWithoutMatcher()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected return. Threw RuntimeException("You done goofed.").'
        );
        $this->subjectWithException->returned();
    }

    public function testReturnedFailureNeverResponded()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected return like "x". Never responded.'
        );
        $this->subjectWithNoResponse->returned('x');
    }

    public function testReturnedFailureNeverRespondedWithNoMatcher()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected return. Never responded.'
        );
        $this->subjectWithNoResponse->returned();
    }

    public function testCheckThrew()
    {
        $this->assertTrue((boolean) $this->subject->never()->checkThrew());
        $this->assertFalse((boolean) $this->subject->checkThrew());
        $this->assertFalse((boolean) $this->subject->checkThrew('Exception'));
        $this->assertFalse((boolean) $this->subject->checkThrew('RuntimeException'));
        $this->assertFalse((boolean) $this->subject->checkThrew($this->exception));
        $this->assertFalse((boolean) $this->subject->checkThrew($this->matcherFactory->equalTo($this->exception)));
        $this->assertFalse((boolean) $this->subject->checkThrew('InvalidArgumentException'));
        $this->assertFalse((boolean) $this->subject->checkThrew(new Exception()));
        $this->assertFalse((boolean) $this->subject->checkThrew(new RuntimeException()));
        $this->assertFalse((boolean) $this->subject->checkThrew($this->matcherFactory->equalTo(new RuntimeException())));
        $this->assertFalse((boolean) $this->subject->checkThrew($this->matcherFactory->equalTo(null)));

        $this->assertTrue((boolean) $this->subjectWithException->checkThrew());
        $this->assertTrue((boolean) $this->subjectWithException->checkThrew('Exception'));
        $this->assertTrue((boolean) $this->subjectWithException->checkThrew('RuntimeException'));
        $this->assertTrue((boolean) $this->subjectWithException->checkThrew($this->exception));
        $this->assertTrue((boolean) $this->subjectWithException->checkThrew($this->matcherFactory->equalTo($this->exception)));
        $this->assertFalse((boolean) $this->subjectWithException->checkThrew('InvalidArgumentException'));
        $this->assertFalse((boolean) $this->subjectWithException->checkThrew(new Exception()));
        $this->assertFalse((boolean) $this->subjectWithException->checkThrew(new RuntimeException()));
        $this->assertFalse(
            (boolean) $this->subjectWithException->checkThrew($this->matcherFactory->equalTo(new RuntimeException()))
        );
        $this->assertFalse((boolean) $this->subjectWithException->checkThrew($this->matcherFactory->equalTo(null)));
        $this->assertFalse((boolean) $this->subjectWithException->never()->checkThrew());
        $this->assertFalse((boolean) $this->subjectWithException->never()->checkThrew('Exception'));
        $this->assertFalse((boolean) $this->subjectWithException->never()->checkThrew('RuntimeException'));
        $this->assertFalse((boolean) $this->subjectWithException->never()->checkThrew($this->exception));
        $this->assertFalse(
            (boolean) $this->subjectWithException->never()->checkThrew($this->matcherFactory->equalTo($this->exception))
        );
    }

    public function testCheckThrewFailureInvalidInput()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Unable to match exceptions against 111.'
        );
        $this->subjectWithException->checkThrew(111);
    }

    public function testCheckThrewFailureInvalidInputObject()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Unable to match exceptions against #0{}.'
        );
        $this->subjectWithException->checkThrew((object) array());
    }

    public function testThrew()
    {
        $this->assertEquals($this->threwAssertionResult, $this->subjectWithException->threw());
        $this->assertEquals($this->threwAssertionResult, $this->subjectWithException->threw('Exception'));
        $this->assertEquals($this->threwAssertionResult, $this->subjectWithException->threw('RuntimeException'));
        $this->assertEquals($this->threwAssertionResult, $this->subjectWithException->threw($this->exception));
        $this->assertEquals(
            $this->threwAssertionResult,
            $this->subjectWithException->threw($this->matcherFactory->equalTo($this->exception))
        );

        $this->assertEquals($this->emptyAssertionResult, $this->subject->never()->threw());
    }

    public function testThrewWithEngineErrorException()
    {
        if (!$this->featureDetector->isSupported('error.exception.engine')) {
            $this->markTestSkipped('Requires engine error exceptions.');
        }

        $this->exception = new Error('You done goofed.');
        $this->threwEvent = $this->callEventFactory->createThrew($this->exception);
        $this->callWithException =
            $this->callFactory->create($this->calledEvent, $this->threwEvent, null, $this->threwEvent);
        $this->subjectWithException = new CallVerifier(
            $this->callWithException,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->traversableVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );
        $this->threwAssertionResult = new EventSequence(array($this->callWithException->responseEvent()));

        $this->assertEquals($this->threwAssertionResult, $this->subjectWithException->threw());
    }

    public function testThrewFailureExpectingAnyNoneThrown()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected exception. Returned "abc".'
        );
        $this->subject->threw();
    }

    public function testThrewFailureExpectingAnyNoResponse()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected exception. Never responded.'
        );
        $this->subjectWithNoResponse->threw();
    }

    public function testThrewFailureExpectingNeverAny()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected no exception. Threw RuntimeException("You done goofed.").'
        );
        $this->subjectWithException->never()->threw();
    }

    public function testThrewFailureTypeMismatch()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected InvalidArgumentException exception. Threw RuntimeException("You done goofed.").'
        );
        $this->subjectWithException->threw('InvalidArgumentException');
    }

    public function testThrewFailureTypeNever()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected no RuntimeException exception. Threw RuntimeException("You done goofed.").'
        );
        $this->subjectWithException->never()->threw('RuntimeException');
    }

    public function testThrewFailureExpectingTypeNoneThrown()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected InvalidArgumentException exception. Returned "abc".'
        );
        $this->subject->threw('InvalidArgumentException');
    }

    public function testThrewFailureExceptionMismatch()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected exception equal to RuntimeException(). Threw RuntimeException("You done goofed.").'
        );
        $this->subjectWithException->threw(new RuntimeException());
    }

    public function testThrewFailureExceptionNever()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected no exception equal to RuntimeException("You done goofed."). ' .
                'Threw RuntimeException("You done goofed.").'
        );
        $this->subjectWithException->never()->threw($this->exception);
    }

    public function testThrewFailureExpectingExceptionNoneThrown()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected exception equal to RuntimeException(). Returned "abc".'
        );
        $this->subject->threw(new RuntimeException());
    }

    public function testThrewFailureMatcherMismatch()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected exception like RuntimeException#0{}. ' .
                'Threw RuntimeException("You done goofed.").'
        );
        $this->subjectWithException->threw($this->matcherFactory->equalTo(new RuntimeException()));
    }

    public function testThrewFailureMatcherNever()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected no exception like RuntimeException#0{message: "You done goofed."}. ' .
                'Threw RuntimeException("You done goofed.").'
        );
        $this->subjectWithException->never()->threw($this->matcherFactory->equalTo($this->exception));
    }

    public function testThrewFailureInvalidInput()
    {
        $this->setExpectedException('InvalidArgumentException', 'Unable to match exceptions against 111.');
        $this->subjectWithException->threw(111);
    }

    public function testThrewFailureInvalidInputObject()
    {
        $this->setExpectedException('InvalidArgumentException', 'Unable to match exceptions against #0{}.');
        $this->subjectWithException->threw((object) array());
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

    public function testCheckTraversed()
    {
        $this->assertTrue((boolean) $this->traversableSubject->checkTraversed());
        $this->assertTrue((boolean) $this->traversableSubject->once()->checkTraversed());
        $this->assertFalse((boolean) $this->subject->checkTraversed());
        $this->assertTrue((boolean) $this->subject->never()->checkTraversed());
        $this->assertFalse((boolean) $this->subjectWithNoResponse->checkTraversed());
        $this->assertTrue((boolean) $this->subjectWithNoResponse->never()->checkTraversed());
    }

    public function testTraversed()
    {
        $this->assertEquals(
            $this->traversableVerifierFactory->create($this->traversableSubject, array($this->traversableCall)),
            $this->traversableSubject->traversed()
        );
        $this->assertEquals(
            $this->traversableVerifierFactory->create($this->subject, array()),
            $this->subject->never()->traversed()
        );
    }

    public function testTraversedFailure()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected traversable. Returned "abc".'
        );
        $this->subject->traversed();
    }

    public function testTraversedFailureNever()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected no traversable. Returned #0["m": "n", "p": "q", "r": "s", "u": "v"].'
        );
        $this->traversableSubject->never()->traversed();
    }

    public function testTraversedFailureWithException()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected traversable. Threw RuntimeException("You done goofed.").'
        );
        $this->subjectWithException->traversed();
    }

    public function testTraversedFailureNeverResponded()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected traversable. Never responded.'
        );
        $this->subjectWithNoResponse->traversed();
    }
}
