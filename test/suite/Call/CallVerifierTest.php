<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call;

use Eloquent\Phony\Assertion\AssertionRenderer;
use Eloquent\Phony\Assertion\Exception\AssertionException;
use Eloquent\Phony\Assertion\ExceptionAssertionRecorder;
use Eloquent\Phony\Call\Exception\UndefinedCallException;
use Eloquent\Phony\Difference\DifferenceEngine;
use Eloquent\Phony\Event\EventSequence;
use Eloquent\Phony\Exporter\InlineExporter;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Matcher\MatcherFactory;
use Eloquent\Phony\Matcher\MatcherVerifier;
use Eloquent\Phony\Mock\Builder\MockBuilderFactory;
use Eloquent\Phony\Phony;
use Eloquent\Phony\Reflection\FeatureDetector;
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Spy\GeneratorSpyMap;
use Eloquent\Phony\Test\TestCallFactory;
use Eloquent\Phony\Test\TestClassA;
use Eloquent\Phony\Test\WithDynamicProperties;
use Eloquent\Phony\Verification\Cardinality;
use Eloquent\Phony\Verification\Exception\InvalidSingularCardinalityException;
use Eloquent\Phony\Verification\GeneratorVerifierFactory;
use Eloquent\Phony\Verification\IterableVerifierFactory;
use Error;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class CallVerifierTest extends TestCase
{
    use WithDynamicProperties;

    protected function setUp(): void
    {
        $this->callFactory = new TestCallFactory();
        $this->callEventFactory = $this->callFactory->eventFactory();
        $this->callEventFactory->sequencer()->set(111);
        $this->thisValue = new TestClassA();
        $this->callback = [$this->thisValue, 'testClassAMethodA'];
        $this->arguments = new Arguments(['a', 'b', 'c']);
        $this->returnValue = 'abc';
        $this->calledEvent = $this->callEventFactory->createCalled($this->callback, $this->arguments);
        $this->returnedEvent = $this->callEventFactory->createReturned($this->returnValue);
        $this->call = $this->callFactory->create($this->calledEvent, $this->returnedEvent, null, $this->returnedEvent);

        $this->invocableInspector = new InvocableInspector();
        $this->matcherFactory = MatcherFactory::instance();
        $this->matcherVerifier = new MatcherVerifier();
        $this->arraySequencer = new Sequencer();
        $this->objectSequencer = new Sequencer();
        $this->featureDetector = FeatureDetector::instance();
        $this->generatorSpyMap = GeneratorSpyMap::instance();
        $this->exporter = new InlineExporter(
            1,
            $this->arraySequencer,
            $this->objectSequencer,
            $this->generatorSpyMap,
            $this->invocableInspector,
            $this->featureDetector
        );
        $this->generatorVerifierFactory = GeneratorVerifierFactory::instance();
        $this->iterableVerifierFactory = IterableVerifierFactory::instance();
        $this->assertionRecorder = ExceptionAssertionRecorder::instance();
        $this->differenceEngine = new DifferenceEngine($this->featureDetector);
        $this->differenceEngine->setUseColor(false);
        $this->assertionRenderer = new AssertionRenderer(
            $this->matcherVerifier,
            $this->exporter,
            $this->differenceEngine,
            $this->featureDetector
        );
        $this->assertionRenderer->setUseColor(false);
        $this->subject = new CallVerifier(
            $this->call,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->iterableVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer
        );

        $this->callVerifierFactory = CallVerifierFactory::instance();
        $this->assertionRecorder->setCallVerifierFactory($this->callVerifierFactory);
        $this->generatorVerifierFactory->setCallVerifierFactory($this->callVerifierFactory);
        $this->iterableVerifierFactory->setCallVerifierFactory($this->callVerifierFactory);

        $this->duration = $this->returnedEvent->time() - $this->calledEvent->time();
        $this->argumentCount = count($this->arguments);
        $this->matchers = $this->matcherFactory->adaptAll($this->arguments->all());
        $this->otherMatcher = $this->matcherFactory->adapt('d');
        $this->events = [$this->calledEvent, $this->returnedEvent];

        $this->callFactory->reset();
        $this->exception = new RuntimeException('You done goofed.');
        $this->threwEvent = $this->callEventFactory->createThrew($this->exception);
        $this->callWithException =
            $this->callFactory->create($this->calledEvent, $this->threwEvent, null, $this->threwEvent);
        $this->subjectWithException = new CallVerifier(
            $this->callWithException,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->iterableVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer
        );

        $this->callFactory->reset();
        $this->calledEventWithNoArguments = $this->callEventFactory->createCalled($this->callback);
        $this->callWithNoArguments = $this->callFactory
            ->create($this->calledEventWithNoArguments, $this->returnedEvent, null, $this->returnedEvent);
        $this->subjectWithNoArguments = new CallVerifier(
            $this->callWithNoArguments,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->iterableVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer
        );

        $this->callFactory->reset();
        $this->callWithNoResponse = $this->callFactory->create($this->calledEvent);
        $this->subjectWithNoResponse = new CallVerifier(
            $this->callWithNoResponse,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->iterableVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer
        );

        $this->callEventFactory->sequencer()->reset();
        $this->earlyCall = $this->callFactory->create();
        $this->callEventFactory->sequencer()->set(222);
        $this->lateCall = $this->callFactory->create();

        $this->assertionResult = new EventSequence([$this->call], $this->callVerifierFactory);
        $this->returnedAssertionResult =
            new EventSequence([$this->call->responseEvent()], $this->callVerifierFactory);
        $this->threwAssertionResult =
            new EventSequence([$this->callWithException->responseEvent()], $this->callVerifierFactory);
        $this->emptyAssertionResult = new EventSequence([], $this->callVerifierFactory);

        $this->returnedIterableEvent =
            $this->callEventFactory->createReturned(['m' => 'n', 'p' => 'q', 'r' => 's', 'u' => 'v']);
        $this->iteratorEventA = $this->callEventFactory->createProduced('m', 'n');
        $this->iteratorEventB = $this->callEventFactory->createProduced('p', 'q');
        $this->iteratorEventE = $this->callEventFactory->createProduced('r', 's');
        $this->iteratorEventG = $this->callEventFactory->createProduced('u', 'v');
        $this->iteratorEvents =
            [$this->iteratorEventA, $this->iteratorEventB, $this->iteratorEventE, $this->iteratorEventG];
        $this->iterableEndEvent = $this->callEventFactory->createConsumed();
        $this->iterableCall = $this->callFactory->create(
            $this->calledEvent,
            $this->returnedIterableEvent,
            $this->iteratorEvents,
            $this->iterableEndEvent
        );
        $this->iterableCallEvents = [
            $this->calledEvent,
            $this->returnedIterableEvent,
            $this->iteratorEventA,
            $this->iteratorEventB,
            $this->iterableEndEvent,
        ];
        $this->iterableSubject = new CallVerifier(
            $this->iterableCall,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->iterableVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer
        );

        $this->iterableCallWithNoEnd = $this->callFactory->create(
            $this->calledEvent,
            $this->returnedIterableEvent,
            $this->iteratorEvents
        );
        $this->iterableSubjectWithNoEnd = new CallVerifier(
            $this->iterableCallWithNoEnd,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->iterableVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer
        );

        $this->featureDetector = new FeatureDetector();
    }

    public function testProxyMethods()
    {
        $this->assertSame($this->calledEvent, $this->subject->eventAt(0));
        $this->assertTrue($this->subject->hasCalls());
        $this->assertSame(2, $this->subject->eventCount());
        $this->assertSame(1, $this->subject->callCount());
        $this->assertCount(2, $this->subject);
        $this->assertSame($this->calledEvent, $this->subject->calledEvent());
        $this->assertSame($this->returnedEvent, $this->subject->responseEvent());
        $this->assertSame([], $this->subject->iterableEvents());
        $this->assertSame($this->returnedEvent, $this->subject->endEvent());
        $this->assertTrue($this->subject->hasEvents());
        $this->assertSame($this->calledEvent, $this->subject->firstEvent());
        $this->assertSame($this->returnedEvent, $this->subject->lastEvent());
        $this->assertSame($this->events, $this->subject->allEvents());
        $this->assertSame([$this->call], $this->subject->allCalls());
        $this->assertTrue($this->subject->hasResponded());
        $this->assertFalse($this->subject->isIterable());
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
        $this->assertSame([null, $this->returnValue], $this->subject->response());
        $this->assertSame([$this->exception, null], $this->subjectWithException->response());
        $this->assertSame($this->call->index(), $this->subject->index());
        $this->assertSame($this->calledEvent->sequenceNumber(), $this->subject->sequenceNumber());
        $this->assertSame($this->calledEvent->time(), $this->subject->time());
        $this->assertSame($this->returnedEvent->time(), $this->subject->responseTime());
        $this->assertSame($this->returnedEvent->time(), $this->subject->endTime());
    }

    public function testFirstCall()
    {
        $this->assertSame($this->subject, $this->subject->firstCall());
    }

    public function testLastCall()
    {
        $this->assertSame($this->subject, $this->subject->lastCall());
    }

    public function testCallAt()
    {
        $this->assertSame($this->subject, $this->subject->callAt());
        $this->assertSame($this->subject, $this->subject->callAt(0));
        $this->assertSame($this->subject, $this->subject->callAt(-1));
    }

    public function testCallAtFailure()
    {
        $this->expectException(UndefinedCallException::class);
        $this->subject->callAt(1);
    }

    public function testIteration()
    {
        $this->assertSame([$this->call], iterator_to_array($this->subject));
    }

    public function testAddIterableEvent()
    {
        $returnedEvent = $this->callEventFactory->createReturned(['a' => 'b', 'c' => 'd']);
        $iterableEventA = $this->callEventFactory->createProduced('a', 'b');
        $iterableEventB = $this->callEventFactory->createProduced('c', 'd');
        $iterableEvents = [$iterableEventA, $iterableEventB];
        $this->call = $this->callFactory->create($this->calledEvent, $returnedEvent);
        $this->subject = new CallVerifier(
            $this->call,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->iterableVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer
        );
        $this->subject->addIterableEvent($iterableEventA);
        $this->subject->addIterableEvent($iterableEventB);

        $this->assertSame($iterableEvents, $this->subject->iterableEvents());
        $this->assertSame($this->call, $iterableEventA->call());
        $this->assertSame($this->call, $iterableEventB->call());
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
        return [
            'Exact arguments'        => [['a', 'b', 'c'],      true,      true],
            'First arguments'        => [['a', 'b'],           false,     true],
            'Single argument'        => [['a'],                false,     true],
            'Last arguments'         => [['b', 'c'],           false,     false],
            'Last argument'          => [['c'],                false,     false],
            'Extra arguments'        => [['a', 'b', 'c', 'd'], false,     false],
            'First argument differs' => [['d', 'b', 'c'],      false,     false],
            'Last argument differs'  => [['a', 'b', 'd'],      false,     false],
            'Unused argument'        => [['d'],                false,     false],
        ];
    }

    /**
     * @dataProvider calledWithData
     */
    public function testCheckCalledWith(array $arguments, $calledWith, $calledWithWildcard)
    {
        $matchers = $this->matcherFactory->adaptAll($arguments);

        $this->assertSame(
            $calledWith,
            (bool) call_user_func_array([$this->subject, 'checkCalledWith'], $arguments)
        );
        $this->assertSame(
            $calledWith,
            (bool) call_user_func_array([$this->subject, 'checkCalledWith'], $matchers)
        );
        $this->assertSame(
            !$calledWith,
            (bool) call_user_func_array([$this->subject->never(), 'checkCalledWith'], $arguments)
        );
        $this->assertSame(
            !$calledWith,
            (bool) call_user_func_array([$this->subject->never(), 'checkCalledWith'], $matchers)
        );

        $arguments[] = $this->matcherFactory->wildcard();
        $matchers[] = $this->matcherFactory->wildcard();

        $this->assertSame(
            $calledWithWildcard,
            (bool) call_user_func_array([$this->subject, 'checkCalledWith'], $arguments)
        );
        $this->assertSame(
            $calledWithWildcard,
            (bool) call_user_func_array([$this->subject, 'checkCalledWith'], $matchers)
        );
        $this->assertSame(
            !$calledWithWildcard,
            (bool) call_user_func_array([$this->subject->never(), 'checkCalledWith'], $arguments)
        );
        $this->assertSame(
            !$calledWithWildcard,
            (bool) call_user_func_array([$this->subject->never(), 'checkCalledWith'], $matchers)
        );
    }

    public function testCheckCalledWithWithWildcardOnly()
    {
        $this->assertTrue((bool) $this->subject->checkCalledWith($this->matcherFactory->wildcard()));
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
        $this->expectException(AssertionException::class);
        $this->subject->calledWith('b', 'c');
    }

    public function testCalledWithFailureNever()
    {
        $this->expectException(AssertionException::class);
        $this->subject->never()->calledWith('a', $this->matcherFactory->wildcard());
    }

    public function testCalledWithFailureWithNoArguments()
    {
        $this->expectException(AssertionException::class);
        $this->subjectWithNoArguments->calledWith('b', 'c');
    }

    public function testCalledWithFailureWithNoMatchers()
    {
        $this->expectException(AssertionException::class);
        $this->subject->calledWith();
    }

    public function testCalledWithFailureInvalidCardinality()
    {
        $this->expectException(InvalidSingularCardinalityException::class);
        $this->subject->times(2)->calledWith('a');
    }

    public function testCheckResponded()
    {
        $this->assertTrue((bool) $this->subject->checkResponded());
        $this->assertTrue((bool) $this->subjectWithException->checkResponded());
        $this->assertFalse((bool) $this->subject->never()->checkResponded());
        $this->assertFalse((bool) $this->subjectWithNoResponse->checkResponded());
        $this->assertTrue((bool) $this->subjectWithNoResponse->never()->checkResponded());
    }

    public function testResponded()
    {
        $this->assertEquals($this->returnedAssertionResult, $this->subject->responded());
        $this->assertEquals($this->threwAssertionResult, $this->subjectWithException->responded());
        $this->assertEquals($this->emptyAssertionResult, $this->subjectWithNoResponse->never()->responded());
    }

    public function testRespondedFailure()
    {
        $this->expectException(AssertionException::class);
        $this->subjectWithNoResponse->responded();
    }

    public function testRespondedFailureNever()
    {
        $this->expectException(AssertionException::class);
        $this->subject->never()->responded();
    }

    public function testCheckCompleted()
    {
        $this->assertTrue((bool) $this->subject->checkCompleted());
        $this->assertTrue((bool) $this->subjectWithException->checkCompleted());
        $this->assertTrue((bool) $this->iterableSubject->checkCompleted());
        $this->assertFalse((bool) $this->subject->never()->checkCompleted());
        $this->assertFalse((bool) $this->subjectWithNoResponse->checkCompleted());
        $this->assertTrue((bool) $this->subjectWithNoResponse->never()->checkCompleted());
        $this->assertFalse((bool) $this->iterableSubjectWithNoEnd->checkCompleted());
        $this->assertTrue((bool) $this->iterableSubjectWithNoEnd->never()->checkCompleted());
    }

    public function testCompleted()
    {
        $this->assertEquals($this->returnedAssertionResult, $this->subject->completed());
        $this->assertEquals($this->threwAssertionResult, $this->subjectWithException->completed());
        $this->assertEquals(
            new EventSequence([$this->iterableEndEvent], $this->callVerifierFactory),
            $this->iterableSubject->completed()
        );
        $this->assertEquals($this->emptyAssertionResult, $this->subjectWithNoResponse->never()->completed());
    }

    public function testCompletedFailure()
    {
        $this->expectException(AssertionException::class);
        $this->iterableSubjectWithNoEnd->completed();
    }

    public function testCompletedFailureNever()
    {
        $this->expectException(AssertionException::class);
        $this->iterableSubject->never()->completed();
    }

    public function testCheckReturned()
    {
        $this->assertTrue((bool) $this->subject->checkReturned());
        $this->assertTrue((bool) $this->subject->checkReturned($this->returnValue));
        $this->assertTrue((bool) $this->subject->checkReturned($this->matcherFactory->adapt($this->returnValue)));
        $this->assertTrue((bool) $this->subject->never()->checkReturned(null));
        $this->assertTrue((bool) $this->subject->never()->checkReturned('y'));
        $this->assertTrue((bool) $this->subject->never()->checkReturned($this->matcherFactory->adapt('y')));
        $this->assertTrue((bool) $this->subjectWithException->never()->checkReturned());
        $this->assertFalse((bool) $this->subject->never()->checkReturned());
        $this->assertFalse((bool) $this->subject->checkReturned(null));
        $this->assertFalse((bool) $this->subject->checkReturned('y'));
        $this->assertFalse((bool) $this->subject->checkReturned($this->matcherFactory->adapt('y')));
        $this->assertFalse((bool) $this->subjectWithException->checkReturned());
        $this->assertFalse((bool) $this->subjectWithNoResponse->checkReturned());
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
        $this->expectException(AssertionException::class);
        $this->subject->returned('x');
    }

    public function testReturnedFailureNever()
    {
        $this->expectException(AssertionException::class);
        $this->subject->never()->returned('abc');
    }

    public function testReturnedFailureNeverWithoutMatcher()
    {
        $this->expectException(AssertionException::class);
        $this->subject->never()->returned();
    }

    public function testReturnedFailureWithException()
    {
        $this->expectException(AssertionException::class);
        $this->subjectWithException->returned('x');
    }

    public function testReturnedFailureWithExceptionWithoutMatcher()
    {
        $this->expectException(AssertionException::class);
        $this->subjectWithException->returned();
    }

    public function testReturnedFailureNeverResponded()
    {
        $this->expectException(AssertionException::class);
        $this->subjectWithNoResponse->returned('x');
    }

    public function testReturnedFailureNeverRespondedWithNoMatcher()
    {
        $this->expectException(AssertionException::class);
        $this->subjectWithNoResponse->returned();
    }

    public function testCheckThrew()
    {
        $this->assertTrue((bool) $this->subject->never()->checkThrew());
        $this->assertFalse((bool) $this->subject->checkThrew());
        $this->assertFalse((bool) $this->subject->checkThrew(Exception::class));
        $this->assertFalse((bool) $this->subject->checkThrew(RuntimeException::class));
        $this->assertFalse((bool) $this->subject->checkThrew($this->exception));
        $this->assertFalse((bool) $this->subject->checkThrew($this->matcherFactory->equalTo($this->exception)));
        $this->assertFalse((bool) $this->subject->checkThrew(InvalidArgumentException::class));
        $this->assertFalse((bool) $this->subject->checkThrew(new Exception()));
        $this->assertFalse((bool) $this->subject->checkThrew(new RuntimeException()));
        $this->assertFalse(
            (bool) $this->subject->checkThrew($this->matcherFactory->equalTo(new RuntimeException()))
        );
        $this->assertFalse((bool) $this->subject->checkThrew($this->matcherFactory->equalTo(null)));

        $this->assertTrue((bool) $this->subjectWithException->checkThrew());
        $this->assertTrue((bool) $this->subjectWithException->checkThrew(Exception::class));
        $this->assertTrue((bool) $this->subjectWithException->checkThrew(RuntimeException::class));
        $this->assertTrue((bool) $this->subjectWithException->checkThrew($this->exception));
        $this->assertTrue(
            (bool) $this->subjectWithException->checkThrew($this->matcherFactory->equalTo($this->exception))
        );
        $this->assertFalse((bool) $this->subjectWithException->checkThrew(InvalidArgumentException::class));
        $this->assertFalse((bool) $this->subjectWithException->checkThrew(new Exception()));
        $this->assertFalse((bool) $this->subjectWithException->checkThrew(new RuntimeException()));
        $this->assertFalse(
            (bool) $this->subjectWithException->checkThrew($this->matcherFactory->equalTo(new RuntimeException()))
        );
        $this->assertFalse((bool) $this->subjectWithException->checkThrew($this->matcherFactory->equalTo(null)));
        $this->assertFalse((bool) $this->subjectWithException->never()->checkThrew());
        $this->assertFalse((bool) $this->subjectWithException->never()->checkThrew(Exception::class));
        $this->assertFalse((bool) $this->subjectWithException->never()->checkThrew(RuntimeException::class));
        $this->assertFalse((bool) $this->subjectWithException->never()->checkThrew($this->exception));
        $this->assertFalse(
            (bool) $this->subjectWithException->never()->checkThrew($this->matcherFactory->equalTo($this->exception))
        );
    }

    public function testCheckThrewFailureInvalidInput()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to match exceptions against 111.');
        $this->subjectWithException->checkThrew(111);
    }

    public function testCheckThrewFailureInvalidInputObject()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to match exceptions against #0{}.');
        $this->subjectWithException->checkThrew((object) []);
    }

    public function testThrew()
    {
        $this->assertEquals($this->threwAssertionResult, $this->subjectWithException->threw());
        $this->assertEquals($this->threwAssertionResult, $this->subjectWithException->threw(Exception::class));
        $this->assertEquals($this->threwAssertionResult, $this->subjectWithException->threw(RuntimeException::class));
        $this->assertEquals($this->threwAssertionResult, $this->subjectWithException->threw($this->exception));
        $this->assertEquals(
            $this->threwAssertionResult,
            $this->subjectWithException->threw($this->matcherFactory->equalTo($this->exception))
        );

        $this->assertEquals($this->emptyAssertionResult, $this->subject->never()->threw());
    }

    public function testThrewWithEngineErrorException()
    {
        $this->exception = new Error('You done goofed.');
        $this->threwEvent = $this->callEventFactory->createThrew($this->exception);
        $this->callWithException =
            $this->callFactory->create($this->calledEvent, $this->threwEvent, null, $this->threwEvent);
        $this->subjectWithException = new CallVerifier(
            $this->callWithException,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->iterableVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer
        );
        $this->threwAssertionResult =
            new EventSequence([$this->callWithException->responseEvent()], $this->callVerifierFactory);

        $this->assertEquals($this->threwAssertionResult, $this->subjectWithException->threw());
    }

    public function testThrewWithInstanceHandle()
    {
        $builder = MockBuilderFactory::instance()->create(RuntimeException::class);
        $exception = $builder->get();
        $threwEvent = $this->callEventFactory->createThrew($exception);
        $call = $this->callFactory->create($this->calledEvent, $threwEvent, null, $threwEvent);
        $subject = new CallVerifier(
            $call,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->iterableVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer
        );
        $handle = Phony::on($exception);

        $this->assertTrue((bool) $subject->threw($handle));
        $this->assertTrue((bool) $subject->checkThrew($handle));
    }

    public function testThrewFailureExpectingAnyNoneThrown()
    {
        $this->expectException(AssertionException::class);
        $this->subject->threw();
    }

    public function testThrewFailureExpectingAnyNoResponse()
    {
        $this->expectException(AssertionException::class);
        $this->subjectWithNoResponse->threw();
    }

    public function testThrewFailureExpectingNeverAny()
    {
        $this->expectException(AssertionException::class);
        $this->subjectWithException->never()->threw();
    }

    public function testThrewFailureTypeMismatch()
    {
        $this->expectException(AssertionException::class);
        $this->subjectWithException->threw(InvalidArgumentException::class);
    }

    public function testThrewFailureTypeNever()
    {
        $this->expectException(AssertionException::class);
        $this->subjectWithException->never()->threw(RuntimeException::class);
    }

    public function testThrewFailureExpectingTypeNoneThrown()
    {
        $this->expectException(AssertionException::class);
        $this->subject->threw(InvalidArgumentException::class);
    }

    public function testThrewFailureExceptionMismatch()
    {
        $this->expectException(AssertionException::class);
        $this->subjectWithException->threw(new RuntimeException());
    }

    public function testThrewFailureExceptionNever()
    {
        $this->expectException(AssertionException::class);
        $this->subjectWithException->never()->threw($this->exception);
    }

    public function testThrewFailureExpectingExceptionNoneThrown()
    {
        $this->expectException(AssertionException::class);
        $this->subject->threw(new RuntimeException());
    }

    public function testThrewFailureMatcherMismatch()
    {
        $this->expectException(AssertionException::class);
        $this->subjectWithException->threw($this->matcherFactory->equalTo(new RuntimeException()));
    }

    public function testThrewFailureMatcherNever()
    {
        $this->expectException(AssertionException::class);
        $this->subjectWithException->never()->threw($this->matcherFactory->equalTo($this->exception));
    }

    public function testThrewFailureInvalidInput()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to match exceptions against 111.');
        $this->subjectWithException->threw(111);
    }

    public function testThrewFailureInvalidInputObject()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to match exceptions against #0{}.');
        $this->subjectWithException->threw((object) []);
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
        $this->assertEquals(new Cardinality(0, 4), $this->subject->atMost(4)->cardinality());
        $this->assertEquals(new Cardinality(5, 6), $this->subject->between(5, 6)->cardinality());
        $this->assertEquals(new Cardinality(5, 6, true), $this->subject->between(5, 6)->always()->cardinality());
    }

    public function testCheckIterated()
    {
        $this->assertTrue((bool) $this->iterableSubject->checkIterated());
        $this->assertTrue((bool) $this->iterableSubject->once()->checkIterated());
        $this->assertFalse((bool) $this->subject->checkIterated());
        $this->assertTrue((bool) $this->subject->never()->checkIterated());
        $this->assertFalse((bool) $this->subjectWithNoResponse->checkIterated());
        $this->assertTrue((bool) $this->subjectWithNoResponse->never()->checkIterated());
    }

    public function testIterated()
    {
        $this->assertEquals(
            $this->iterableVerifierFactory->create($this->iterableCall, [$this->iterableCall]),
            $this->iterableSubject->iterated()
        );
        $this->assertEquals(
            $this->iterableVerifierFactory->create($this->call, []),
            $this->subject->never()->iterated()
        );
    }

    public function testIteratedFailure()
    {
        $this->expectException(AssertionException::class);
        $this->subject->iterated();
    }

    public function testIteratedFailureNever()
    {
        $this->expectException(AssertionException::class);
        $this->iterableSubject->never()->iterated();
    }

    public function testIteratedFailureWithException()
    {
        $this->expectException(AssertionException::class);
        $this->subjectWithException->iterated();
    }

    public function testIteratedFailureNeverResponded()
    {
        $this->expectException(AssertionException::class);
        $this->subjectWithNoResponse->iterated();
    }
}
