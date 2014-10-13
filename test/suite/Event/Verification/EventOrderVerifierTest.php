<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Event\Verification;

use Eloquent\Phony\Assertion\Recorder\AssertionRecorder;
use Eloquent\Phony\Assertion\Renderer\AssertionRenderer;
use Eloquent\Phony\Event\EventCollection;
use Eloquent\Phony\Test\TestCallFactory;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class EventOrderVerifierTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->assertionRecorder = new AssertionRecorder();
        $this->assertionRenderer = new AssertionRenderer();
        $this->subject = new EventOrderVerifier($this->assertionRecorder, $this->assertionRenderer);

        $this->callFactory = new TestCallFactory();
        $this->callEventFactory = $this->callFactory->eventFactory();

        $this->callACalled = $this->callEventFactory->createCalled('implode', array('a'));
        $this->callAResponse = $this->callEventFactory->createReturned();
        $this->callBCalled = $this->callEventFactory->createCalled('implode', array('b'));
        $this->callCCalled = $this->callEventFactory->createCalled('implode', array('c'));
        $this->callCResponse = $this->callEventFactory->createReturned();
        $this->callBResponse = $this->callEventFactory->createReturned();
        $this->callA = $this->callFactory->create($this->callACalled, $this->callAResponse);
        $this->callB = $this->callFactory->create($this->callBCalled, $this->callBResponse);
        $this->callC = $this->callFactory->create($this->callCCalled, $this->callCResponse);
    }

    public function testConstructor()
    {
        $this->assertSame($this->assertionRecorder, $this->subject->assertionRecorder());
        $this->assertSame($this->assertionRenderer, $this->subject->assertionRenderer());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new EventOrderVerifier();

        $this->assertSame(AssertionRecorder::instance(), $this->subject->assertionRecorder());
        $this->assertSame(AssertionRenderer::instance(), $this->subject->assertionRenderer());
    }

    public function testCheckInOrderSequence()
    {
        $this->assertTrue((boolean) $this->subject->checkInOrderSequence(array()));
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
                    new EventCollection(array($this->callA, $this->callC)),
                    new EventCollection(array($this->callB)),
                )
            )
        );
        $this->assertTrue(
            (boolean) $this->subject->checkInOrderSequence(
                array(
                    new EventCollection(array($this->callB)),
                    new EventCollection(array($this->callA, $this->callC)),
                )
            )
        );
        $this->assertFalse((boolean) $this->subject->checkInOrderSequence(array($this->callB, $this->callA)));
        $this->assertFalse((boolean) $this->subject->checkInOrderSequence(array($this->callC, $this->callB)));
        $this->assertFalse((boolean) $this->subject->checkInOrderSequence(array($this->callA, $this->callA)));
        $this->assertFalse(
            (boolean) $this->subject->checkInOrderSequence(
                array(
                    new EventCollection(array($this->callB, $this->callC)),
                    new EventCollection(array($this->callA)),
                )
            )
        );
        $this->assertFalse(
            (boolean) $this->subject->checkInOrderSequence(
                array(
                    new EventCollection(array($this->callC)),
                    new EventCollection(array($this->callA, $this->callB)),
                )
            )
        );
        $this->assertFalse(
            (boolean) $this->subject->checkInOrderSequence(
                array(
                    new EventCollection(),
                    new EventCollection(array($this->callA)),
                )
            )
        );
        $this->assertFalse(
            (boolean) $this->subject->checkInOrderSequence(
                array(
                    new EventCollection(array($this->callA)),
                    new EventCollection(),
                )
            )
        );
    }

    public function testInOrderSequence()
    {
        $this->assertEquals(new EventCollection(), $this->subject->inOrderSequence(array()));
        $this->assertEquals(
            new EventCollection(array($this->callA)),
            $this->subject->inOrderSequence(array($this->callA))
        );
        $this->assertEquals(
            new EventCollection(array($this->callA, $this->callB, $this->callC)),
            $this->subject->inOrderSequence(array($this->callA, $this->callB, $this->callC))
        );
        $this->assertEquals(
            new EventCollection(array($this->callACalled, $this->callBCalled, $this->callCCalled)),
            $this->subject
                ->inOrderSequence(array($this->callACalled, $this->callBCalled, $this->callCCalled))
        );
        $this->assertEquals(
            new EventCollection(array($this->callAResponse, $this->callCResponse, $this->callBResponse)),
            $this->subject
                ->inOrderSequence(array($this->callAResponse, $this->callCResponse, $this->callBResponse))
        );
        $this->assertEquals(
            new EventCollection(array($this->callACalled, $this->callAResponse)),
            $this->subject->inOrderSequence(array($this->callACalled, $this->callAResponse))
        );
        $this->assertEquals(
            new EventCollection(array($this->callA, $this->callB)),
            $this->subject->inOrderSequence(
                array(
                    new EventCollection(array($this->callA, $this->callC)),
                    new EventCollection(array($this->callB)),
                )
            )
        );
        $this->assertEquals(
            new EventCollection(array($this->callB, $this->callC)),
            $this->subject->inOrderSequence(
                array(
                    new EventCollection(array($this->callB)),
                    new EventCollection(array($this->callA, $this->callC)),
                )
            )
        );
    }

    public function testInOrderSequenceFailure()
    {
        $expected = <<<'EOD'
Expected events in order:
    - implode('a')
    - implode('c')
    - implode('b')
Order:
    - implode('a')
    - implode('b')
    - implode('c')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->inOrderSequence(array($this->callA, $this->callC, $this->callB));
    }

    public function testInOrderSequenceFailureOnlySuppliedEvents()
    {
        $expected = <<<'EOD'
Expected events in order:
    - implode('b')
    - implode('a')
Order:
    - implode('a')
    - implode('b')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->inOrderSequence(array($this->callB, $this->callA));
    }

    public function testInOrderSequenceFailureEventMergingExampleA()
    {
        $expected = <<<'EOD'
Expected events in order:
    - implode('b')
    - implode('a')
Order:
    - implode('a')
    - implode('b')
    - implode('c')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->inOrderSequence(
            array(
                new EventCollection(array($this->callB, $this->callC)),
                new EventCollection(array($this->callA)),
            )
        );
    }

    public function testInOrderSequenceFailureEventMergingExampleB()
    {
        $expected = <<<'EOD'
Expected events in order:
    - implode('c')
    - implode('b')
Order:
    - implode('a')
    - implode('b')
    - implode('c')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->inOrderSequence(
            array(
                new EventCollection(array($this->callC)),
                new EventCollection(array($this->callA, $this->callB)),
            )
        );
    }

    public function testInOrderSequenceFailureEventMergingExampleC()
    {
        $expected = <<<'EOD'
Expected events in order:
    - implode('c')
    - implode('a')
    - implode('c')
    - <none>
Order:
    - implode('a')
    - implode('b')
    - implode('c')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->inOrderSequence(
            array(
                new EventCollection(array($this->callC)),
                new EventCollection(array($this->callB, $this->callA)),
                new EventCollection(array($this->callC)),
                new EventCollection(),
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
        $this->subject->inOrderSequence(array(new EventCollection()));
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
