<?php

declare(strict_types=1);

namespace Eloquent\Phony\Event;

use Eloquent\Phony\Assertion\AssertionRenderer;
use Eloquent\Phony\Assertion\Exception\AssertionException;
use Eloquent\Phony\Assertion\ExceptionAssertionRecorder;
use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Call\CallVerifierFactory;
use Eloquent\Phony\Difference\DifferenceEngine;
use Eloquent\Phony\Exporter\InlineExporter;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Matcher\MatcherVerifier;
use Eloquent\Phony\Reflection\FeatureDetector;
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Spy\GeneratorSpyMap;
use Eloquent\Phony\Test\TestCallFactory;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class EventOrderVerifierTest extends TestCase
{
    protected function setUp(): void
    {
        $this->callVerifierFactory = CallVerifierFactory::instance();
        $this->assertionRecorder = ExceptionAssertionRecorder::instance();
        $this->assertionRecorder->setCallVerifierFactory($this->callVerifierFactory);
        $this->invocableInspector = InvocableInspector::instance();
        $this->matcherVerifier = MatcherVerifier::instance();
        $this->arraySequencer = new Sequencer();
        $this->objectSequencer = new Sequencer();
        $this->featureDetector = FeatureDetector::instance();
        $this->generatorSpyMap = GeneratorSpyMap::instance();
        $this->inlineExporter = new InlineExporter(
            1,
            $this->arraySequencer,
            $this->objectSequencer,
            $this->generatorSpyMap,
            $this->invocableInspector,
            $this->featureDetector
        );
        $this->differenceEngine = DifferenceEngine::instance();
        $this->assertionRenderer = new AssertionRenderer(
            $this->matcherVerifier,
            $this->inlineExporter,
            $this->differenceEngine,
            $this->featureDetector
        );
        $this->assertionRenderer->setUseColor(false);
        $this->subject = new EventOrderVerifier($this->assertionRecorder, $this->assertionRenderer);

        $this->callFactory = new TestCallFactory();
        $this->callEventFactory = $this->callFactory->eventFactory();

        $this->callACalled = $this->callEventFactory->createCalled('implode', Arguments::create('a'));
        $this->callAResponse = $this->callEventFactory->createReturned(null);
        $this->callBCalled = $this->callEventFactory->createCalled('implode', Arguments::create('b'));
        $this->callCCalled = $this->callEventFactory->createCalled('implode', Arguments::create('c'));
        $this->callCResponse = $this->callEventFactory->createReturned(null);
        $this->callBResponse = $this->callEventFactory->createReturned(null);
        $this->callA = $this->callFactory->create($this->callACalled, $this->callAResponse);
        $this->callB = $this->callFactory->create($this->callBCalled, $this->callBResponse);
        $this->callC = $this->callFactory->create($this->callCCalled, $this->callCResponse);
    }

    public function testCheckInOrder()
    {
        $this->assertTrue((bool) $this->subject->checkInOrder($this->callA));
        $this->assertTrue((bool) $this->subject->checkInOrder($this->callA, $this->callB, $this->callC));
        $this->assertTrue(
            (bool) $this->subject->checkInOrder($this->callACalled, $this->callBCalled, $this->callCCalled)
        );
        $this->assertTrue(
            (bool) $this->subject->checkInOrder($this->callAResponse, $this->callCResponse, $this->callBResponse)
        );
        $this->assertTrue((bool) $this->subject->checkInOrder($this->callACalled, $this->callAResponse));
        $this->assertTrue(
            (bool) $this->subject->checkInOrder(
                new EventSequence([$this->callA, $this->callC], $this->callVerifierFactory),
                new EventSequence([$this->callB], $this->callVerifierFactory)
            )
        );
        $this->assertTrue(
            (bool) $this->subject->checkInOrder(
                new EventSequence([$this->callB], $this->callVerifierFactory),
                new EventSequence([$this->callA, $this->callC], $this->callVerifierFactory)
            )
        );
        $this->assertFalse((bool) $this->subject->checkInOrder());
        $this->assertFalse((bool) $this->subject->checkInOrder($this->callB, $this->callA));
        $this->assertFalse((bool) $this->subject->checkInOrder($this->callC, $this->callB));
        $this->assertFalse((bool) $this->subject->checkInOrder($this->callA, $this->callA));
        $this->assertFalse(
            (bool) $this->subject->checkInOrder(
                new EventSequence([$this->callB, $this->callC], $this->callVerifierFactory),
                new EventSequence([$this->callA], $this->callVerifierFactory)
            )
        );
        $this->assertFalse(
            (bool) $this->subject->checkInOrder(
                new EventSequence([$this->callC], $this->callVerifierFactory),
                new EventSequence([$this->callA, $this->callB], $this->callVerifierFactory)
            )
        );
    }

    public function testCheckInOrderFailureEmptyResult()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot verify event order with empty results.');
        $this->subject->checkInOrder(new EventSequence([], $this->callVerifierFactory));
    }

    public function testCheckInOrderFailureInvalidArgumentObject()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot verify event order with supplied value #0{}.');
        $this->subject->checkInOrder((object) []);
    }

    public function testInOrder()
    {
        $this->assertEquals(
            new EventSequence([$this->callA], $this->callVerifierFactory),
            $this->subject->inOrder($this->callA)
        );
        $this->assertEquals(
            new EventSequence([$this->callA, $this->callB, $this->callC], $this->callVerifierFactory),
            $this->subject->inOrder($this->callA, $this->callB, $this->callC)
        );
        $this->assertEquals(
            new EventSequence(
                [$this->callACalled, $this->callBCalled, $this->callCCalled],
                $this->callVerifierFactory
            ),
            $this->subject->inOrder($this->callACalled, $this->callBCalled, $this->callCCalled)
        );
        $this->assertEquals(
            new EventSequence(
                [$this->callAResponse, $this->callCResponse, $this->callBResponse],
                $this->callVerifierFactory
            ),
            $this->subject->inOrder($this->callAResponse, $this->callCResponse, $this->callBResponse)
        );
        $this->assertEquals(
            new EventSequence([$this->callACalled, $this->callAResponse], $this->callVerifierFactory),
            $this->subject->inOrder($this->callACalled, $this->callAResponse)
        );
        $this->assertEquals(
            new EventSequence([$this->callA, $this->callB], $this->callVerifierFactory),
            $this->subject->inOrder(
                new EventSequence([$this->callA, $this->callC], $this->callVerifierFactory),
                new EventSequence([$this->callB], $this->callVerifierFactory)
            )
        );
        $this->assertEquals(
            new EventSequence([$this->callB, $this->callC], $this->callVerifierFactory),
            $this->subject->inOrder(
                new EventSequence([$this->callB], $this->callVerifierFactory),
                new EventSequence([$this->callA, $this->callC], $this->callVerifierFactory)
            )
        );
    }

    public function testInOrderFailure()
    {
        $this->expectException(AssertionException::class);
        $this->subject->inOrder($this->callA, $this->callC, $this->callB);
    }

    public function testInOrderFailureEmpty()
    {
        $this->expectException(AssertionException::class);
        $this->subject->inOrder();
    }

    public function testInOrderFailureOnlySuppliedEvents()
    {
        $this->expectException(AssertionException::class);
        $this->subject->inOrder($this->callB, $this->callA);
    }

    public function testInOrderFailureEventMergingExampleA()
    {
        $this->expectException(AssertionException::class);
        $this->subject->inOrder(
            new EventSequence([$this->callB, $this->callC], $this->callVerifierFactory),
            new EventSequence([$this->callA], $this->callVerifierFactory)
        );
    }

    public function testInOrderFailureEventMergingExampleB()
    {
        $this->expectException(AssertionException::class);
        $this->subject->inOrder(
            new EventSequence([$this->callC], $this->callVerifierFactory),
            new EventSequence([$this->callA, $this->callB], $this->callVerifierFactory)
        );
    }

    public function testInOrderFailureEventMergingExampleC()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot verify event order with empty results.');

        $this->subject->inOrder(
            new EventSequence([$this->callC], $this->callVerifierFactory),
            new EventSequence([$this->callB, $this->callA], $this->callVerifierFactory),
            new EventSequence([$this->callC], $this->callVerifierFactory),
            new EventSequence([], $this->callVerifierFactory)
        );
    }

    public function testInOrderFailureEmptyResult()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot verify event order with empty results.');
        $this->subject->inOrder(new EventSequence([], $this->callVerifierFactory));
    }

    public function testInOrderFailureInvalidArgumentObject()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot verify event order with supplied value #0{}.');
        $this->subject->inOrder((object) []);
    }

    public function testCheckAnyOrder()
    {
        $this->assertTrue((bool) $this->subject->checkAnyOrder($this->callA));
        $this->assertTrue((bool) $this->subject->checkAnyOrder($this->callA, $this->callB, $this->callC));
        $this->assertTrue((bool) $this->subject->checkAnyOrder($this->callC, $this->callB, $this->callA));
        $this->assertFalse((bool) $this->subject->checkAnyOrder());
    }

    public function testAnyOrder()
    {
        $this->assertEquals(
            new EventSequence([$this->callA], $this->callVerifierFactory),
            $this->subject->anyOrder($this->callA)
        );
        $this->assertEquals(
            new EventSequence([$this->callA, $this->callB, $this->callC], $this->callVerifierFactory),
            $this->subject->anyOrder($this->callA, $this->callB, $this->callC)
        );
        $this->assertEquals(
            new EventSequence([$this->callA, $this->callB, $this->callC], $this->callVerifierFactory),
            $this->subject->anyOrder($this->callC, $this->callB, $this->callA)
        );
    }

    public function testAnyOrderFailure()
    {
        $this->expectException(AssertionException::class);
        $this->expectExceptionMessage('Expected events. No events recorded.');
        $this->subject->anyOrder();
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
