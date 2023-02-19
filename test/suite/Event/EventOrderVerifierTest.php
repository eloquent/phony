<?php

declare(strict_types=1);

namespace Eloquent\Phony\Event;

use AllowDynamicProperties;
use Eloquent\Phony\Assertion\Exception\AssertionException;
use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Test\Facade\FacadeContainer;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

#[AllowDynamicProperties]
class EventOrderVerifierTest extends TestCase
{
    protected function setUp(): void
    {
        $this->container = FacadeContainer::withTestCallFactory();
        $this->callFactory = $this->container->callFactory;
        $this->eventFactory = $this->container->eventFactory;
        $this->container->assertionRenderer->setUseColor(false);
        $this->container->differenceEngine->setUseColor(false);

        $this->subject = $this->container->eventOrderVerifier;

        $this->callVerifierFactory = $this->container->callVerifierFactory;

        $this->callACalled = $this->eventFactory->createCalled('implode', Arguments::create('a'));
        $this->callAResponse = $this->eventFactory->createReturned(null);
        $this->callBCalled = $this->eventFactory->createCalled('implode', Arguments::create('b'));
        $this->callCCalled = $this->eventFactory->createCalled('implode', Arguments::create('c'));
        $this->callCResponse = $this->eventFactory->createReturned(null);
        $this->callBResponse = $this->eventFactory->createReturned(null);
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
}
