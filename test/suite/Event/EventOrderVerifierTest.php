<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Event;

use Eloquent\Phony\Assertion\AssertionRenderer;
use Eloquent\Phony\Assertion\ExceptionAssertionRecorder;
use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Call\CallVerifierFactory;
use Eloquent\Phony\Difference\DifferenceEngine;
use Eloquent\Phony\Exporter\InlineExporter;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Matcher\MatcherVerifier;
use Eloquent\Phony\Reflection\FeatureDetector;
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Test\TestCallFactory;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class EventOrderVerifierTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->callVerifierFactory = CallVerifierFactory::instance();
        $this->assertionRecorder = ExceptionAssertionRecorder::instance();
        $this->assertionRecorder->setCallVerifierFactory($this->callVerifierFactory);
        $this->invocableInspector = InvocableInspector::instance();
        $this->matcherVerifier = MatcherVerifier::instance();
        $this->objectSequencer = new Sequencer();
        $this->inlineExporter = new InlineExporter(1, $this->objectSequencer, $this->invocableInspector);
        $this->differenceEngine = DifferenceEngine::instance();
        $this->featureDetector = FeatureDetector::instance();
        $this->assertionRenderer = new AssertionRenderer(
            $this->invocableInspector,
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
        $this->assertTrue((boolean) $this->subject->checkInOrder($this->callA));
        $this->assertTrue((boolean) $this->subject->checkInOrder($this->callA, $this->callB, $this->callC));
        $this->assertTrue(
            (boolean) $this->subject->checkInOrder($this->callACalled, $this->callBCalled, $this->callCCalled)
        );
        $this->assertTrue(
            (boolean) $this->subject->checkInOrder($this->callAResponse, $this->callCResponse, $this->callBResponse)
        );
        $this->assertTrue((boolean) $this->subject->checkInOrder($this->callACalled, $this->callAResponse));
        $this->assertTrue(
            (boolean) $this->subject->checkInOrder(
                new EventSequence(array($this->callA, $this->callC), $this->callVerifierFactory),
                new EventSequence(array($this->callB), $this->callVerifierFactory)
            )
        );
        $this->assertTrue(
            (boolean) $this->subject->checkInOrder(
                new EventSequence(array($this->callB), $this->callVerifierFactory),
                new EventSequence(array($this->callA, $this->callC), $this->callVerifierFactory)
            )
        );
        $this->assertFalse((boolean) $this->subject->checkInOrder());
        $this->assertFalse((boolean) $this->subject->checkInOrder($this->callB, $this->callA));
        $this->assertFalse((boolean) $this->subject->checkInOrder($this->callC, $this->callB));
        $this->assertFalse((boolean) $this->subject->checkInOrder($this->callA, $this->callA));
        $this->assertFalse(
            (boolean) $this->subject->checkInOrder(
                new EventSequence(array($this->callB, $this->callC), $this->callVerifierFactory),
                new EventSequence(array($this->callA), $this->callVerifierFactory)
            )
        );
        $this->assertFalse(
            (boolean) $this->subject->checkInOrder(
                new EventSequence(array($this->callC), $this->callVerifierFactory),
                new EventSequence(array($this->callA, $this->callB), $this->callVerifierFactory)
            )
        );
    }

    public function testCheckInOrderFailureEmptyResult()
    {
        $this->setExpectedException('InvalidArgumentException', 'Cannot verify event order with empty results.');
        $this->subject->checkInOrder(new EventSequence(array(), $this->callVerifierFactory));
    }

    public function testCheckInOrderFailureInvalidArgument()
    {
        $this->setExpectedException('InvalidArgumentException', 'Cannot verify event order with supplied value 111.');
        $this->subject->checkInOrder(111);
    }

    public function testCheckInOrderFailureInvalidArgumentObject()
    {
        $this->setExpectedException('InvalidArgumentException', 'Cannot verify event order with supplied value #0{}.');
        $this->subject->checkInOrder((object) array());
    }

    public function testInOrder()
    {
        $this->assertEquals(
            new EventSequence(array($this->callA), $this->callVerifierFactory),
            $this->subject->inOrder($this->callA)
        );
        $this->assertEquals(
            new EventSequence(array($this->callA, $this->callB, $this->callC), $this->callVerifierFactory),
            $this->subject->inOrder($this->callA, $this->callB, $this->callC)
        );
        $this->assertEquals(
            new EventSequence(
                array($this->callACalled, $this->callBCalled, $this->callCCalled),
                $this->callVerifierFactory
            ),
            $this->subject->inOrder($this->callACalled, $this->callBCalled, $this->callCCalled)
        );
        $this->assertEquals(
            new EventSequence(
                array($this->callAResponse, $this->callCResponse, $this->callBResponse),
                $this->callVerifierFactory
            ),
            $this->subject->inOrder($this->callAResponse, $this->callCResponse, $this->callBResponse)
        );
        $this->assertEquals(
            new EventSequence(array($this->callACalled, $this->callAResponse), $this->callVerifierFactory),
            $this->subject->inOrder($this->callACalled, $this->callAResponse)
        );
        $this->assertEquals(
            new EventSequence(array($this->callA, $this->callB), $this->callVerifierFactory),
            $this->subject->inOrder(
                new EventSequence(array($this->callA, $this->callC), $this->callVerifierFactory),
                new EventSequence(array($this->callB), $this->callVerifierFactory)
            )
        );
        $this->assertEquals(
            new EventSequence(array($this->callB, $this->callC), $this->callVerifierFactory),
            $this->subject->inOrder(
                new EventSequence(array($this->callB), $this->callVerifierFactory),
                new EventSequence(array($this->callA, $this->callC), $this->callVerifierFactory)
            )
        );
    }

    public function testInOrderFailure()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->inOrder($this->callA, $this->callC, $this->callB);
    }

    public function testInOrderFailureEmpty()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->inOrder();
    }

    public function testInOrderFailureOnlySuppliedEvents()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->inOrder($this->callB, $this->callA);
    }

    public function testInOrderFailureEventMergingExampleA()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->inOrder(
            new EventSequence(array($this->callB, $this->callC), $this->callVerifierFactory),
            new EventSequence(array($this->callA), $this->callVerifierFactory)
        );
    }

    public function testInOrderFailureEventMergingExampleB()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->inOrder(
            new EventSequence(array($this->callC), $this->callVerifierFactory),
            new EventSequence(array($this->callA, $this->callB), $this->callVerifierFactory)
        );
    }

    public function testInOrderFailureEventMergingExampleC()
    {
        $this->setExpectedException('InvalidArgumentException', 'Cannot verify event order with empty results.');

        $this->subject->inOrder(
            new EventSequence(array($this->callC), $this->callVerifierFactory),
            new EventSequence(array($this->callB, $this->callA), $this->callVerifierFactory),
            new EventSequence(array($this->callC), $this->callVerifierFactory),
            new EventSequence(array(), $this->callVerifierFactory)
        );
    }

    public function testInOrderFailureEmptyResult()
    {
        $this->setExpectedException('InvalidArgumentException', 'Cannot verify event order with empty results.');
        $this->subject->inOrder(new EventSequence(array(), $this->callVerifierFactory));
    }

    public function testInOrderFailureInvalidArgument()
    {
        $this->setExpectedException('InvalidArgumentException', 'Cannot verify event order with supplied value 111.');
        $this->subject->inOrder(111);
    }

    public function testInOrderFailureInvalidArgumentObject()
    {
        $this->setExpectedException('InvalidArgumentException', 'Cannot verify event order with supplied value #0{}.');
        $this->subject->inOrder((object) array());
    }

    public function testCheckInOrderSequence()
    {
        $this->assertTrue((boolean) $this->subject->checkInOrderSequence(array($this->callA)));
        $this->assertTrue(
            (boolean) $this->subject->checkInOrderSequence(array($this->callA, $this->callB, $this->callC))
        );
        $this->assertTrue(
            (boolean) $this->subject
                ->checkInOrderSequence(array($this->callACalled, $this->callBCalled, $this->callCCalled))
        );
        $this->assertTrue(
            (boolean) $this->subject
                ->checkInOrderSequence(array($this->callAResponse, $this->callCResponse, $this->callBResponse))
        );
        $this->assertTrue(
            (boolean) $this->subject->checkInOrderSequence(array($this->callACalled, $this->callAResponse))
        );
        $this->assertTrue(
            (boolean) $this->subject->checkInOrderSequence(
                array(
                    new EventSequence(array($this->callA, $this->callC), $this->callVerifierFactory),
                    new EventSequence(array($this->callB), $this->callVerifierFactory),
                )
            )
        );
        $this->assertTrue(
            (boolean) $this->subject->checkInOrderSequence(
                array(
                    new EventSequence(array($this->callB), $this->callVerifierFactory),
                    new EventSequence(array($this->callA, $this->callC), $this->callVerifierFactory),
                )
            )
        );
        $this->assertFalse((boolean) $this->subject->checkInOrderSequence(array()));
        $this->assertFalse((boolean) $this->subject->checkInOrderSequence(array($this->callB, $this->callA)));
        $this->assertFalse((boolean) $this->subject->checkInOrderSequence(array($this->callC, $this->callB)));
        $this->assertFalse((boolean) $this->subject->checkInOrderSequence(array($this->callA, $this->callA)));
        $this->assertFalse(
            (boolean) $this->subject->checkInOrderSequence(
                array(
                    new EventSequence(array($this->callB, $this->callC), $this->callVerifierFactory),
                    new EventSequence(array($this->callA), $this->callVerifierFactory),
                )
            )
        );
        $this->assertFalse(
            (boolean) $this->subject->checkInOrderSequence(
                array(
                    new EventSequence(array($this->callC), $this->callVerifierFactory),
                    new EventSequence(array($this->callA, $this->callB), $this->callVerifierFactory),
                )
            )
        );
    }

    public function testCheckInOrderSequenceFailureEmptyResult()
    {
        $this->setExpectedException('InvalidArgumentException', 'Cannot verify event order with empty results.');
        $this->subject->checkInOrderSequence(array(new EventSequence(array(), $this->callVerifierFactory)));
    }

    public function testCheckInOrderSequenceFailureInvalidArgument()
    {
        $this->setExpectedException('InvalidArgumentException', 'Cannot verify event order with supplied value 111.');
        $this->subject->checkInOrderSequence(array(111));
    }

    public function testCheckInOrderSequenceFailureInvalidArgumentObject()
    {
        $this->setExpectedException('InvalidArgumentException', 'Cannot verify event order with supplied value #0{}.');
        $this->subject->checkInOrderSequence(array((object) array()));
    }

    public function testInOrderSequence()
    {
        $this->assertEquals(
            new EventSequence(array($this->callA), $this->callVerifierFactory),
            $this->subject->inOrderSequence(array($this->callA))
        );
        $this->assertEquals(
            new EventSequence(array($this->callA, $this->callB, $this->callC), $this->callVerifierFactory),
            $this->subject->inOrderSequence(array($this->callA, $this->callB, $this->callC))
        );
        $this->assertEquals(
            new EventSequence(
                array($this->callACalled, $this->callBCalled, $this->callCCalled),
                $this->callVerifierFactory
            ),
            $this->subject
                ->inOrderSequence(array($this->callACalled, $this->callBCalled, $this->callCCalled))
        );
        $this->assertEquals(
            new EventSequence(
                array($this->callAResponse, $this->callCResponse, $this->callBResponse),
                $this->callVerifierFactory
            ),
            $this->subject
                ->inOrderSequence(array($this->callAResponse, $this->callCResponse, $this->callBResponse))
        );
        $this->assertEquals(
            new EventSequence(array($this->callACalled, $this->callAResponse), $this->callVerifierFactory),
            $this->subject->inOrderSequence(array($this->callACalled, $this->callAResponse))
        );
        $this->assertEquals(
            new EventSequence(array($this->callA, $this->callB), $this->callVerifierFactory),
            $this->subject->inOrderSequence(
                array(
                    new EventSequence(array($this->callA, $this->callC), $this->callVerifierFactory),
                    new EventSequence(array($this->callB), $this->callVerifierFactory),
                )
            )
        );
        $this->assertEquals(
            new EventSequence(array($this->callB, $this->callC), $this->callVerifierFactory),
            $this->subject->inOrderSequence(
                array(
                    new EventSequence(array($this->callB), $this->callVerifierFactory),
                    new EventSequence(array($this->callA, $this->callC), $this->callVerifierFactory),
                )
            )
        );
    }

    public function testInOrderSequenceFailure()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->inOrderSequence(array($this->callA, $this->callC, $this->callB));
    }

    public function testInOrderSequenceFailureEmpty()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->inOrderSequence(array());
    }

    public function testInOrderSequenceFailureOnlySuppliedEvents()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->inOrderSequence(array($this->callB, $this->callA));
    }

    public function testInOrderSequenceFailureEventMergingExampleA()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->inOrderSequence(
            array(
                new EventSequence(array($this->callB, $this->callC), $this->callVerifierFactory),
                new EventSequence(array($this->callA), $this->callVerifierFactory),
            )
        );
    }

    public function testInOrderSequenceFailureEventMergingExampleB()
    {
        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException');
        $this->subject->inOrderSequence(
            array(
                new EventSequence(array($this->callC), $this->callVerifierFactory),
                new EventSequence(array($this->callA, $this->callB), $this->callVerifierFactory),
            )
        );
    }

    public function testInOrderSequenceFailureEventMergingExampleC()
    {
        $this->setExpectedException('InvalidArgumentException', 'Cannot verify event order with empty results.');

        $this->subject->inOrderSequence(
            array(
                new EventSequence(array($this->callC), $this->callVerifierFactory),
                new EventSequence(array($this->callB, $this->callA), $this->callVerifierFactory),
                new EventSequence(array($this->callC), $this->callVerifierFactory),
                new EventSequence(array(), $this->callVerifierFactory),
            )
        );
    }

    public function testInOrderSequenceFailureEmptyResult()
    {
        $this->setExpectedException('InvalidArgumentException', 'Cannot verify event order with empty results.');
        $this->subject->inOrderSequence(array(new EventSequence(array(), $this->callVerifierFactory)));
    }

    public function testInOrderSequenceFailureInvalidArgument()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Cannot verify event order with supplied value 111.'
        );
        $this->subject->inOrderSequence(array(111));
    }

    public function testInOrderSequenceFailureInvalidArgumentObject()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Cannot verify event order with supplied value #0{}.'
        );
        $this->subject->inOrderSequence(array((object) array()));
    }

    public function testCheckAnyOrder()
    {
        $this->assertTrue((boolean) $this->subject->checkAnyOrder($this->callA));
        $this->assertTrue((boolean) $this->subject->checkAnyOrder($this->callA, $this->callB, $this->callC));
        $this->assertTrue((boolean) $this->subject->checkAnyOrder($this->callC, $this->callB, $this->callA));
        $this->assertFalse((boolean) $this->subject->checkAnyOrder());
    }

    public function testAnyOrder()
    {
        $this->assertEquals(
            new EventSequence(array($this->callA), $this->callVerifierFactory),
            $this->subject->anyOrder($this->callA)
        );
        $this->assertEquals(
            new EventSequence(array($this->callA, $this->callB, $this->callC), $this->callVerifierFactory),
            $this->subject->anyOrder($this->callA, $this->callB, $this->callC)
        );
        $this->assertEquals(
            new EventSequence(array($this->callA, $this->callB, $this->callC), $this->callVerifierFactory),
            $this->subject->anyOrder($this->callC, $this->callB, $this->callA)
        );
    }

    public function testAnyOrderFailure()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected events. No events recorded.'
        );
        $this->subject->anyOrder();
    }

    public function testCheckAnyOrderSequence()
    {
        $this->assertTrue((boolean) $this->subject->checkAnyOrderSequence(array($this->callA)));
        $this->assertTrue(
            (boolean) $this->subject->checkAnyOrderSequence(array($this->callA, $this->callB, $this->callC))
        );
        $this->assertTrue(
            (boolean) $this->subject->checkAnyOrderSequence(array($this->callC, $this->callB, $this->callA))
        );
        $this->assertFalse((boolean) $this->subject->checkAnyOrderSequence(array()));
    }

    public function testAnyOrderSequence()
    {
        $this->assertEquals(
            new EventSequence(array($this->callA), $this->callVerifierFactory),
            $this->subject->anyOrderSequence(array($this->callA))
        );
        $this->assertEquals(
            new EventSequence(array($this->callA, $this->callB, $this->callC), $this->callVerifierFactory),
            $this->subject->anyOrderSequence(array($this->callA, $this->callB, $this->callC))
        );
        $this->assertEquals(
            new EventSequence(array($this->callA, $this->callB, $this->callC), $this->callVerifierFactory),
            $this->subject->anyOrderSequence(array($this->callC, $this->callB, $this->callA))
        );
    }

    public function testAnyOrderSequenceFailure()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected events. No events recorded.'
        );
        $this->subject->anyOrderSequence(array());
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
