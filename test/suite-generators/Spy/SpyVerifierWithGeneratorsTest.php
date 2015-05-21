<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Spy;

use Eloquent\Phony\Assertion\Recorder\AssertionRecorder;
use Eloquent\Phony\Assertion\Renderer\AssertionRenderer;
use Eloquent\Phony\Call\Call;
use Eloquent\Phony\Call\Event\CallEventCollection;
use Eloquent\Phony\Call\Factory\CallVerifierFactory;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Matcher\EqualToMatcher;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Test\TestCallFactory;
use Eloquent\Phony\Test\TestClassA;
use Exception;
use PHPUnit_Framework_TestCase;
use RuntimeException;

class SpyVerifierWithGeneratorsTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->callback = 'implode';
        $this->callFactory = new TestCallFactory();
        $this->label = 'label';
        $this->spy = new Spy($this->callback, $this->label, false, false, $this->callFactory);

        $this->matcherFactory = new MatcherFactory();
        $this->matcherVerifier = new MatcherVerifier();
        $this->callVerifierFactory = new CallVerifierFactory();
        $this->assertionRecorder = new AssertionRecorder();
        $this->assertionRenderer = new AssertionRenderer();
        $this->invocableInspector = new InvocableInspector();
        $this->subject = new SpyVerifier(
            $this->spy,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );

        $this->callEventFactory = $this->callFactory->eventFactory();

        $this->returnValueA = 'x';
        $this->returnValueB = 'y';
        $this->exceptionA = new RuntimeException('You done goofed.');
        $this->exceptionB = new RuntimeException('Consequences will never be the same.');
        $this->thisValueA = new TestClassA();
        $this->thisValueB = new TestClassA();
        $this->arguments = array('a', 'b', 'c');
        $this->matchers = $this->matcherFactory->adaptAll($this->arguments);
        $this->otherMatcher = $this->matcherFactory->adapt('d');
        $this->callA = $this->callFactory->create(
            $this->callEventFactory->createCalled(array($this->thisValueA, 'testClassAMethodA'), $this->arguments),
            $this->callEventFactory->createReturned($this->returnValueA)
        );
        $this->callAResponse = $this->callA->responseEvent();
        $this->callB = $this->callFactory->create(
            $this->callEventFactory->createCalled(array($this->thisValueB, 'testClassAMethodA')),
            $this->callEventFactory->createReturned($this->returnValueB)
        );
        $this->callBResponse = $this->callB->responseEvent();
        $this->callC = $this->callFactory->create(
            $this->callEventFactory->createCalled(array($this->thisValueA, 'testClassAMethodA'), $this->arguments),
            $this->callEventFactory->createThrew($this->exceptionA)
        );
        $this->callCResponse = $this->callC->responseEvent();
        $this->callD = $this->callFactory->create(
            $this->callEventFactory->createCalled('implode'),
            $this->callEventFactory->createThrew($this->exceptionB)
        );
        $this->callDResponse = $this->callD->responseEvent();
        $this->calls = array($this->callA, $this->callB, $this->callC, $this->callD);
        $this->wrappedCallA = $this->callVerifierFactory->adapt($this->callA);
        $this->wrappedCallB = $this->callVerifierFactory->adapt($this->callB);
        $this->wrappedCallC = $this->callVerifierFactory->adapt($this->callC);
        $this->wrappedCallD = $this->callVerifierFactory->adapt($this->callD);
        $this->wrappedCalls = array($this->wrappedCallA, $this->wrappedCallB, $this->wrappedCallC, $this->wrappedCallD);

        $this->callFactory->reset();

        // additions for generators

        $this->receivedExceptionA = new RuntimeException('Consequences will never be the same.');
        $this->receivedExceptionB = new RuntimeException('Because I backtraced it.');
        $this->generatorCalledEvent = $this->callEventFactory->createCalled();
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
            $this->generatorCalledEvent,
            $this->generatedEvent,
            $this->generatorEvents,
            $this->generatorEndEvent
        );
    }

    public function testCheckProduced()
    {
        $this->assertFalse((boolean) $this->subject->checkProduced());
        $this->assertFalse((boolean) $this->subject->checkProduced('n'));
        $this->assertFalse((boolean) $this->subject->checkProduced('m', 'n'));
        $this->assertFalse((boolean) $this->subject->times(1)->checkProduced());
        $this->assertFalse((boolean) $this->subject->once()->checkProduced('n'));
        $this->assertTrue((boolean) $this->subject->never()->checkProduced('m'));
        $this->assertFalse((boolean) $this->subject->checkProduced('m'));
        $this->assertFalse((boolean) $this->subject->checkProduced('m', 'o'));

        $this->subject->setCalls($this->calls);

        $this->assertFalse((boolean) $this->subject->checkProduced());
        $this->assertFalse((boolean) $this->subject->checkProduced('n'));
        $this->assertFalse((boolean) $this->subject->checkProduced('m', 'n'));
        $this->assertFalse((boolean) $this->subject->times(1)->checkProduced());
        $this->assertFalse((boolean) $this->subject->once()->checkProduced('n'));
        $this->assertTrue((boolean) $this->subject->never()->checkProduced('m'));
        $this->assertFalse((boolean) $this->subject->checkProduced('m'));
        $this->assertFalse((boolean) $this->subject->checkProduced('m', 'o'));

        $this->subject->addCall($this->generatorCall);

        $this->assertTrue((boolean) $this->subject->checkProduced());
        $this->assertTrue((boolean) $this->subject->checkProduced('n'));
        $this->assertTrue((boolean) $this->subject->checkProduced('m', 'n'));
        $this->assertTrue((boolean) $this->subject->times(4)->checkProduced());
        $this->assertTrue((boolean) $this->subject->once()->checkProduced('n'));
        $this->assertTrue((boolean) $this->subject->never()->checkProduced('m'));
        $this->assertFalse((boolean) $this->subject->checkProduced('m'));
        $this->assertFalse((boolean) $this->subject->checkProduced('m', 'o'));
        $this->assertFalse((boolean) $this->subject->always()->checkProduced());

        $this->subject->setCalls(array($this->generatorCall));

        $this->assertTrue((boolean) $this->subject->always()->checkProduced());
    }

    public function testProduced()
    {
        $this->subject->setCalls($this->calls);
        $this->subject->addCall($this->generatorCall);

        $this->assertEquals(
            new CallEventCollection(
                array($this->generatorEventA, $this->generatorEventC, $this->generatorEventE, $this->generatorEventG)
            ),
            $this->subject->produced()
        );
        $this->assertEquals(new CallEventCollection(array($this->generatorEventA)), $this->subject->produced('n'));
        $this->assertEquals(new CallEventCollection(array($this->generatorEventA)), $this->subject->produced('m', 'n'));
        $this->assertEquals(
            new CallEventCollection(
                array($this->generatorEventA, $this->generatorEventC, $this->generatorEventE, $this->generatorEventG)
            ),
            $this->subject->times(4)->produced()
        );
        $this->assertEquals(
            new CallEventCollection(array($this->generatorEventA)),
            $this->subject->once()->produced('n')
        );
        $this->assertEquals(new CallEventCollection(), $this->subject->never()->produced('m'));

        $this->subject->setCalls(array($this->generatorCall));

        $this->assertEquals(
            new CallEventCollection(
                array($this->generatorEventA, $this->generatorEventC, $this->generatorEventE, $this->generatorEventG)
            ),
            $this->subject->always()->produced()
        );
    }

    public function testProducedFailureNoCallsNoMatchers()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected call on implode[label] to produce. Never called.'
        );
        $this->subject->produced();
    }

    public function testProducedFailureNoCallsValueOnly()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected call on implode[label] to produce like "x". Never called.'
        );
        $this->subject->produced('x');
    }

    public function testProducedFailureNoCallsKeyAndValue()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected call on implode[label] to produce like "x": "y". Never called.'
        );
        $this->subject->produced('x', 'y');
    }

    public function testProducedFailureNoGeneratorsNoMatchers()
    {
        $this->subject->setCalls($this->calls);
        $expected = <<<'EOD'
Expected call on implode[label] to produce. Responded:
    - returned "x"
    - returned "y"
    - threw RuntimeException("You done goofed.")
    - threw RuntimeException("Consequences will never be the same.")
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->produced();
    }

    public function testProducedFailureNoGeneratorsValueOnly()
    {
        $this->subject->setCalls($this->calls);
        $expected = <<<'EOD'
Expected call on implode[label] to produce like "x". Responded:
    - returned "x"
    - returned "y"
    - threw RuntimeException("You done goofed.")
    - threw RuntimeException("Consequences will never be the same.")
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->produced('x');
    }

    public function testProducedFailureNoGeneratorsKeyAndValue()
    {
        $this->subject->setCalls($this->calls);
        $expected = <<<'EOD'
Expected call on implode[label] to produce like "x": "y". Responded:
    - returned "x"
    - returned "y"
    - threw RuntimeException("You done goofed.")
    - threw RuntimeException("Consequences will never be the same.")
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->produced('x', 'y');
    }

    public function testProducedFailureValueMismatch()
    {
        $this->subject->setCalls($this->calls);
        $this->subject->addCall($this->generatorCall);
        $expected = <<<'EOD'
Expected call on implode[label] to produce like "x". Responded:
    - returned "x"
    - returned "y"
    - threw RuntimeException("You done goofed.")
    - threw RuntimeException("Consequences will never be the same.")
    - generated:
        - produced "m": "n"
        - received "o"
        - produced "p": "q"
        - received exception RuntimeException("Consequences will never be the same.")
        - produced "r": "s"
        - received "t"
        - produced "u": "v"
        - received exception RuntimeException("Because I backtraced it.")
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->produced('x');
    }

    public function testProducedFailureKeyValueMismatch()
    {
        $this->subject->setCalls($this->calls);
        $this->subject->addCall($this->generatorCall);
        $expected = <<<'EOD'
Expected call on implode[label] to produce like "x": "y". Responded:
    - returned "x"
    - returned "y"
    - threw RuntimeException("You done goofed.")
    - threw RuntimeException("Consequences will never be the same.")
    - generated:
        - produced "m": "n"
        - received "o"
        - produced "p": "q"
        - received exception RuntimeException("Consequences will never be the same.")
        - produced "r": "s"
        - received "t"
        - produced "u": "v"
        - received exception RuntimeException("Because I backtraced it.")
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->produced('x', 'y');
    }

    public function testProducedFailureAlways()
    {
        $this->subject->setCalls($this->calls);
        $this->subject->addCall($this->generatorCall);
        $expected = <<<'EOD'
Expected every call on implode[label] to produce like "n". Responded:
    - returned "x"
    - returned "y"
    - threw RuntimeException("You done goofed.")
    - threw RuntimeException("Consequences will never be the same.")
    - generated:
        - produced "m": "n"
        - received "o"
        - produced "p": "q"
        - received exception RuntimeException("Consequences will never be the same.")
        - produced "r": "s"
        - received "t"
        - produced "u": "v"
        - received exception RuntimeException("Because I backtraced it.")
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->always()->produced('n');
    }

    public function testProducedFailureNever()
    {
        $this->subject->setCalls($this->calls);
        $this->subject->addCall($this->generatorCall);
        $expected = <<<'EOD'
Expected no call on implode[label] to produce like "n". Responded:
    - returned "x"
    - returned "y"
    - threw RuntimeException("You done goofed.")
    - threw RuntimeException("Consequences will never be the same.")
    - generated:
        - produced "m": "n"
        - received "o"
        - produced "p": "q"
        - received exception RuntimeException("Consequences will never be the same.")
        - produced "r": "s"
        - received "t"
        - produced "u": "v"
        - received exception RuntimeException("Because I backtraced it.")
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->never()->produced('n');
    }

    public function testCheckProducedAll()
    {
        $this->assertFalse((boolean) $this->subject->checkProducedAll());
        $this->assertFalse((boolean) $this->subject->checkProducedAll('n', 'q', 's', 'v'));
        $this->assertFalse(
            (boolean) $this->subject
                ->checkProducedAll('n', array('p', 'q'), 's', array('u', 'v'))
        );
        $this->assertFalse(
            (boolean) $this->subject
                ->checkProducedAll(array('m', 'n'), array('p', 'q'), array('r', 's'), array('u', 'v'))
        );
        $this->assertFalse((boolean) $this->subject->checkProducedAll('x', 'q', 's', 'v'));
        $this->assertFalse((boolean) $this->subject->checkProducedAll('n', 'q', 's', array('x', 'v')));
        $this->assertFalse((boolean) $this->subject->checkProducedAll('q', 's', 'v'));
        $this->assertFalse((boolean) $this->subject->checkProducedAll('n', 's', 'v'));
        $this->assertFalse((boolean) $this->subject->checkProducedAll('n', 'q', 's'));
        $this->assertTrue((boolean) $this->subject->never()->checkProducedAll());
        $this->assertTrue((boolean) $this->subject->never()->checkProducedAll('q', 's', 'v'));
        $this->assertTrue((boolean) $this->subject->never()->checkProducedAll('n', 's', 'v'));
        $this->assertTrue((boolean) $this->subject->never()->checkProducedAll('n', 'q', 's'));

        $this->subject->setCalls($this->calls);

        $this->assertTrue((boolean) $this->subject->checkProducedAll());
        $this->assertFalse((boolean) $this->subject->checkProducedAll('n', 'q', 's', 'v'));
        $this->assertFalse(
            (boolean) $this->subject
                ->checkProducedAll('n', array('p', 'q'), 's', array('u', 'v'))
        );
        $this->assertFalse(
            (boolean) $this->subject
                ->checkProducedAll(array('m', 'n'), array('p', 'q'), array('r', 's'), array('u', 'v'))
        );
        $this->assertFalse((boolean) $this->subject->checkProducedAll('x', 'q', 's', 'v'));
        $this->assertFalse((boolean) $this->subject->checkProducedAll('n', 'q', 's', array('x', 'v')));
        $this->assertFalse((boolean) $this->subject->checkProducedAll('q', 's', 'v'));
        $this->assertFalse((boolean) $this->subject->checkProducedAll('n', 's', 'v'));
        $this->assertFalse((boolean) $this->subject->checkProducedAll('n', 'q', 's'));
        $this->assertFalse((boolean) $this->subject->never()->checkProducedAll());
        $this->assertTrue((boolean) $this->subject->never()->checkProducedAll('q', 's', 'v'));
        $this->assertTrue((boolean) $this->subject->never()->checkProducedAll('n', 's', 'v'));
        $this->assertTrue((boolean) $this->subject->never()->checkProducedAll('n', 'q', 's'));

        $this->subject->addCall($this->generatorCall);

        $this->assertTrue((boolean) $this->subject->checkProducedAll());
        $this->assertTrue((boolean) $this->subject->checkProducedAll('n', 'q', 's', 'v'));
        $this->assertTrue(
            (boolean) $this->subject
                ->checkProducedAll('n', array('p', 'q'), 's', array('u', 'v'))
        );
        $this->assertTrue(
            (boolean) $this->subject
                ->checkProducedAll(array('m', 'n'), array('p', 'q'), array('r', 's'), array('u', 'v'))
        );
        $this->assertFalse((boolean) $this->subject->checkProducedAll('x', 'q', 's', 'v'));
        $this->assertFalse((boolean) $this->subject->checkProducedAll('n', 'q', 's', array('x', 'v')));
        $this->assertFalse((boolean) $this->subject->checkProducedAll('q', 's', 'v'));
        $this->assertFalse((boolean) $this->subject->checkProducedAll('n', 's', 'v'));
        $this->assertFalse((boolean) $this->subject->checkProducedAll('n', 'q', 's'));
        $this->assertFalse((boolean) $this->subject->never()->checkProducedAll());
        $this->assertTrue((boolean) $this->subject->never()->checkProducedAll('q', 's', 'v'));
        $this->assertTrue((boolean) $this->subject->never()->checkProducedAll('n', 's', 'v'));
        $this->assertTrue((boolean) $this->subject->never()->checkProducedAll('n', 'q', 's'));
    }

    public function testProducedAll()
    {
        $this->subject->setCalls($this->calls);
        $this->subject->addCall($this->generatorCall);

        $this->assertEquals(
            new CallEventCollection(
                array($this->callAResponse, $this->callBResponse, $this->callCResponse, $this->callDResponse)
            ),
            $this->subject->producedAll()
        );
        $this->assertEquals(
            new CallEventCollection(array($this->generatorEventG)),
            $this->subject->producedAll('n', 'q', 's', 'v')
        );
        $this->assertEquals(
            new CallEventCollection(array($this->generatorEventG)),
            $this->subject->producedAll('n', array('p', 'q'), 's', array('u', 'v'))
        );
        $this->assertEquals(
            new CallEventCollection(array($this->generatorEventG)),
            $this->subject->producedAll(array('m', 'n'), array('p', 'q'), array('r', 's'), array('u', 'v'))
        );
        $this->assertEquals(new CallEventCollection(), $this->subject->never()->producedAll('q', 's', 'v'));
        $this->assertEquals(new CallEventCollection(), $this->subject->never()->producedAll('n', 's', 'v'));
        $this->assertEquals(new CallEventCollection(), $this->subject->never()->producedAll('n', 'q', 's'));
    }

    public function testProducedAllFailureNeverCalled()
    {
        $expected = <<<'EOD'
Expected call on implode[label] to produce like:
    - "a"
    - "b": "c"
Never called.
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->producedAll('a', array('b', 'c'));
    }

    public function testProducedAllFailureNothingProduced()
    {
        $this->subject->setCalls($this->calls);
        $expected = <<<'EOD'
Expected call on implode[label] to produce like:
    - "a"
    - "b": "c"
Responded:
    - returned "x"
    - returned "y"
    - threw RuntimeException("You done goofed.")
    - threw RuntimeException("Consequences will never be the same.")
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->producedAll('a', array('b', 'c'));
    }

    public function testProducedAllFailureMismatch()
    {
        $this->subject->setCalls($this->calls);
        $this->subject->addCall($this->generatorCall);
        $expected = <<<'EOD'
Expected call on implode[label] to produce like:
    - "a"
    - "b": "c"
Responded:
    - returned "x"
    - returned "y"
    - threw RuntimeException("You done goofed.")
    - threw RuntimeException("Consequences will never be the same.")
    - generated:
        - produced "m": "n"
        - received "o"
        - produced "p": "q"
        - received exception RuntimeException("Consequences will never be the same.")
        - produced "r": "s"
        - received "t"
        - produced "u": "v"
        - received exception RuntimeException("Because I backtraced it.")
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->producedAll('a', array('b', 'c'));
    }

    public function testProducedAllFailureMismatchNever()
    {
        $this->subject->setCalls($this->calls);
        $this->subject->addCall($this->generatorCall);
        $expected = <<<'EOD'
Expected no call on implode[label] to produce like:
    - "n"
    - "q"
    - "s"
    - "v"
Responded:
    - returned "x"
    - returned "y"
    - threw RuntimeException("You done goofed.")
    - threw RuntimeException("Consequences will never be the same.")
    - generated:
        - produced "m": "n"
        - received "o"
        - produced "p": "q"
        - received exception RuntimeException("Consequences will never be the same.")
        - produced "r": "s"
        - received "t"
        - produced "u": "v"
        - received exception RuntimeException("Because I backtraced it.")
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->never()->producedAll('n', 'q', 's', 'v');
    }

    public function testProducedAllFailureExpectedNothing()
    {
        $this->subject->addCall($this->generatorCall);
        $expected = <<<'EOD'
Expected call on implode[label] to produce nothing. Responded:
    - generated:
        - produced "m": "n"
        - received "o"
        - produced "p": "q"
        - received exception RuntimeException("Consequences will never be the same.")
        - produced "r": "s"
        - received "t"
        - produced "u": "v"
        - received exception RuntimeException("Because I backtraced it.")
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->producedAll();
    }

    public function testCheckReceived()
    {
        $this->assertFalse((boolean) $this->subject->checkReceived());
        $this->assertFalse((boolean) $this->subject->checkReceived('o'));
        $this->assertFalse((boolean) $this->subject->times(1)->checkReceived());
        $this->assertFalse((boolean) $this->subject->once()->checkReceived('o'));
        $this->assertTrue((boolean) $this->subject->never()->checkReceived('x'));
        $this->assertFalse((boolean) $this->subject->always()->checkReceived());
        $this->assertFalse((boolean) $this->subject->checkReceived('x'));

        $this->subject->setCalls($this->calls);

        $this->assertFalse((boolean) $this->subject->checkReceived());
        $this->assertFalse((boolean) $this->subject->checkReceived('o'));
        $this->assertFalse((boolean) $this->subject->times(1)->checkReceived());
        $this->assertFalse((boolean) $this->subject->once()->checkReceived('o'));
        $this->assertTrue((boolean) $this->subject->never()->checkReceived('x'));
        $this->assertFalse((boolean) $this->subject->always()->checkReceived());
        $this->assertFalse((boolean) $this->subject->checkReceived('x'));

        $this->subject->addCall($this->generatorCall);

        $this->assertTrue((boolean) $this->subject->checkReceived());
        $this->assertTrue((boolean) $this->subject->checkReceived('o'));
        $this->assertTrue((boolean) $this->subject->times(2)->checkReceived());
        $this->assertTrue((boolean) $this->subject->once()->checkReceived('o'));
        $this->assertTrue((boolean) $this->subject->never()->checkReceived('x'));
        $this->assertFalse((boolean) $this->subject->always()->checkReceived());
        $this->assertFalse((boolean) $this->subject->checkReceived('x'));
    }

    public function testReceived()
    {
        $this->subject->setCalls($this->calls);
        $this->subject->addCall($this->generatorCall);

        $this->assertEquals(
            new CallEventCollection(array($this->generatorEventB, $this->generatorEventF)),
            $this->subject->received()
        );
        $this->assertEquals(new CallEventCollection(array($this->generatorEventB)), $this->subject->received('o'));
        $this->assertEquals(
            new CallEventCollection(array($this->generatorEventB, $this->generatorEventF)),
            $this->subject->times(2)->received()
        );
        $this->assertEquals(
            new CallEventCollection(array($this->generatorEventB)),
            $this->subject->once()->received('o')
        );
        $this->assertEquals(new CallEventCollection(), $this->subject->never()->received('x'));
    }

    public function testReceivedFailureNoCallsNoMatcher()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected generator returned by implode[label] to receive value. Never called.'
        );
        $this->subject->received();
    }

    public function testReceivedFailureNoCallsWithMatcher()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected generator returned by implode[label] to receive value like "x". Never called.'
        );
        $this->subject->received('x');
    }

    public function testReceivedFailureNoGeneratorsNoMatcher()
    {
        $this->subject->setCalls($this->calls);
        $expected = <<<'EOD'
Expected generator returned by implode[label] to receive value. Responded:
    - returned "x"
    - returned "y"
    - threw RuntimeException("You done goofed.")
    - threw RuntimeException("Consequences will never be the same.")
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->received();
    }

    public function testReceivedFailureNoGeneratorsWithMatcher()
    {
        $this->subject->setCalls($this->calls);
        $expected = <<<'EOD'
Expected generator returned by implode[label] to receive value like "x". Responded:
    - returned "x"
    - returned "y"
    - threw RuntimeException("You done goofed.")
    - threw RuntimeException("Consequences will never be the same.")
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->received('x');
    }

    public function testReceivedFailureMatcherMismatch()
    {
        $this->subject->setCalls($this->calls);
        $this->subject->addCall($this->generatorCall);
        $expected = <<<'EOD'
Expected generator returned by implode[label] to receive value like "x". Responded:
    - returned "x"
    - returned "y"
    - threw RuntimeException("You done goofed.")
    - threw RuntimeException("Consequences will never be the same.")
    - generated:
        - produced "m": "n"
        - received "o"
        - produced "p": "q"
        - received exception RuntimeException("Consequences will never be the same.")
        - produced "r": "s"
        - received "t"
        - produced "u": "v"
        - received exception RuntimeException("Because I backtraced it.")
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->received('x');
    }

    public function testCheckReceivedException()
    {
        $this->assertFalse((boolean) $this->subject->checkReceivedException());
        $this->assertFalse((boolean) $this->subject->checkReceivedException('Exception'));
        $this->assertFalse((boolean) $this->subject->checkReceivedException('RuntimeException'));
        $this->assertFalse((boolean) $this->subject->checkReceivedException($this->receivedExceptionA));
        $this->assertFalse((boolean) $this->subject->checkReceivedException($this->receivedExceptionB));
        $this->assertFalse(
            (boolean) $this->subject->checkReceivedException(new EqualToMatcher($this->receivedExceptionA))
        );
        $this->assertFalse((boolean) $this->subject->checkReceivedException('InvalidArgumentException'));
        $this->assertFalse((boolean) $this->subject->checkReceivedException(new Exception()));
        $this->assertFalse((boolean) $this->subject->checkReceivedException(new RuntimeException()));
        $this->assertFalse(
            (boolean) $this->subject->checkReceivedException(new EqualToMatcher(new RuntimeException()))
        );
        $this->assertFalse((boolean) $this->subject->checkReceivedException(new EqualToMatcher(null)));
        $this->assertTrue((boolean) $this->subject->never()->checkReceivedException());
        $this->assertTrue((boolean) $this->subject->never()->checkReceivedException('Exception'));
        $this->assertTrue((boolean) $this->subject->never()->checkReceivedException('RuntimeException'));
        $this->assertTrue((boolean) $this->subject->never()->checkReceivedException($this->receivedExceptionA));
        $this->assertTrue(
            (boolean) $this->subject->never()->checkReceivedException(new EqualToMatcher($this->receivedExceptionA))
        );

        $this->subject->setCalls($this->calls);

        $this->assertFalse((boolean) $this->subject->checkReceivedException());
        $this->assertFalse((boolean) $this->subject->checkReceivedException('Exception'));
        $this->assertFalse((boolean) $this->subject->checkReceivedException('RuntimeException'));
        $this->assertFalse((boolean) $this->subject->checkReceivedException($this->receivedExceptionA));
        $this->assertFalse((boolean) $this->subject->checkReceivedException($this->receivedExceptionB));
        $this->assertFalse(
            (boolean) $this->subject->checkReceivedException(new EqualToMatcher($this->receivedExceptionA))
        );
        $this->assertFalse((boolean) $this->subject->checkReceivedException('InvalidArgumentException'));
        $this->assertFalse((boolean) $this->subject->checkReceivedException(new Exception()));
        $this->assertFalse((boolean) $this->subject->checkReceivedException(new RuntimeException()));
        $this->assertFalse(
            (boolean) $this->subject->checkReceivedException(new EqualToMatcher(new RuntimeException()))
        );
        $this->assertFalse((boolean) $this->subject->checkReceivedException(new EqualToMatcher(null)));
        $this->assertTrue((boolean) $this->subject->never()->checkReceivedException());
        $this->assertTrue((boolean) $this->subject->never()->checkReceivedException('Exception'));
        $this->assertTrue((boolean) $this->subject->never()->checkReceivedException('RuntimeException'));
        $this->assertTrue((boolean) $this->subject->never()->checkReceivedException($this->receivedExceptionA));
        $this->assertTrue(
            (boolean) $this->subject->never()->checkReceivedException(new EqualToMatcher($this->receivedExceptionA))
        );

        $this->subject->addCall($this->generatorCall);

        $this->assertTrue((boolean) $this->subject->checkReceivedException());
        $this->assertTrue((boolean) $this->subject->checkReceivedException('Exception'));
        $this->assertTrue((boolean) $this->subject->checkReceivedException('RuntimeException'));
        $this->assertTrue((boolean) $this->subject->checkReceivedException($this->receivedExceptionA));
        $this->assertTrue((boolean) $this->subject->checkReceivedException($this->receivedExceptionB));
        $this->assertTrue(
            (boolean) $this->subject->checkReceivedException(new EqualToMatcher($this->receivedExceptionA))
        );
        $this->assertFalse((boolean) $this->subject->checkReceivedException('InvalidArgumentException'));
        $this->assertFalse((boolean) $this->subject->checkReceivedException(new Exception()));
        $this->assertFalse((boolean) $this->subject->checkReceivedException(new RuntimeException()));
        $this->assertFalse(
            (boolean) $this->subject->checkReceivedException(new EqualToMatcher(new RuntimeException()))
        );
        $this->assertFalse((boolean) $this->subject->checkReceivedException(new EqualToMatcher(null)));
        $this->assertFalse((boolean) $this->subject->never()->checkReceivedException());
        $this->assertFalse((boolean) $this->subject->never()->checkReceivedException('Exception'));
        $this->assertFalse((boolean) $this->subject->never()->checkReceivedException('RuntimeException'));
        $this->assertFalse((boolean) $this->subject->never()->checkReceivedException($this->receivedExceptionA));
        $this->assertFalse(
            (boolean) $this->subject->never()->checkReceivedException(new EqualToMatcher($this->receivedExceptionA))
        );
    }

    public function testReceivedException()
    {
        $this->subject->setCalls($this->calls);
        $this->subject->addCall($this->generatorCall);

        $this->assertEquals(
            new CallEventCollection(array($this->generatorEventD, $this->generatorEventH)),
            $this->subject->receivedException()
        );
        $this->assertEquals(
            new CallEventCollection(array($this->generatorEventD, $this->generatorEventH)),
            $this->subject->receivedException('Exception')
        );
        $this->assertEquals(
            new CallEventCollection(array($this->generatorEventD, $this->generatorEventH)),
            $this->subject->receivedException('RuntimeException')
        );
        $this->assertEquals(
            new CallEventCollection(array($this->generatorEventD)),
            $this->subject->receivedException($this->receivedExceptionA)
        );
        $this->assertEquals(
            new CallEventCollection(array($this->generatorEventH)),
            $this->subject->receivedException($this->receivedExceptionB)
        );
        $this->assertEquals(
            new CallEventCollection(array($this->generatorEventD)),
            $this->subject->receivedException(new EqualToMatcher($this->receivedExceptionA))
        );
        $this->assertEquals(
            new CallEventCollection(),
            $this->subject->never()->receivedException('InvalidArgumentException')
        );
    }

    public function testReceivedExceptionFailureNoCallsNoType()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected generator returned by implode[label] to receive exception. Never called.'
        );
        $this->subject->receivedException();
    }

    public function testReceivedExceptionFailureNoCallsWithType()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected generator returned by implode[label] to receive InvalidArgumentException exception. ' .
                'Never called.'
        );
        $this->subject->receivedException('InvalidArgumentException');
    }

    public function testReceivedExceptionFailureNoCallsWithException()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected generator returned by implode[label] to receive exception equal to RuntimeException(). ' .
                'Never called.'
        );
        $this->subject->receivedException(new RuntimeException());
    }

    public function testReceivedExceptionFailureNoCallsWithMatcher()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            'Expected generator returned by implode[label] to receive exception like ' .
                'RuntimeException#0{}. Never called.'
        );
        $this->subject->receivedException(new EqualToMatcher(new RuntimeException()));
    }

    public function testReceivedExceptionFailureExpectingNeverAny()
    {
        $this->subject->setCalls($this->calls);
        $this->subject->addCall($this->generatorCall);
        $expected = <<<'EOD'
Expected no generator returned by implode[label] to receive exception. Responded:
    - returned "x"
    - returned "y"
    - threw RuntimeException("You done goofed.")
    - threw RuntimeException("Consequences will never be the same.")
    - generated:
        - produced "m": "n"
        - received "o"
        - produced "p": "q"
        - received exception RuntimeException("Consequences will never be the same.")
        - produced "r": "s"
        - received "t"
        - produced "u": "v"
        - received exception RuntimeException("Because I backtraced it.")
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->never()->receivedException();
    }

    public function testReceivedExceptionFailureExpectingAlwaysAny()
    {
        $this->subject->setCalls($this->calls);
        $this->subject->addCall($this->generatorCall);
        $expected = <<<'EOD'
Expected every generator returned by implode[label] to receive exception. Responded:
    - returned "x"
    - returned "y"
    - threw RuntimeException("You done goofed.")
    - threw RuntimeException("Consequences will never be the same.")
    - generated:
        - produced "m": "n"
        - received "o"
        - produced "p": "q"
        - received exception RuntimeException("Consequences will never be the same.")
        - produced "r": "s"
        - received "t"
        - produced "u": "v"
        - received exception RuntimeException("Because I backtraced it.")
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->always()->receivedException();
    }

    public function testReceivedExceptionFailureTypeMismatch()
    {
        $this->subject->setCalls($this->calls);
        $this->subject->addCall($this->generatorCall);
        $expected = <<<'EOD'
Expected generator returned by implode[label] to receive InvalidArgumentException exception. Responded:
    - returned "x"
    - returned "y"
    - threw RuntimeException("You done goofed.")
    - threw RuntimeException("Consequences will never be the same.")
    - generated:
        - produced "m": "n"
        - received "o"
        - produced "p": "q"
        - received exception RuntimeException("Consequences will never be the same.")
        - produced "r": "s"
        - received "t"
        - produced "u": "v"
        - received exception RuntimeException("Because I backtraced it.")
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->receivedException('InvalidArgumentException');
    }

    public function testReceivedExceptionFailureTypeNever()
    {
        $this->subject->setCalls($this->calls);
        $this->subject->addCall($this->generatorCall);
        $expected = <<<'EOD'
Expected no generator returned by implode[label] to receive RuntimeException exception. Responded:
    - returned "x"
    - returned "y"
    - threw RuntimeException("You done goofed.")
    - threw RuntimeException("Consequences will never be the same.")
    - generated:
        - produced "m": "n"
        - received "o"
        - produced "p": "q"
        - received exception RuntimeException("Consequences will never be the same.")
        - produced "r": "s"
        - received "t"
        - produced "u": "v"
        - received exception RuntimeException("Because I backtraced it.")
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->never()->receivedException('RuntimeException');
    }

    public function testReceivedExceptionFailureExpectingTypeNoneReceived()
    {
        $this->subject->setCalls($this->calls);
        $this->subject->addCall($this->generatorCall);
        $expected = <<<'EOD'
Expected generator returned by implode[label] to receive InvalidArgumentException exception. Responded:
    - returned "x"
    - returned "y"
    - threw RuntimeException("You done goofed.")
    - threw RuntimeException("Consequences will never be the same.")
    - generated:
        - produced "m": "n"
        - received "o"
        - produced "p": "q"
        - received exception RuntimeException("Consequences will never be the same.")
        - produced "r": "s"
        - received "t"
        - produced "u": "v"
        - received exception RuntimeException("Because I backtraced it.")
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->receivedException('InvalidArgumentException');
    }

    public function testReceivedExceptionFailureExceptionMismatch()
    {
        $this->subject->setCalls($this->calls);
        $this->subject->addCall($this->generatorCall);
        $expected = <<<'EOD'
Expected generator returned by implode[label] to receive exception equal to RuntimeException(). Responded:
    - returned "x"
    - returned "y"
    - threw RuntimeException("You done goofed.")
    - threw RuntimeException("Consequences will never be the same.")
    - generated:
        - produced "m": "n"
        - received "o"
        - produced "p": "q"
        - received exception RuntimeException("Consequences will never be the same.")
        - produced "r": "s"
        - received "t"
        - produced "u": "v"
        - received exception RuntimeException("Because I backtraced it.")
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->receivedException(new RuntimeException());
    }

    public function testReceivedExceptionFailureExceptionNever()
    {
        $this->subject->setCalls($this->calls);
        $this->subject->addCall($this->generatorCall);
        $expected = <<<'EOD'
Expected no generator returned by implode[label] to receive exception equal to RuntimeException("Consequences will never be the same."). Responded:
    - returned "x"
    - returned "y"
    - threw RuntimeException("You done goofed.")
    - threw RuntimeException("Consequences will never be the same.")
    - generated:
        - produced "m": "n"
        - received "o"
        - produced "p": "q"
        - received exception RuntimeException("Consequences will never be the same.")
        - produced "r": "s"
        - received "t"
        - produced "u": "v"
        - received exception RuntimeException("Because I backtraced it.")
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->never()->receivedException($this->receivedExceptionA);
    }

    public function testReceivedExceptionFailureExpectingExceptionNoneReceived()
    {
        $this->subject->setCalls($this->calls);
        $this->subject->addCall($this->generatorCall);
        $expected = <<<'EOD'
Expected generator returned by implode[label] to receive exception equal to RuntimeException(). Responded:
    - returned "x"
    - returned "y"
    - threw RuntimeException("You done goofed.")
    - threw RuntimeException("Consequences will never be the same.")
    - generated:
        - produced "m": "n"
        - received "o"
        - produced "p": "q"
        - received exception RuntimeException("Consequences will never be the same.")
        - produced "r": "s"
        - received "t"
        - produced "u": "v"
        - received exception RuntimeException("Because I backtraced it.")
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->receivedException(new RuntimeException());
    }

    public function testReceivedExceptionFailureMatcherMismatch()
    {
        $this->subject->setCalls($this->calls);
        $this->subject->addCall($this->generatorCall);
        $expected = <<<'EOD'
Expected generator returned by implode[label] to receive exception like RuntimeException#0{}. Responded:
    - returned "x"
    - returned "y"
    - threw RuntimeException("You done goofed.")
    - threw RuntimeException("Consequences will never be the same.")
    - generated:
        - produced "m": "n"
        - received "o"
        - produced "p": "q"
        - received exception RuntimeException("Consequences will never be the same.")
        - produced "r": "s"
        - received "t"
        - produced "u": "v"
        - received exception RuntimeException("Because I backtraced it.")
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->receivedException(new EqualToMatcher(new RuntimeException()));
    }

    public function testReceivedExceptionFailureMatcherNever()
    {
        $this->subject->setCalls($this->calls);
        $this->subject->addCall($this->generatorCall);
        $expected = <<<'EOD'
Expected no generator returned by implode[label] to receive exception like RuntimeException#0{message: "Consequences will never be the same."}. Responded:
    - returned "x"
    - returned "y"
    - threw RuntimeException("You done goofed.")
    - threw RuntimeException("Consequences will never be the same.")
    - generated:
        - produced "m": "n"
        - received "o"
        - produced "p": "q"
        - received exception RuntimeException("Consequences will never be the same.")
        - produced "r": "s"
        - received "t"
        - produced "u": "v"
        - received exception RuntimeException("Because I backtraced it.")
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->never()->receivedException(new EqualToMatcher($this->receivedExceptionA));
    }

    public function testReceivedExceptionFailureInvalidInput()
    {
        $this->setExpectedException('InvalidArgumentException', 'Unable to match exceptions against 111.');
        $this->subject->receivedException(111);
    }

    public function testReceivedExceptionFailureInvalidInputObject()
    {
        $this->setExpectedException('InvalidArgumentException', 'Unable to match exceptions against #0{}.');
        $this->subject->receivedException((object) array());
    }
}
