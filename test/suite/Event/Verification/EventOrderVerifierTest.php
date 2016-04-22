<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Event\Verification;

use Eloquent\Phony\Assertion\AssertionRenderer;
use Eloquent\Phony\Assertion\ExceptionAssertionRecorder;
use Eloquent\Phony\Call\Argument\Arguments;
use Eloquent\Phony\Event\EventSequence;
use Eloquent\Phony\Test\TestCallFactory;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class EventOrderVerifierTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->assertionRecorder = ExceptionAssertionRecorder::instance();
        $this->assertionRenderer = AssertionRenderer::instance();
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
                new EventSequence(array($this->callA, $this->callC)),
                new EventSequence(array($this->callB))
            )
        );
        $this->assertTrue(
            (boolean) $this->subject->checkInOrder(
                new EventSequence(array($this->callB)),
                new EventSequence(array($this->callA, $this->callC))
            )
        );
        $this->assertFalse((boolean) $this->subject->checkInOrder());
        $this->assertFalse((boolean) $this->subject->checkInOrder($this->callB, $this->callA));
        $this->assertFalse((boolean) $this->subject->checkInOrder($this->callC, $this->callB));
        $this->assertFalse((boolean) $this->subject->checkInOrder($this->callA, $this->callA));
        $this->assertFalse(
            (boolean) $this->subject->checkInOrder(
                new EventSequence(array($this->callB, $this->callC)),
                new EventSequence(array($this->callA))
            )
        );
        $this->assertFalse(
            (boolean) $this->subject->checkInOrder(
                new EventSequence(array($this->callC)),
                new EventSequence(array($this->callA, $this->callB))
            )
        );
        $this->assertFalse(
            (boolean) $this->subject->checkInOrder(
                new EventSequence(array()),
                new EventSequence(array($this->callA))
            )
        );
        $this->assertFalse(
            (boolean) $this->subject->checkInOrder(
                new EventSequence(array($this->callA)),
                new EventSequence(array())
            )
        );
    }

    public function testCheckInOrderFailureInvalidArgument()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Cannot verify event order with supplied value of type integer.'
        );
        $this->subject->checkInOrder(111);
    }

    public function testCheckInOrderFailureInvalidArgumentObject()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            "Cannot verify event order with supplied value of type 'stdClass'."
        );
        $this->subject->checkInOrder((object) array());
    }

    public function testInOrder()
    {
        $this->assertEquals(new EventSequence(array($this->callA)), $this->subject->inOrder($this->callA));
        $this->assertEquals(
            new EventSequence(array($this->callA, $this->callB, $this->callC)),
            $this->subject->inOrder($this->callA, $this->callB, $this->callC)
        );
        $this->assertEquals(
            new EventSequence(array($this->callACalled, $this->callBCalled, $this->callCCalled)),
            $this->subject->inOrder($this->callACalled, $this->callBCalled, $this->callCCalled)
        );
        $this->assertEquals(
            new EventSequence(array($this->callAResponse, $this->callCResponse, $this->callBResponse)),
            $this->subject->inOrder($this->callAResponse, $this->callCResponse, $this->callBResponse)
        );
        $this->assertEquals(
            new EventSequence(array($this->callACalled, $this->callAResponse)),
            $this->subject->inOrder($this->callACalled, $this->callAResponse)
        );
        $this->assertEquals(
            new EventSequence(array($this->callA, $this->callB)),
            $this->subject->inOrder(
                new EventSequence(array($this->callA, $this->callC)),
                new EventSequence(array($this->callB))
            )
        );
        $this->assertEquals(
            new EventSequence(array($this->callB, $this->callC)),
            $this->subject->inOrder(
                new EventSequence(array($this->callB)),
                new EventSequence(array($this->callA, $this->callC))
            )
        );
    }

    public function testInOrderFailure()
    {
        $expected = <<<'EOD'
Expected events in order:
    - called implode("a")
    - called implode("c")
    - called implode("b")
Order:
    - called implode("a")
    - called implode("b")
    - called implode("c")
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->inOrder($this->callA, $this->callC, $this->callB);
    }

    public function testInOrderFailureEmpty()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected events. No events recorded.'
        );
        $this->subject->inOrder();
    }

    public function testInOrderFailureOnlySuppliedEvents()
    {
        $expected = <<<'EOD'
Expected events in order:
    - called implode("b")
    - called implode("a")
Order:
    - called implode("a")
    - called implode("b")
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->inOrder($this->callB, $this->callA);
    }

    public function testInOrderFailureEventMergingExampleA()
    {
        $expected = <<<'EOD'
Expected events in order:
    - called implode("b")
    - called implode("a")
Order:
    - called implode("a")
    - called implode("b")
    - called implode("c")
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->inOrder(
            new EventSequence(array($this->callB, $this->callC)),
            new EventSequence(array($this->callA))
        );
    }

    public function testInOrderFailureEventMergingExampleB()
    {
        $expected = <<<'EOD'
Expected events in order:
    - called implode("c")
    - called implode("b")
Order:
    - called implode("a")
    - called implode("b")
    - called implode("c")
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->inOrder(
            new EventSequence(array($this->callC)),
            new EventSequence(array($this->callA, $this->callB))
        );
    }

    public function testInOrderFailureEventMergingExampleC()
    {
        $expected = <<<'EOD'
Expected events in order:
    - called implode("c")
    - called implode("a")
    - called implode("c")
    - <none>
Order:
    - called implode("a")
    - called implode("b")
    - called implode("c")
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->inOrder(
            new EventSequence(array($this->callC)),
            new EventSequence(array($this->callB, $this->callA)),
            new EventSequence(array($this->callC)),
            new EventSequence(array())
        );
    }

    public function testInOrderFailureWithEmptyMatch()
    {
        $expected = <<<'EOD'
Expected events in order:
    - <none>
No events recorded.
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->inOrder(new EventSequence(array()));
    }

    public function testInOrderFailureInvalidArgument()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Cannot verify event order with supplied value of type integer.'
        );
        $this->subject->inOrder(111);
    }

    public function testInOrderFailureInvalidArgumentObject()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            "Cannot verify event order with supplied value of type 'stdClass'."
        );
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
                    new EventSequence(array($this->callA, $this->callC)),
                    new EventSequence(array($this->callB)),
                )
            )
        );
        $this->assertTrue(
            (boolean) $this->subject->checkInOrderSequence(
                array(
                    new EventSequence(array($this->callB)),
                    new EventSequence(array($this->callA, $this->callC)),
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
                    new EventSequence(array($this->callB, $this->callC)),
                    new EventSequence(array($this->callA)),
                )
            )
        );
        $this->assertFalse(
            (boolean) $this->subject->checkInOrderSequence(
                array(
                    new EventSequence(array($this->callC)),
                    new EventSequence(array($this->callA, $this->callB)),
                )
            )
        );
        $this->assertFalse(
            (boolean) $this->subject->checkInOrderSequence(
                array(
                    new EventSequence(array()),
                    new EventSequence(array($this->callA)),
                )
            )
        );
        $this->assertFalse(
            (boolean) $this->subject->checkInOrderSequence(
                array(
                    new EventSequence(array($this->callA)),
                    new EventSequence(array()),
                )
            )
        );
    }

    public function testCheckInOrderSequenceFailureInvalidArgument()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Cannot verify event order with supplied value of type integer.'
        );
        $this->subject->checkInOrderSequence(array(111));
    }

    public function testCheckInOrderSequenceFailureInvalidArgumentObject()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            "Cannot verify event order with supplied value of type 'stdClass'."
        );
        $this->subject->checkInOrderSequence(array((object) array()));
    }

    public function testInOrderSequence()
    {
        $this->assertEquals(
            new EventSequence(array($this->callA)),
            $this->subject->inOrderSequence(array($this->callA))
        );
        $this->assertEquals(
            new EventSequence(array($this->callA, $this->callB, $this->callC)),
            $this->subject->inOrderSequence(array($this->callA, $this->callB, $this->callC))
        );
        $this->assertEquals(
            new EventSequence(array($this->callACalled, $this->callBCalled, $this->callCCalled)),
            $this->subject
                ->inOrderSequence(array($this->callACalled, $this->callBCalled, $this->callCCalled))
        );
        $this->assertEquals(
            new EventSequence(array($this->callAResponse, $this->callCResponse, $this->callBResponse)),
            $this->subject
                ->inOrderSequence(array($this->callAResponse, $this->callCResponse, $this->callBResponse))
        );
        $this->assertEquals(
            new EventSequence(array($this->callACalled, $this->callAResponse)),
            $this->subject->inOrderSequence(array($this->callACalled, $this->callAResponse))
        );
        $this->assertEquals(
            new EventSequence(array($this->callA, $this->callB)),
            $this->subject->inOrderSequence(
                array(
                    new EventSequence(array($this->callA, $this->callC)),
                    new EventSequence(array($this->callB)),
                )
            )
        );
        $this->assertEquals(
            new EventSequence(array($this->callB, $this->callC)),
            $this->subject->inOrderSequence(
                array(
                    new EventSequence(array($this->callB)),
                    new EventSequence(array($this->callA, $this->callC)),
                )
            )
        );
    }

    public function testInOrderSequenceFailure()
    {
        $expected = <<<'EOD'
Expected events in order:
    - called implode("a")
    - called implode("c")
    - called implode("b")
Order:
    - called implode("a")
    - called implode("b")
    - called implode("c")
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->inOrderSequence(array($this->callA, $this->callC, $this->callB));
    }

    public function testInOrderSequenceFailureEmpty()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected events. No events recorded.'
        );
        $this->subject->inOrderSequence(array());
    }

    public function testInOrderSequenceFailureOnlySuppliedEvents()
    {
        $expected = <<<'EOD'
Expected events in order:
    - called implode("b")
    - called implode("a")
Order:
    - called implode("a")
    - called implode("b")
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->inOrderSequence(array($this->callB, $this->callA));
    }

    public function testInOrderSequenceFailureEventMergingExampleA()
    {
        $expected = <<<'EOD'
Expected events in order:
    - called implode("b")
    - called implode("a")
Order:
    - called implode("a")
    - called implode("b")
    - called implode("c")
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->inOrderSequence(
            array(
                new EventSequence(array($this->callB, $this->callC)),
                new EventSequence(array($this->callA)),
            )
        );
    }

    public function testInOrderSequenceFailureEventMergingExampleB()
    {
        $expected = <<<'EOD'
Expected events in order:
    - called implode("c")
    - called implode("b")
Order:
    - called implode("a")
    - called implode("b")
    - called implode("c")
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->inOrderSequence(
            array(
                new EventSequence(array($this->callC)),
                new EventSequence(array($this->callA, $this->callB)),
            )
        );
    }

    public function testInOrderSequenceFailureEventMergingExampleC()
    {
        $expected = <<<'EOD'
Expected events in order:
    - called implode("c")
    - called implode("a")
    - called implode("c")
    - <none>
Order:
    - called implode("a")
    - called implode("b")
    - called implode("c")
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->inOrderSequence(
            array(
                new EventSequence(array($this->callC)),
                new EventSequence(array($this->callB, $this->callA)),
                new EventSequence(array($this->callC)),
                new EventSequence(array()),
            )
        );
    }

    public function testInOrderSequenceFailureWithEmptyMatch()
    {
        $expected = <<<'EOD'
Expected events in order:
    - <none>
No events recorded.
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->inOrderSequence(array(new EventSequence(array())));
    }

    public function testInOrderSequenceFailureInvalidArgument()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Cannot verify event order with supplied value of type integer.'
        );
        $this->subject->inOrderSequence(array(111));
    }

    public function testInOrderSequenceFailureInvalidArgumentObject()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            "Cannot verify event order with supplied value of type 'stdClass'."
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
        $this->assertEquals(new EventSequence(array($this->callA)), $this->subject->anyOrder($this->callA));
        $this->assertEquals(
            new EventSequence(array($this->callA, $this->callB, $this->callC)),
            $this->subject->anyOrder($this->callA, $this->callB, $this->callC)
        );
        $this->assertEquals(
            new EventSequence(array($this->callA, $this->callB, $this->callC)),
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
            new EventSequence(array($this->callA)),
            $this->subject->anyOrderSequence(array($this->callA))
        );
        $this->assertEquals(
            new EventSequence(array($this->callA, $this->callB, $this->callC)),
            $this->subject->anyOrderSequence(array($this->callA, $this->callB, $this->callC))
        );
        $this->assertEquals(
            new EventSequence(array($this->callA, $this->callB, $this->callC)),
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
