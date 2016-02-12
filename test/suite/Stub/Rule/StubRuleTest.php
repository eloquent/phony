<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Stub\Rule;

use Eloquent\Phony\Matcher\EqualToMatcher;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Stub\Answer\Answer;
use Eloquent\Phony\Stub\Answer\CallRequest;
use PHPUnit_Framework_TestCase;

class StubRuleTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->criteria = array(new EqualToMatcher('a'), new EqualToMatcher('b'));
        $this->answerA = new Answer(new CallRequest('implode'));
        $this->answerB = new Answer(new CallRequest('implode'));
        $this->answers = array($this->answerA, $this->answerB);
        $this->matcherVerifier = new MatcherVerifier();
        $this->subject = new StubRule($this->criteria, $this->answers, $this->matcherVerifier);
    }

    public function testConstructor()
    {
        $this->assertSame($this->criteria, $this->subject->criteria());
        $this->assertSame($this->answers, $this->subject->answers());
        $this->assertSame($this->matcherVerifier, $this->subject->matcherVerifier());
    }

    public function testConstructorDefaults()
    {
        $this->subject = new StubRule($this->criteria, $this->answers);

        $this->assertSame(MatcherVerifier::instance(), $this->subject->matcherVerifier());
    }

    public function testMatches()
    {
        $this->assertTrue($this->subject->matches(array('a', 'b')));
        $this->assertFalse($this->subject->matches(array('a')));
    }

    public function testNext()
    {
        $this->assertSame($this->answerA, $this->subject->next());
        $this->assertSame($this->answerB, $this->subject->next());
        $this->assertSame($this->answerB, $this->subject->next());
        $this->assertSame($this->answerB, $this->subject->next());
    }

    public function testNextFailureUndefined()
    {
        $this->subject = new StubRule($this->criteria, array(), $this->matcherVerifier);

        $this->setExpectedException('Eloquent\Phony\Stub\Rule\Exception\UndefinedAnswerException');
        $this->subject->next();
    }
}
