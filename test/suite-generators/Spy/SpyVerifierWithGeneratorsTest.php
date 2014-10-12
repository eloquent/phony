<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Spy;

use Eloquent\Phony\Assertion\Recorder\AssertionRecorder;
use Eloquent\Phony\Assertion\Renderer\AssertionRenderer;
use Eloquent\Phony\Call\Call;
use Eloquent\Phony\Call\Factory\CallVerifierFactory;
use Eloquent\Phony\Event\EventCollection;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Matcher\EqualToMatcher;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Test\TestCallFactory;
use Eloquent\Phony\Test\TestClass;
use Exception;
use PHPUnit_Framework_TestCase;
use RuntimeException;

class SpyVerifierWithGeneratorsTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->callback = 'implode';
        $this->callFactory = new TestCallFactory();
        $this->spy = new Spy($this->callback, false, $this->callFactory);

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
        $this->thisValueA = new TestClass();
        $this->thisValueB = new TestClass();
        $this->arguments = array('a', 'b', 'c');
        $this->matchers = $this->matcherFactory->adaptAll($this->arguments);
        $this->otherMatcher = $this->matcherFactory->adapt('d');
        $this->callA = $this->callFactory->create(
            $this->callEventFactory->createCalled(array($this->thisValueA, 'methodA'), $this->arguments),
            $this->callEventFactory->createReturned($this->returnValueA)
        );
        $this->callAResponse = $this->callA->responseEvent();
        $this->callB = $this->callFactory->create(
            $this->callEventFactory->createCalled(array($this->thisValueB, 'methodA')),
            $this->callEventFactory->createReturned($this->returnValueB)
        );
        $this->callBResponse = $this->callB->responseEvent();
        $this->callC = $this->callFactory->create(
            $this->callEventFactory->createCalled(array($this->thisValueA, 'methodA'), $this->arguments),
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

        $this->sentExceptionA = new RuntimeException('Consequences will never be the same.');
        $this->sentExceptionB = new RuntimeException('Because I backtraced it.');
        $this->generatorCalledEvent = $this->callEventFactory->createCalled();
        $this->generatedEvent = $this->callEventFactory->createGenerated();
        $this->generatorEventA = $this->callEventFactory->createYielded('m', 'n');
        $this->generatorEventB = $this->callEventFactory->createSent('o');
        $this->generatorEventC = $this->callEventFactory->createYielded('p', 'q');
        $this->generatorEventD = $this->callEventFactory->createSentException($this->sentExceptionA);
        $this->generatorEventE = $this->callEventFactory->createYielded('r', 's');
        $this->generatorEventF = $this->callEventFactory->createSent('t');
        $this->generatorEventG = $this->callEventFactory->createYielded('u', 'v');
        $this->generatorEventH = $this->callEventFactory->createSentException($this->sentExceptionB);
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

    public function testCheckYielded()
    {
        $this->assertFalse((boolean) $this->subject->checkYielded());
        $this->assertFalse((boolean) $this->subject->checkYielded('n'));
        $this->assertFalse((boolean) $this->subject->checkYielded('m', 'n'));
        $this->assertFalse((boolean) $this->subject->times(1)->checkYielded());
        $this->assertFalse((boolean) $this->subject->once()->checkYielded('n'));
        $this->assertTrue((boolean) $this->subject->never()->checkYielded('m'));
        $this->assertFalse((boolean) $this->subject->checkYielded('m'));
        $this->assertFalse((boolean) $this->subject->checkYielded('m', 'o'));

        $this->subject->setCalls($this->calls);

        $this->assertFalse((boolean) $this->subject->checkYielded());
        $this->assertFalse((boolean) $this->subject->checkYielded('n'));
        $this->assertFalse((boolean) $this->subject->checkYielded('m', 'n'));
        $this->assertFalse((boolean) $this->subject->times(1)->checkYielded());
        $this->assertFalse((boolean) $this->subject->once()->checkYielded('n'));
        $this->assertTrue((boolean) $this->subject->never()->checkYielded('m'));
        $this->assertFalse((boolean) $this->subject->checkYielded('m'));
        $this->assertFalse((boolean) $this->subject->checkYielded('m', 'o'));

        $this->subject->addCall($this->generatorCall);

        $this->assertTrue((boolean) $this->subject->checkYielded());
        $this->assertTrue((boolean) $this->subject->checkYielded('n'));
        $this->assertTrue((boolean) $this->subject->checkYielded('m', 'n'));
        $this->assertTrue((boolean) $this->subject->times(1)->checkYielded());
        $this->assertTrue((boolean) $this->subject->once()->checkYielded('n'));
        $this->assertTrue((boolean) $this->subject->never()->checkYielded('m'));
        $this->assertFalse((boolean) $this->subject->checkYielded('m'));
        $this->assertFalse((boolean) $this->subject->checkYielded('m', 'o'));
        $this->assertFalse((boolean) $this->subject->always()->checkYielded());

        $this->subject->setCalls(array($this->generatorCall));

        $this->assertTrue((boolean) $this->subject->always()->checkYielded());
    }

    public function testYielded()
    {
        $this->subject->setCalls($this->calls);
        $this->subject->addCall($this->generatorCall);

        $this->assertEquals(new EventCollection(array($this->generatorEventA)), $this->subject->yielded());
        $this->assertEquals(new EventCollection(array($this->generatorEventA)), $this->subject->yielded('n'));
        $this->assertEquals(new EventCollection(array($this->generatorEventA)), $this->subject->yielded('m', 'n'));
        $this->assertEquals(
            new EventCollection(array($this->generatorEventA)),
            $this->subject->times(1)->yielded()
        );
        $this->assertEquals(
            new EventCollection(array($this->generatorEventA)),
            $this->subject->once()->yielded('n')
        );
        $this->assertEquals(new EventCollection(), $this->subject->never()->yielded('m'));

        $this->subject->setCalls(array($this->generatorCall));

        $this->assertEquals(
            new EventCollection(array($this->generatorEventA)),
            $this->subject->always()->yielded()
        );
    }

    public function testYieldedFailureNoCallsNoMatchers()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected call to yield. Never called."
        );
        $this->subject->yielded();
    }

    public function testYieldedFailureNoCallsValueOnly()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected call to yield like <'x'>. Never called."
        );
        $this->subject->yielded('x');
    }

    public function testYieldedFailureNoCallsKeyAndValue()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected call to yield like <'x'> => <'y'>. Never called."
        );
        $this->subject->yielded('x', 'y');
    }

    public function testYieldedFailureNoGeneratorsNoMatchers()
    {
        $this->subject->setCalls($this->calls);
        $expected = <<<'EOD'
Expected call to yield. Responded:
    - returned 'x'
    - returned 'y'
    - threw RuntimeException('You done goofed.')
    - threw RuntimeException('Consequences will never be the same.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->yielded();
    }

    public function testYieldedFailureNoGeneratorsValueOnly()
    {
        $this->subject->setCalls($this->calls);
        $expected = <<<'EOD'
Expected call to yield like <'x'>. Responded:
    - returned 'x'
    - returned 'y'
    - threw RuntimeException('You done goofed.')
    - threw RuntimeException('Consequences will never be the same.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->yielded('x');
    }

    public function testYieldedFailureNoGeneratorsKeyAndValue()
    {
        $this->subject->setCalls($this->calls);
        $expected = <<<'EOD'
Expected call to yield like <'x'> => <'y'>. Responded:
    - returned 'x'
    - returned 'y'
    - threw RuntimeException('You done goofed.')
    - threw RuntimeException('Consequences will never be the same.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->yielded('x', 'y');
    }

    public function testYieldedFailureValueMismatch()
    {
        $this->subject->setCalls($this->calls);
        $this->subject->addCall($this->generatorCall);
        $expected = <<<'EOD'
Expected call to yield like <'x'>. Responded:
    - returned 'x'
    - returned 'y'
    - threw RuntimeException('You done goofed.')
    - threw RuntimeException('Consequences will never be the same.')
    - generated:
        - yielded 'm' => 'n'
        - sent 'o'
        - yielded 'p' => 'q'
        - sent exception RuntimeException('Consequences will never be the same.')
        - yielded 'r' => 's'
        - sent 't'
        - yielded 'u' => 'v'
        - sent exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->yielded('x');
    }

    public function testYieldedFailureKeyValueMismatch()
    {
        $this->subject->setCalls($this->calls);
        $this->subject->addCall($this->generatorCall);
        $expected = <<<'EOD'
Expected call to yield like <'x'> => <'y'>. Responded:
    - returned 'x'
    - returned 'y'
    - threw RuntimeException('You done goofed.')
    - threw RuntimeException('Consequences will never be the same.')
    - generated:
        - yielded 'm' => 'n'
        - sent 'o'
        - yielded 'p' => 'q'
        - sent exception RuntimeException('Consequences will never be the same.')
        - yielded 'r' => 's'
        - sent 't'
        - yielded 'u' => 'v'
        - sent exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->yielded('x', 'y');
    }

    public function testYieldedFailureAlways()
    {
        $this->subject->setCalls($this->calls);
        $this->subject->addCall($this->generatorCall);
        $expected = <<<'EOD'
Expected every call to yield like <'n'>. Responded:
    - returned 'x'
    - returned 'y'
    - threw RuntimeException('You done goofed.')
    - threw RuntimeException('Consequences will never be the same.')
    - generated:
        - yielded 'm' => 'n'
        - sent 'o'
        - yielded 'p' => 'q'
        - sent exception RuntimeException('Consequences will never be the same.')
        - yielded 'r' => 's'
        - sent 't'
        - yielded 'u' => 'v'
        - sent exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->always()->yielded('n');
    }

    public function testYieldedFailureNever()
    {
        $this->subject->setCalls($this->calls);
        $this->subject->addCall($this->generatorCall);
        $expected = <<<'EOD'
Expected no call to yield like <'n'>. Responded:
    - returned 'x'
    - returned 'y'
    - threw RuntimeException('You done goofed.')
    - threw RuntimeException('Consequences will never be the same.')
    - generated:
        - yielded 'm' => 'n'
        - sent 'o'
        - yielded 'p' => 'q'
        - sent exception RuntimeException('Consequences will never be the same.')
        - yielded 'r' => 's'
        - sent 't'
        - yielded 'u' => 'v'
        - sent exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->never()->yielded('n');
    }

    public function testCheckSent()
    {
        $this->assertFalse((boolean) $this->subject->checkSent());
        $this->assertFalse((boolean) $this->subject->checkSent('o'));
        $this->assertFalse((boolean) $this->subject->times(1)->checkSent());
        $this->assertFalse((boolean) $this->subject->once()->checkSent('o'));
        $this->assertTrue((boolean) $this->subject->never()->checkSent('x'));
        $this->assertFalse((boolean) $this->subject->always()->checkSent());
        $this->assertFalse((boolean) $this->subject->checkSent('x'));

        $this->subject->setCalls($this->calls);

        $this->assertFalse((boolean) $this->subject->checkSent());
        $this->assertFalse((boolean) $this->subject->checkSent('o'));
        $this->assertFalse((boolean) $this->subject->times(1)->checkSent());
        $this->assertFalse((boolean) $this->subject->once()->checkSent('o'));
        $this->assertTrue((boolean) $this->subject->never()->checkSent('x'));
        $this->assertFalse((boolean) $this->subject->always()->checkSent());
        $this->assertFalse((boolean) $this->subject->checkSent('x'));

        $this->subject->addCall($this->generatorCall);

        $this->assertTrue((boolean) $this->subject->checkSent());
        $this->assertTrue((boolean) $this->subject->checkSent('o'));
        $this->assertTrue((boolean) $this->subject->times(1)->checkSent());
        $this->assertTrue((boolean) $this->subject->once()->checkSent('o'));
        $this->assertTrue((boolean) $this->subject->never()->checkSent('x'));
        $this->assertFalse((boolean) $this->subject->always()->checkSent());
        $this->assertFalse((boolean) $this->subject->checkSent('x'));
    }

    public function testSent()
    {
        $this->subject->setCalls($this->calls);
        $this->subject->addCall($this->generatorCall);

        $this->assertEquals(new EventCollection(array($this->generatorEventB)), $this->subject->sent());
        $this->assertEquals(new EventCollection(array($this->generatorEventB)), $this->subject->sent('o'));
        $this->assertEquals(new EventCollection(array($this->generatorEventB)), $this->subject->times(1)->sent());
        $this->assertEquals(new EventCollection(array($this->generatorEventB)), $this->subject->once()->sent('o'));
        $this->assertEquals(new EventCollection(), $this->subject->never()->sent('x'));
    }

    public function testSentFailureNoCallsNoMatcher()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected generator to be sent value. Never called."
        );
        $this->subject->sent();
    }

    public function testSentFailureNoCallsWithMatcher()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected generator to be sent value like <'x'>. Never called."
        );
        $this->subject->sent('x');
    }

    public function testSentFailureNoGeneratorsNoMatcher()
    {
        $this->subject->setCalls($this->calls);
        $expected = <<<'EOD'
Expected generator to be sent value. Responded:
    - returned 'x'
    - returned 'y'
    - threw RuntimeException('You done goofed.')
    - threw RuntimeException('Consequences will never be the same.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->sent();
    }

    public function testSentFailureNoGeneratorsWithMatcher()
    {
        $this->subject->setCalls($this->calls);
        $expected = <<<'EOD'
Expected generator to be sent value like <'x'>. Responded:
    - returned 'x'
    - returned 'y'
    - threw RuntimeException('You done goofed.')
    - threw RuntimeException('Consequences will never be the same.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->sent('x');
    }

    public function testSentFailureMatcherMismatch()
    {
        $this->subject->setCalls($this->calls);
        $this->subject->addCall($this->generatorCall);
        $expected = <<<'EOD'
Expected generator to be sent value like <'x'>. Responded:
    - returned 'x'
    - returned 'y'
    - threw RuntimeException('You done goofed.')
    - threw RuntimeException('Consequences will never be the same.')
    - generated:
        - yielded 'm' => 'n'
        - sent 'o'
        - yielded 'p' => 'q'
        - sent exception RuntimeException('Consequences will never be the same.')
        - yielded 'r' => 's'
        - sent 't'
        - yielded 'u' => 'v'
        - sent exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->sent('x');
    }

    public function testCheckSentException()
    {
        $this->assertFalse((boolean) $this->subject->checkSentException());
        $this->assertFalse((boolean) $this->subject->checkSentException('Exception'));
        $this->assertFalse((boolean) $this->subject->checkSentException('RuntimeException'));
        $this->assertFalse((boolean) $this->subject->checkSentException($this->sentExceptionA));
        $this->assertFalse((boolean) $this->subject->checkSentException($this->sentExceptionB));
        $this->assertFalse((boolean) $this->subject->checkSentException(new EqualToMatcher($this->sentExceptionA)));
        $this->assertFalse((boolean) $this->subject->checkSentException('InvalidArgumentException'));
        $this->assertFalse((boolean) $this->subject->checkSentException(new Exception()));
        $this->assertFalse((boolean) $this->subject->checkSentException(new RuntimeException()));
        $this->assertFalse((boolean) $this->subject->checkSentException(new EqualToMatcher(new RuntimeException())));
        $this->assertFalse((boolean) $this->subject->checkSentException(new EqualToMatcher(null)));
        $this->assertTrue((boolean) $this->subject->never()->checkSentException());
        $this->assertTrue((boolean) $this->subject->never()->checkSentException('Exception'));
        $this->assertTrue((boolean) $this->subject->never()->checkSentException('RuntimeException'));
        $this->assertTrue((boolean) $this->subject->never()->checkSentException($this->sentExceptionA));
        $this->assertTrue(
            (boolean) $this->subject->never()->checkSentException(new EqualToMatcher($this->sentExceptionA))
        );

        $this->subject->setCalls($this->calls);

        $this->assertFalse((boolean) $this->subject->checkSentException());
        $this->assertFalse((boolean) $this->subject->checkSentException('Exception'));
        $this->assertFalse((boolean) $this->subject->checkSentException('RuntimeException'));
        $this->assertFalse((boolean) $this->subject->checkSentException($this->sentExceptionA));
        $this->assertFalse((boolean) $this->subject->checkSentException($this->sentExceptionB));
        $this->assertFalse((boolean) $this->subject->checkSentException(new EqualToMatcher($this->sentExceptionA)));
        $this->assertFalse((boolean) $this->subject->checkSentException('InvalidArgumentException'));
        $this->assertFalse((boolean) $this->subject->checkSentException(new Exception()));
        $this->assertFalse((boolean) $this->subject->checkSentException(new RuntimeException()));
        $this->assertFalse((boolean) $this->subject->checkSentException(new EqualToMatcher(new RuntimeException())));
        $this->assertFalse((boolean) $this->subject->checkSentException(new EqualToMatcher(null)));
        $this->assertTrue((boolean) $this->subject->never()->checkSentException());
        $this->assertTrue((boolean) $this->subject->never()->checkSentException('Exception'));
        $this->assertTrue((boolean) $this->subject->never()->checkSentException('RuntimeException'));
        $this->assertTrue((boolean) $this->subject->never()->checkSentException($this->sentExceptionA));
        $this->assertTrue(
            (boolean) $this->subject->never()->checkSentException(new EqualToMatcher($this->sentExceptionA))
        );

        $this->subject->addCall($this->generatorCall);

        $this->assertTrue((boolean) $this->subject->checkSentException());
        $this->assertTrue((boolean) $this->subject->checkSentException('Exception'));
        $this->assertTrue((boolean) $this->subject->checkSentException('RuntimeException'));
        $this->assertTrue((boolean) $this->subject->checkSentException($this->sentExceptionA));
        $this->assertTrue((boolean) $this->subject->checkSentException($this->sentExceptionB));
        $this->assertTrue((boolean) $this->subject->checkSentException(new EqualToMatcher($this->sentExceptionA)));
        $this->assertFalse((boolean) $this->subject->checkSentException('InvalidArgumentException'));
        $this->assertFalse((boolean) $this->subject->checkSentException(new Exception()));
        $this->assertFalse((boolean) $this->subject->checkSentException(new RuntimeException()));
        $this->assertFalse((boolean) $this->subject->checkSentException(new EqualToMatcher(new RuntimeException())));
        $this->assertFalse((boolean) $this->subject->checkSentException(new EqualToMatcher(null)));
        $this->assertFalse((boolean) $this->subject->never()->checkSentException());
        $this->assertFalse((boolean) $this->subject->never()->checkSentException('Exception'));
        $this->assertFalse((boolean) $this->subject->never()->checkSentException('RuntimeException'));
        $this->assertFalse((boolean) $this->subject->never()->checkSentException($this->sentExceptionA));
        $this->assertFalse(
            (boolean) $this->subject->never()->checkSentException(new EqualToMatcher($this->sentExceptionA))
        );
    }

    public function testSentException()
    {
        $this->subject->setCalls($this->calls);
        $this->subject->addCall($this->generatorCall);

        $this->assertEquals(new EventCollection(array($this->generatorEventD)), $this->subject->sentException());
        $this->assertEquals(
            new EventCollection(array($this->generatorEventD)),
            $this->subject->sentException('Exception')
        );
        $this->assertEquals(
            new EventCollection(array($this->generatorEventD)),
            $this->subject->sentException('RuntimeException')
        );
        $this->assertEquals(
            new EventCollection(array($this->generatorEventD)),
            $this->subject->sentException($this->sentExceptionA)
        );
        $this->assertEquals(
            new EventCollection(array($this->generatorEventH)),
            $this->subject->sentException($this->sentExceptionB)
        );
        $this->assertEquals(
            new EventCollection(array($this->generatorEventD)),
            $this->subject->sentException(new EqualToMatcher($this->sentExceptionA))
        );
        $this->assertEquals(new EventCollection(), $this->subject->never()->sentException('InvalidArgumentException'));
    }

    public function testSentExceptionFailureNoCallsNoType()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected generator to be sent exception. Never called."
        );
        $this->subject->sentException();
    }

    public function testSentExceptionFailureNoCallsWithType()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected generator to be sent 'InvalidArgumentException' exception. Never called."
        );
        $this->subject->sentException('InvalidArgumentException');
    }

    public function testSentExceptionFailureNoCallsWithException()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected generator to be sent exception equal to RuntimeException(). Never called."
        );
        $this->subject->sentException(new RuntimeException());
    }

    public function testSentExceptionFailureNoCallsWithMatcher()
    {
        $this->setExpectedException(
            'Eloquent\Phony\Assertion\Exception\AssertionException',
            "Expected generator to be sent exception like <RuntimeException Object (...)>. Never called."
        );
        $this->subject->sentException(new EqualToMatcher(new RuntimeException()));
    }

    public function testSentExceptionFailureExpectingNeverAny()
    {
        $this->subject->setCalls($this->calls);
        $this->subject->addCall($this->generatorCall);
        $expected = <<<'EOD'
Expected no generator to be sent exception. Responded:
    - returned 'x'
    - returned 'y'
    - threw RuntimeException('You done goofed.')
    - threw RuntimeException('Consequences will never be the same.')
    - generated:
        - yielded 'm' => 'n'
        - sent 'o'
        - yielded 'p' => 'q'
        - sent exception RuntimeException('Consequences will never be the same.')
        - yielded 'r' => 's'
        - sent 't'
        - yielded 'u' => 'v'
        - sent exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->never()->sentException();
    }

    public function testSentExceptionFailureExpectingAlwaysAny()
    {
        $this->subject->setCalls($this->calls);
        $this->subject->addCall($this->generatorCall);
        $expected = <<<'EOD'
Expected every generator to be sent exception. Responded:
    - returned 'x'
    - returned 'y'
    - threw RuntimeException('You done goofed.')
    - threw RuntimeException('Consequences will never be the same.')
    - generated:
        - yielded 'm' => 'n'
        - sent 'o'
        - yielded 'p' => 'q'
        - sent exception RuntimeException('Consequences will never be the same.')
        - yielded 'r' => 's'
        - sent 't'
        - yielded 'u' => 'v'
        - sent exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->always()->sentException();
    }

    public function testSentExceptionFailureTypeMismatch()
    {
        $this->subject->setCalls($this->calls);
        $this->subject->addCall($this->generatorCall);
        $expected = <<<'EOD'
Expected generator to be sent 'InvalidArgumentException' exception. Responded:
    - returned 'x'
    - returned 'y'
    - threw RuntimeException('You done goofed.')
    - threw RuntimeException('Consequences will never be the same.')
    - generated:
        - yielded 'm' => 'n'
        - sent 'o'
        - yielded 'p' => 'q'
        - sent exception RuntimeException('Consequences will never be the same.')
        - yielded 'r' => 's'
        - sent 't'
        - yielded 'u' => 'v'
        - sent exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->sentException('InvalidArgumentException');
    }

    public function testSentExceptionFailureTypeNever()
    {
        $this->subject->setCalls($this->calls);
        $this->subject->addCall($this->generatorCall);
        $expected = <<<'EOD'
Expected no generator to be sent 'RuntimeException' exception. Responded:
    - returned 'x'
    - returned 'y'
    - threw RuntimeException('You done goofed.')
    - threw RuntimeException('Consequences will never be the same.')
    - generated:
        - yielded 'm' => 'n'
        - sent 'o'
        - yielded 'p' => 'q'
        - sent exception RuntimeException('Consequences will never be the same.')
        - yielded 'r' => 's'
        - sent 't'
        - yielded 'u' => 'v'
        - sent exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->never()->sentException('RuntimeException');
    }

    public function testSentExceptionFailureExpectingTypeNoneSent()
    {
        $this->subject->setCalls($this->calls);
        $this->subject->addCall($this->generatorCall);
        $expected = <<<'EOD'
Expected generator to be sent 'InvalidArgumentException' exception. Responded:
    - returned 'x'
    - returned 'y'
    - threw RuntimeException('You done goofed.')
    - threw RuntimeException('Consequences will never be the same.')
    - generated:
        - yielded 'm' => 'n'
        - sent 'o'
        - yielded 'p' => 'q'
        - sent exception RuntimeException('Consequences will never be the same.')
        - yielded 'r' => 's'
        - sent 't'
        - yielded 'u' => 'v'
        - sent exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->sentException('InvalidArgumentException');
    }

    public function testSentExceptionFailureExceptionMismatch()
    {
        $this->subject->setCalls($this->calls);
        $this->subject->addCall($this->generatorCall);
        $expected = <<<'EOD'
Expected generator to be sent exception equal to RuntimeException(). Responded:
    - returned 'x'
    - returned 'y'
    - threw RuntimeException('You done goofed.')
    - threw RuntimeException('Consequences will never be the same.')
    - generated:
        - yielded 'm' => 'n'
        - sent 'o'
        - yielded 'p' => 'q'
        - sent exception RuntimeException('Consequences will never be the same.')
        - yielded 'r' => 's'
        - sent 't'
        - yielded 'u' => 'v'
        - sent exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->sentException(new RuntimeException());
    }

    public function testSentExceptionFailureExceptionNever()
    {
        $this->subject->setCalls($this->calls);
        $this->subject->addCall($this->generatorCall);
        $expected = <<<'EOD'
Expected no generator to be sent exception equal to RuntimeException('Consequences will never be the same.'). Responded:
    - returned 'x'
    - returned 'y'
    - threw RuntimeException('You done goofed.')
    - threw RuntimeException('Consequences will never be the same.')
    - generated:
        - yielded 'm' => 'n'
        - sent 'o'
        - yielded 'p' => 'q'
        - sent exception RuntimeException('Consequences will never be the same.')
        - yielded 'r' => 's'
        - sent 't'
        - yielded 'u' => 'v'
        - sent exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->never()->sentException($this->sentExceptionA);
    }

    public function testSentExceptionFailureExpectingExceptionNoneSent()
    {
        $this->subject->setCalls($this->calls);
        $this->subject->addCall($this->generatorCall);
        $expected = <<<'EOD'
Expected generator to be sent exception equal to RuntimeException(). Responded:
    - returned 'x'
    - returned 'y'
    - threw RuntimeException('You done goofed.')
    - threw RuntimeException('Consequences will never be the same.')
    - generated:
        - yielded 'm' => 'n'
        - sent 'o'
        - yielded 'p' => 'q'
        - sent exception RuntimeException('Consequences will never be the same.')
        - yielded 'r' => 's'
        - sent 't'
        - yielded 'u' => 'v'
        - sent exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->sentException(new RuntimeException());
    }

    public function testSentExceptionFailureMatcherMismatch()
    {
        $this->subject->setCalls($this->calls);
        $this->subject->addCall($this->generatorCall);
        $expected = <<<'EOD'
Expected generator to be sent exception like <RuntimeException Object (...)>. Responded:
    - returned 'x'
    - returned 'y'
    - threw RuntimeException('You done goofed.')
    - threw RuntimeException('Consequences will never be the same.')
    - generated:
        - yielded 'm' => 'n'
        - sent 'o'
        - yielded 'p' => 'q'
        - sent exception RuntimeException('Consequences will never be the same.')
        - yielded 'r' => 's'
        - sent 't'
        - yielded 'u' => 'v'
        - sent exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->sentException(new EqualToMatcher(new RuntimeException()));
    }

    public function testSentExceptionFailureMatcherNever()
    {
        $this->subject->setCalls($this->calls);
        $this->subject->addCall($this->generatorCall);
        $expected = <<<'EOD'
Expected no generator to be sent exception like <RuntimeException Object (...)>. Responded:
    - returned 'x'
    - returned 'y'
    - threw RuntimeException('You done goofed.')
    - threw RuntimeException('Consequences will never be the same.')
    - generated:
        - yielded 'm' => 'n'
        - sent 'o'
        - yielded 'p' => 'q'
        - sent exception RuntimeException('Consequences will never be the same.')
        - yielded 'r' => 's'
        - sent 't'
        - yielded 'u' => 'v'
        - sent exception RuntimeException('Because I backtraced it.')
EOD;

        $this->setExpectedException('Eloquent\Phony\Assertion\Exception\AssertionException', $expected);
        $this->subject->never()->sentException(new EqualToMatcher($this->sentExceptionA));
    }

    public function testSentExceptionFailureInvalidInput()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            "Unable to match exceptions against 111."
        );
        $this->subject->sentException(111);
    }

    public function testSentExceptionFailureInvalidInputObject()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            "Unable to match exceptions against stdClass Object ()."
        );
        $this->subject->sentException((object) array());
    }
}
