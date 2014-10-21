<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call;

use Eloquent\Phony\Assertion\Recorder\AssertionRecorder;
use Eloquent\Phony\Assertion\Renderer\AssertionRenderer;
use Eloquent\Phony\Call\Argument\Arguments;
use Eloquent\Phony\Call\Event\CallEventCollection;
use Eloquent\Phony\Call\Event\CalledEvent;
use Eloquent\Phony\Call\Event\ReturnedEvent;
use Eloquent\Phony\Call\Event\ThrewEvent;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Matcher\EqualToMatcher;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Test\TestCallFactory;
use Exception;
use PHPUnit_Framework_TestCase;
use RuntimeException;

class CallVerifierWithGeneratorsTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->callFactory = new TestCallFactory();
        $this->callEventFactory = $this->callFactory->eventFactory();
        $this->callEventFactory->sequencer()->set(111);
        $this->thisValue = (object) array();
        $this->callback = array($this->thisValue, 'implode');
        $this->arguments = new Arguments(array('a', 'b', 'c'));
        $this->returnValue = 'abc';
        $this->calledEvent = $this->callEventFactory->createCalled($this->callback, $this->arguments);
        $this->returnedEvent = $this->callEventFactory->createReturned($this->returnValue);
        $this->call = $this->callFactory->create($this->calledEvent, $this->returnedEvent);

        $this->matcherFactory = new MatcherFactory();
        $this->matcherVerifier = new MatcherVerifier();
        $this->assertionRecorder = new AssertionRecorder();
        $this->assertionRenderer = new AssertionRenderer();
        $this->invocableInspector = new InvocableInspector();
        $this->subject = new CallVerifier(
            $this->call,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );

        $this->duration = $this->returnedEvent->time() - $this->calledEvent->time();
        $this->argumentCount = count($this->arguments);
        $this->matchers = $this->matcherFactory->adaptAll($this->arguments->all());
        $this->otherMatcher = $this->matcherFactory->adapt('d');
        $this->events = array($this->calledEvent, $this->returnedEvent);

        $this->exception = new RuntimeException('You done goofed.');
        $this->threwEvent = $this->callEventFactory->createThrew($this->exception);
        $this->callWithException = $this->callFactory->create($this->calledEvent, $this->threwEvent);
        $this->subjectWithException = new CallVerifier(
            $this->callWithException,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );

        $this->calledEventWithNoArguments = $this->callEventFactory->createCalled($this->callback);
        $this->callWithNoArguments = $this->callFactory
            ->create($this->calledEventWithNoArguments, $this->returnedEvent);
        $this->subjectWithNoArguments = new CallVerifier(
            $this->callWithNoArguments,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );

        $this->calledEventWithNoArguments = $this->callEventFactory->createCalled($this->callback);
        $this->callWithNoResponse = $this->callFactory->create($this->calledEvent);
        $this->subjectWithNoResponse = new CallVerifier(
            $this->callWithNoResponse,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );

        $this->callEventFactory->sequencer()->reset();
        $this->earlyCall = $this->callFactory->create();
        $this->callEventFactory->sequencer()->set(222);
        $this->lateCall = $this->callFactory->create();

        $this->assertionResult = new CallEventCollection(array($this->call));
        $this->returnedAssertionResult = new CallEventCollection(array($this->call->responseEvent()));
        $this->threwAssertionResult = new CallEventCollection(array($this->callWithException->responseEvent()));
        $this->emptyAssertionResult = new CallEventCollection();

        // additions for generators

        $this->receivedExceptionA = new RuntimeException('Consequences will never be the same.');
        $this->receivedExceptionB = new RuntimeException('Because I backtraced it.');
        $this->generatedEvent = $this->callEventFactory->createGenerated();
        $this->generatorEventA = $this->callEventFactory->createProduced('m', 'n');
        $this->generatorEventB = $this->callEventFactory->createReceived('o');
        $this->generatorEventC = $this->callEventFactory->createProduced('p', 'q');
        $this->generatorEventD = $this->callEventFactory->createReceivedException($this->receivedExceptionA);
        $this->generatorEventE = $this->callEventFactory->createProduced('r', 's');
        $this->generatorEventF = $this->callEventFactory->createReceived('t');
        $this->generatorEventG = $this->callEventFactory->createProduced('u', 'v');
        $this->generatorEventH = $this->callEventFactory->createReceivedException($this->receivedExceptionB);
        $this->generatorEvents = array(
            $this->generatorEventA,
            $this->generatorEventB,
            $this->generatorEventC,
            $this->generatorEventD,
            $this->generatorEventE,
            $this->generatorEventF,
            $this->generatorEventG,
            $this->generatorEventH,
        );
        $this->generatorEndEvent = $this->callEventFactory->createReturned();
        $this->generatorCall = $this->callFactory->create(
            $this->calledEvent,
            $this->generatedEvent,
            $this->generatorEvents,
            $this->generatorEndEvent
        );
        $this->generatorCallEvents = array(
            $this->calledEvent,
            $this->generatedEvent,
            $this->generatorEventA,
            $this->generatorEventB,
            $this->generatorEventC,
            $this->generatorEventD,
            $this->generatorEventE,
            $this->generatorEventF,
            $this->generatorEventG,
            $this->generatorEventH,
            $this->generatorEndEvent,
        );
        $this->generatorSubject = new CallVerifier(
            $this->generatorCall,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );
    }

    public function testProxyMethodsWithGeneratorEvents()
    {
        $this->assertSame($this->calledEvent, $this->generatorSubject->calledEvent());
        $this->assertSame($this->generatedEvent, $this->generatorSubject->responseEvent());
        $this->assertSame($this->generatorEvents, $this->generatorSubject->traversableEvents());
        $this->assertSame($this->generatorEndEvent, $this->generatorSubject->endEvent());
        $this->assertSame($this->generatorCallEvents, $this->generatorSubject->events());
        $this->assertTrue($this->generatorSubject->hasResponded());
        $this->assertTrue($this->generatorSubject->isGenerator());
        $this->assertTrue($this->generatorSubject->hasCompleted());
        $this->assertSame($this->callback, $this->generatorSubject->callback());
        $this->assertSame($this->arguments, $this->generatorSubject->arguments());
        $this->assertInstanceOf('Generator', $this->generatorSubject->returnValue());
        $this->assertSame($this->calledEvent->sequenceNumber(), $this->generatorSubject->sequenceNumber());
        $this->assertSame($this->calledEvent->time(), $this->generatorSubject->time());
        $this->assertSame($this->generatedEvent->time(), $this->generatorSubject->responseTime());
        $this->assertSame($this->generatorEndEvent->time(), $this->generatorSubject->endTime());
        $this->assertNull($this->generatorSubject->exception());
    }

    public function testAddGeneratorEvent()
    {
        $generatedEvent = $this->callEventFactory->createGenerated();
        $generatorEventA = $this->callEventFactory->createProduced();
        $generatorEventB = $this->callEventFactory->createReceived();
        $generatorEvents = array($generatorEventA, $generatorEventB);
        $this->call = new Call($this->calledEvent, $generatedEvent);
        $this->subject = new CallVerifier(
            $this->call,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );
        $this->subject->addTraversableEvent($generatorEventA);
        $this->subject->addTraversableEvent($generatorEventB);

        $this->assertSame($generatorEvents, $this->subject->traversableEvents());
    }

    public function testDurationMethodsWithGeneratorEvents()
    {
        $this->assertEquals(7, $this->generatorSubject->responseDuration());
        $this->assertNull($this->subjectWithNoResponse->duration());
    }

    public function testCheckProduced()
    {
        $this->assertTrue((boolean) $this->generatorSubject->checkProduced());
        $this->assertTrue((boolean) $this->generatorSubject->checkProduced('n'));
        $this->assertTrue((boolean) $this->generatorSubject->checkProduced('m', 'n'));
        $this->assertTrue((boolean) $this->generatorSubject->times(4)->checkProduced());
        $this->assertTrue((boolean) $this->generatorSubject->once()->checkProduced('n'));
        $this->assertTrue((boolean) $this->generatorSubject->never()->checkProduced('m'));
        $this->assertFalse((boolean) $this->generatorSubject->checkProduced('m'));
        $this->assertFalse((boolean) $this->generatorSubject->checkProduced('m', 'o'));
        $this->assertFalse((boolean) $this->subject->checkProduced());
    }

    public function testProduced()
    {
        $this->assertEquals(
            new CallEventCollection(
                array($this->generatorEventA, $this->generatorEventC, $this->generatorEventE, $this->generatorEventG)
            ),
            $this->generatorSubject->produced()
        );
        $this->assertEquals(
            new CallEventCollection(array($this->generatorEventA)),
            $this->generatorSubject->produced('n')
        );
        $this->assertEquals(
            new CallEventCollection(array($this->generatorEventA)),
            $this->generatorSubject->produced('m', 'n')
        );
        $this->assertEquals(
            new CallEventCollection(
                array($this->generatorEventA, $this->generatorEventC, $this->generatorEventE, $this->generatorEventG)
            ),
            $this->generatorSubject->times(4)->produced()
        );
        $this->assertEquals(
            new CallEventCollection(array($this->generatorEventA)),
            $this->generatorSubject->once()->produced('n')
        );
        $this->assertEquals(new CallEventCollection(), $this->generatorSubject->never()->produced('m'));
    }

    public function testProducedFailureWithNoMatchers()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected call to produce. Produced nothing."
        );
        $this->subject->produced();
    }

    public function testProducedFailureWithNoMatchersNever()
    {
        $expected = <<<'EOD'
Expected no call to produce. Produced:
    - produced 'm' => 'n'
    - received 'o'
    - produced 'p' => 'q'
    - received exception RuntimeException('Consequences will never be the same.')
    - produced 'r' => 's'
    - received 't'
    - produced 'u' => 'v'
    - received exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->generatorSubject->never()->produced();
    }

    public function testProducedFailureWithValueOnly()
    {
        $expected = <<<'EOD'
Expected call to produce like <'m'>. Produced:
    - produced 'm' => 'n'
    - received 'o'
    - produced 'p' => 'q'
    - received exception RuntimeException('Consequences will never be the same.')
    - produced 'r' => 's'
    - received 't'
    - produced 'u' => 'v'
    - received exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->generatorSubject->produced('m');
    }

    public function testProducedFailureWithValueOnlyNever()
    {
        $expected = <<<'EOD'
Expected no call to produce like <'n'>. Produced:
    - produced 'm' => 'n'
    - received 'o'
    - produced 'p' => 'q'
    - received exception RuntimeException('Consequences will never be the same.')
    - produced 'r' => 's'
    - received 't'
    - produced 'u' => 'v'
    - received exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->generatorSubject->never()->produced('n');
    }

    public function testProducedFailureWithValueOnlyAlways()
    {
        $expected = <<<'EOD'
Expected every call to produce like <'n'>. Produced:
    - produced 'm' => 'n'
    - received 'o'
    - produced 'p' => 'q'
    - received exception RuntimeException('Consequences will never be the same.')
    - produced 'r' => 's'
    - received 't'
    - produced 'u' => 'v'
    - received exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->generatorSubject->always()->produced('n');
    }

    public function testProducedFailureWithValueOnlyWithNoGeneratorEvents()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected call to produce like <'n'>. Produced nothing."
        );
        $this->subject->produced('n');
    }

    public function testProducedFailureWithKeyAndValue()
    {
        $expected = <<<'EOD'
Expected call to produce like <'m'> => <'o'>. Produced:
    - produced 'm' => 'n'
    - received 'o'
    - produced 'p' => 'q'
    - received exception RuntimeException('Consequences will never be the same.')
    - produced 'r' => 's'
    - received 't'
    - produced 'u' => 'v'
    - received exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->generatorSubject->produced('m', 'o');
    }

    public function testProducedFailureWithKeyAndValueNever()
    {
        $expected = <<<'EOD'
Expected no call to produce like <'m'> => <'n'>. Produced:
    - produced 'm' => 'n'
    - received 'o'
    - produced 'p' => 'q'
    - received exception RuntimeException('Consequences will never be the same.')
    - produced 'r' => 's'
    - received 't'
    - produced 'u' => 'v'
    - received exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->generatorSubject->never()->produced('m', 'n');
    }

    public function testProducedFailureWithKeyAndValueWithNoGeneratorEvents()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected call to produce like <'m'> => <'n'>. Produced nothing."
        );
        $this->subject->produced('m', 'n');
    }

    public function testCheckProducedAll()
    {
        $this->assertTrue((boolean) $this->subject->checkProducedAll());
        $this->assertTrue((boolean) $this->generatorSubject->checkProducedAll('n', 'q', 's', 'v'));
        $this->assertTrue(
            (boolean) $this->generatorSubject
                ->checkProducedAll('n', array('p', 'q'), 's', array('u', 'v'))
        );
        $this->assertTrue(
            (boolean) $this->generatorSubject
                ->checkProducedAll(array('m', 'n'), array('p', 'q'), array('r', 's'), array('u', 'v'))
        );
        $this->assertFalse((boolean) $this->generatorSubject->checkProducedAll('x', 'q', 's', 'v'));
        $this->assertFalse((boolean) $this->generatorSubject->checkProducedAll('n', 'q', 's', array('x', 'v')));
        $this->assertFalse((boolean) $this->generatorSubject->checkProducedAll('q', 's', 'v'));
        $this->assertFalse((boolean) $this->generatorSubject->checkProducedAll('n', 's', 'v'));
        $this->assertFalse((boolean) $this->generatorSubject->checkProducedAll('n', 'q', 's'));
        $this->assertFalse((boolean) $this->subject->never()->checkProducedAll());
        $this->assertTrue((boolean) $this->generatorSubject->never()->checkProducedAll());
        $this->assertTrue((boolean) $this->generatorSubject->never()->checkProducedAll('q', 's', 'v'));
        $this->assertTrue((boolean) $this->generatorSubject->never()->checkProducedAll('n', 's', 'v'));
        $this->assertTrue((boolean) $this->generatorSubject->never()->checkProducedAll('n', 'q', 's'));
    }

    public function testProducedAll()
    {
        $expected = new CallEventCollection(array($this->generatorEventG));

        $this->assertEquals($expected, $this->generatorSubject->producedAll('n', 'q', 's', 'v'));
        $this->assertEquals(
            $expected,
            $this->generatorSubject->producedAll('n', array('p', 'q'), 's', array('u', 'v'))
        );
        $this->assertEquals(
            $expected,
            $this->generatorSubject->producedAll(array('m', 'n'), array('p', 'q'), array('r', 's'), array('u', 'v'))
        );
        $this->assertEquals(new CallEventCollection(), $this->generatorSubject->never()->producedAll('q', 's', 'v'));
        $this->assertEquals(new CallEventCollection(), $this->generatorSubject->never()->producedAll('n', 's', 'v'));
        $this->assertEquals(new CallEventCollection(), $this->generatorSubject->never()->producedAll('n', 'q', 's'));
    }

    public function testProducedAllFailureNothingProduced()
    {
        $expected = <<<'EOD'
Expected call to produce like:
    - <'a'>
    - <'b'> => <'c'>
Produced nothing.
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->producedAll('a', array('b', 'c'));
    }

    public function testProducedAllFailureMismatch()
    {
        $expected = <<<'EOD'
Expected call to produce like:
    - <'a'>
    - <'b'> => <'c'>
Produced:
    - produced 'm' => 'n'
    - received 'o'
    - produced 'p' => 'q'
    - received exception RuntimeException('Consequences will never be the same.')
    - produced 'r' => 's'
    - received 't'
    - produced 'u' => 'v'
    - received exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->generatorSubject->producedAll('a', array('b', 'c'));
    }

    public function testProducedAllFailureMismatchNever()
    {
        $expected = <<<'EOD'
Expected no call to produce like:
    - <'n'>
    - <'q'>
    - <'s'>
    - <'v'>
Produced:
    - produced 'm' => 'n'
    - received 'o'
    - produced 'p' => 'q'
    - received exception RuntimeException('Consequences will never be the same.')
    - produced 'r' => 's'
    - received 't'
    - produced 'u' => 'v'
    - received exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->generatorSubject->never()->producedAll('n', 'q', 's', 'v');
    }

    public function testProducedAllFailureExpectedNothing()
    {
        $expected = <<<'EOD'
Expected call to produce nothing. Produced:
    - produced 'm' => 'n'
    - received 'o'
    - produced 'p' => 'q'
    - received exception RuntimeException('Consequences will never be the same.')
    - produced 'r' => 's'
    - received 't'
    - produced 'u' => 'v'
    - received exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->generatorSubject->producedAll();
    }

    public function testCheckReceived()
    {
        $this->assertTrue((boolean) $this->generatorSubject->checkReceived());
        $this->assertTrue((boolean) $this->generatorSubject->checkReceived('o'));
        $this->assertTrue((boolean) $this->generatorSubject->times(2)->checkReceived());
        $this->assertTrue((boolean) $this->generatorSubject->once()->checkReceived('o'));
        $this->assertTrue((boolean) $this->generatorSubject->never()->checkReceived('x'));
        $this->assertFalse((boolean) $this->generatorSubject->always()->checkReceived());
        $this->assertFalse((boolean) $this->generatorSubject->checkReceived('x'));
        $this->assertFalse((boolean) $this->subject->checkReceived());
    }

    public function testReceived()
    {
        $this->assertEquals(
            new CallEventCollection(array($this->generatorEventB, $this->generatorEventF)),
            $this->generatorSubject->received()
        );
        $this->assertEquals(
            new CallEventCollection(array($this->generatorEventB)),
            $this->generatorSubject->received('o')
        );
        $this->assertEquals(
            new CallEventCollection(array($this->generatorEventB, $this->generatorEventF)),
            $this->generatorSubject->times(2)->received()
        );
        $this->assertEquals(
            new CallEventCollection(array($this->generatorEventB)),
            $this->generatorSubject->once()->received('o')
        );
        $this->assertEquals(new CallEventCollection(), $this->generatorSubject->never()->received('x'));
    }

    public function testReceivedFailureNoMatcher()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected generator to receive value. Produced nothing."
        );
        $this->subject->received();
    }

    public function testReceivedFailureWithMatcher()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected generator to receive value like <'x'>. Produced nothing."
        );
        $this->subject->received('x');
    }

    public function testReceivedFailureWithNoMatchersNever()
    {
        $expected = <<<'EOD'
Expected no generator to receive value. Produced:
    - produced 'm' => 'n'
    - received 'o'
    - produced 'p' => 'q'
    - received exception RuntimeException('Consequences will never be the same.')
    - produced 'r' => 's'
    - received 't'
    - produced 'u' => 'v'
    - received exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->generatorSubject->never()->received();
    }

    public function testReceivedFailureWithMatcherNever()
    {
        $expected = <<<'EOD'
Expected no generator to receive value like <'o'>. Produced:
    - produced 'm' => 'n'
    - received 'o'
    - produced 'p' => 'q'
    - received exception RuntimeException('Consequences will never be the same.')
    - produced 'r' => 's'
    - received 't'
    - produced 'u' => 'v'
    - received exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->generatorSubject->never()->received('o');
    }

    public function testReceivedFailureWithNoMatcherAlways()
    {
        $expected = <<<'EOD'
Expected every generator to receive value. Produced:
    - produced 'm' => 'n'
    - received 'o'
    - produced 'p' => 'q'
    - received exception RuntimeException('Consequences will never be the same.')
    - produced 'r' => 's'
    - received 't'
    - produced 'u' => 'v'
    - received exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->generatorSubject->always()->received();
    }

    public function testReceivedFailureWithMatcherAlways()
    {
        $expected = <<<'EOD'
Expected every generator to receive value like <'o'>. Produced:
    - produced 'm' => 'n'
    - received 'o'
    - produced 'p' => 'q'
    - received exception RuntimeException('Consequences will never be the same.')
    - produced 'r' => 's'
    - received 't'
    - produced 'u' => 'v'
    - received exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->generatorSubject->always()->received('o');
    }

    public function testCheckReceivedException()
    {
        $this->assertTrue((boolean) $this->generatorSubject->checkReceivedException());
        $this->assertTrue((boolean) $this->generatorSubject->checkReceivedException('Exception'));
        $this->assertTrue((boolean) $this->generatorSubject->checkReceivedException('RuntimeException'));
        $this->assertTrue((boolean) $this->generatorSubject->checkReceivedException($this->receivedExceptionA));
        $this->assertTrue((boolean) $this->generatorSubject->checkReceivedException($this->receivedExceptionB));
        $this->assertTrue(
            (boolean) $this->generatorSubject->checkReceivedException(new EqualToMatcher($this->receivedExceptionA))
        );
        $this->assertFalse((boolean) $this->generatorSubject->checkReceivedException('InvalidArgumentException'));
        $this->assertFalse((boolean) $this->generatorSubject->checkReceivedException(new Exception()));
        $this->assertFalse((boolean) $this->generatorSubject->checkReceivedException(new RuntimeException()));
        $this->assertFalse(
            (boolean) $this->generatorSubject->checkReceivedException(new EqualToMatcher(new RuntimeException()))
        );
        $this->assertFalse((boolean) $this->generatorSubject->checkReceivedException(new EqualToMatcher(null)));
        $this->assertFalse((boolean) $this->generatorSubject->never()->checkReceivedException());
        $this->assertFalse((boolean) $this->generatorSubject->never()->checkReceivedException('Exception'));
        $this->assertFalse((boolean) $this->generatorSubject->never()->checkReceivedException('RuntimeException'));
        $this->assertFalse(
            (boolean) $this->generatorSubject->never()->checkReceivedException($this->receivedExceptionA)
        );
        $this->assertFalse(
            (boolean) $this->generatorSubject->never()
                ->checkReceivedException(new EqualToMatcher($this->receivedExceptionA))
        );
    }

    public function testCheckReceivedExceptionFailureInvalidInput()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            "Unable to match exceptions against 111."
        );
        $this->generatorSubject->checkReceivedException(111);
    }

    public function testCheckReceivedExceptionFailureInvalidInputObject()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            "Unable to match exceptions against stdClass Object ()."
        );
        $this->generatorSubject->checkReceivedException((object) array());
    }

    public function testReceivedException()
    {
        $this->assertEquals(
            new CallEventCollection(array($this->generatorEventD, $this->generatorEventH)),
            $this->generatorSubject->receivedException()
        );
        $this->assertEquals(
            new CallEventCollection(array($this->generatorEventD, $this->generatorEventH)),
            $this->generatorSubject->receivedException('Exception')
        );
        $this->assertEquals(
            new CallEventCollection(array($this->generatorEventD, $this->generatorEventH)),
            $this->generatorSubject->receivedException('RuntimeException')
        );
        $this->assertEquals(
            new CallEventCollection(array($this->generatorEventD)),
            $this->generatorSubject->receivedException($this->receivedExceptionA)
        );
        $this->assertEquals(
            new CallEventCollection(array($this->generatorEventH)),
            $this->generatorSubject->receivedException($this->receivedExceptionB)
        );
        $this->assertEquals(
            new CallEventCollection(array($this->generatorEventD)),
            $this->generatorSubject->receivedException(new EqualToMatcher($this->receivedExceptionA))
        );
    }

    public function testReceivedExceptionFailureExpectingAnyNoneReceived()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected generator to receive exception. Produced nothing."
        );
        $this->subject->receivedException();
    }

    public function testReceivedExceptionFailureExpectingAnyNoResponse()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected generator to receive exception. Produced nothing."
        );
        $this->subject->receivedException();
    }

    public function testReceivedExceptionFailureExpectingNeverAny()
    {
        $expected = <<<'EOD'
Expected no generator to receive exception. Produced:
    - produced 'm' => 'n'
    - received 'o'
    - produced 'p' => 'q'
    - received exception RuntimeException('Consequences will never be the same.')
    - produced 'r' => 's'
    - received 't'
    - produced 'u' => 'v'
    - received exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->generatorSubject->never()->receivedException();
    }

    public function testReceivedExceptionFailureExpectingAlwaysAny()
    {
        $expected = <<<'EOD'
Expected every generator to receive exception. Produced:
    - produced 'm' => 'n'
    - received 'o'
    - produced 'p' => 'q'
    - received exception RuntimeException('Consequences will never be the same.')
    - produced 'r' => 's'
    - received 't'
    - produced 'u' => 'v'
    - received exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->generatorSubject->always()->receivedException();
    }

    public function testReceivedExceptionFailureTypeMismatch()
    {
        $expected = <<<'EOD'
Expected generator to receive 'InvalidArgumentException' exception. Produced:
    - produced 'm' => 'n'
    - received 'o'
    - produced 'p' => 'q'
    - received exception RuntimeException('Consequences will never be the same.')
    - produced 'r' => 's'
    - received 't'
    - produced 'u' => 'v'
    - received exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->generatorSubject->receivedException('InvalidArgumentException');
    }

    public function testReceivedExceptionFailureTypeNever()
    {
        $expected = <<<'EOD'
Expected no generator to receive 'RuntimeException' exception. Produced:
    - produced 'm' => 'n'
    - received 'o'
    - produced 'p' => 'q'
    - received exception RuntimeException('Consequences will never be the same.')
    - produced 'r' => 's'
    - received 't'
    - produced 'u' => 'v'
    - received exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->generatorSubject->never()->receivedException('RuntimeException');
    }

    public function testReceivedExceptionFailureExpectingTypeNoneReceived()
    {
        $expected = <<<'EOD'
Expected generator to receive 'InvalidArgumentException' exception. Produced:
    - produced 'm' => 'n'
    - received 'o'
    - produced 'p' => 'q'
    - received exception RuntimeException('Consequences will never be the same.')
    - produced 'r' => 's'
    - received 't'
    - produced 'u' => 'v'
    - received exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->generatorSubject->receivedException('InvalidArgumentException');
    }

    public function testReceivedExceptionFailureExceptionMismatch()
    {
        $expected = <<<'EOD'
Expected generator to receive exception equal to RuntimeException(). Produced:
    - produced 'm' => 'n'
    - received 'o'
    - produced 'p' => 'q'
    - received exception RuntimeException('Consequences will never be the same.')
    - produced 'r' => 's'
    - received 't'
    - produced 'u' => 'v'
    - received exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->generatorSubject->receivedException(new RuntimeException());
    }

    public function testReceivedExceptionFailureExceptionNever()
    {
        $expected = <<<'EOD'
Expected no generator to receive exception equal to RuntimeException('Consequences will never be the same.'). Produced:
    - produced 'm' => 'n'
    - received 'o'
    - produced 'p' => 'q'
    - received exception RuntimeException('Consequences will never be the same.')
    - produced 'r' => 's'
    - received 't'
    - produced 'u' => 'v'
    - received exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->generatorSubject->never()->receivedException($this->receivedExceptionA);
    }

    public function testReceivedExceptionFailureExpectingExceptionNoneReceived()
    {
        $expected = <<<'EOD'
Expected generator to receive exception equal to RuntimeException(). Produced:
    - produced 'm' => 'n'
    - received 'o'
    - produced 'p' => 'q'
    - received exception RuntimeException('Consequences will never be the same.')
    - produced 'r' => 's'
    - received 't'
    - produced 'u' => 'v'
    - received exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->generatorSubject->receivedException(new RuntimeException());
    }

    public function testReceivedExceptionFailureMatcherMismatch()
    {
        $expected = <<<'EOD'
Expected generator to receive exception like <RuntimeException Object (...)>. Produced:
    - produced 'm' => 'n'
    - received 'o'
    - produced 'p' => 'q'
    - received exception RuntimeException('Consequences will never be the same.')
    - produced 'r' => 's'
    - received 't'
    - produced 'u' => 'v'
    - received exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->generatorSubject->receivedException(new EqualToMatcher(new RuntimeException()));
    }

    public function testReceivedExceptionFailureMatcherNever()
    {
        $expected = <<<'EOD'
Expected no generator to receive exception like <RuntimeException Object (...)>. Produced:
    - produced 'm' => 'n'
    - received 'o'
    - produced 'p' => 'q'
    - received exception RuntimeException('Consequences will never be the same.')
    - produced 'r' => 's'
    - received 't'
    - produced 'u' => 'v'
    - received exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->generatorSubject->never()->receivedException(new EqualToMatcher($this->receivedExceptionA));
    }

    public function testReceivedExceptionFailureInvalidInput()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            "Unable to match exceptions against 111."
        );
        $this->generatorSubject->receivedException(111);
    }

    public function testReceivedExceptionFailureInvalidInputObject()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            "Unable to match exceptions against stdClass Object ()."
        );
        $this->generatorSubject->receivedException((object) array());
    }
}
