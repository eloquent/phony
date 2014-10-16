<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Stub\Rule;

use Eloquent\Phony\Matcher\EqualToMatcher;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Stub\Answer\Answer;
use Eloquent\Phony\Stub\Answer\ArgumentCallRequest;
use PHPUnit_Framework_TestCase;

class StubRuleTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->criteria = array(new EqualToMatcher('a'), new EqualToMatcher('b'));
        $this->matcherVerifier = new MatcherVerifier();
        $this->subject = new StubRule($this->criteria, $this->matcherVerifier);

        $this->answerA = new Answer(new ArgumentCallRequest(1));
        $this->answerB = new Answer(new ArgumentCallRequest(2));
    }

    public function testConstructor()
    {
        $this->assertSame($this->criteria, $this->subject->criteria());
        $this->assertSame($this->matcherVerifier, $this->subject->matcherVerifier());
        $this->assertSame(array(), $this->subject->answers());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new StubRule($this->criteria);

        $this->assertSame(MatcherVerifier::instance(), $this->subject->matcherVerifier());
    }

    public function testAddAnswer()
    {
        $this->subject->addAnswer($this->answerA);
        $this->subject->addAnswer($this->answerB);

        $this->assertSame(array($this->answerA, $this->answerB), $this->subject->answers());
    }

    public function testMatches()
    {
        $this->assertTrue($this->subject->matches(array('a', 'b')));
        $this->assertFalse($this->subject->matches(array('a')));
    }

    public function testNext()
    {
        $this->subject->addAnswer($this->answerA);
        $this->subject->addAnswer($this->answerB);

        $this->assertSame($this->answerA, $this->subject->next());
        $this->assertSame($this->answerB, $this->subject->next());
        $this->assertSame($this->answerB, $this->subject->next());
        $this->assertSame($this->answerB, $this->subject->next());
    }

    public function testNextFailureUndefined()
    {
        $this->setExpectedException('Eloquent\Phony\Stub\Rule\Exception\UndefinedAnswerException');
        $this->subject->next();
    }
}
