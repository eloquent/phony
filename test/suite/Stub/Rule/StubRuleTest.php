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

use Eloquent\Phony\Call\Argument\Arguments;
use Eloquent\Phony\Matcher\EqualToMatcher;
use Eloquent\Phony\Stub\Answer\Answer;
use Eloquent\Phony\Stub\Answer\CallRequest;
use PHPUnit_Framework_TestCase;

class StubRuleTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->criteria = array(new EqualToMatcher('a'), new EqualToMatcher('b'));
        $this->answerA = new Answer(new CallRequest('implode', Arguments::create(), false, false, false), array());
        $this->answerB = new Answer(new CallRequest('implode', Arguments::create(), false, false, false), array());
        $this->answers = array($this->answerA, $this->answerB);
        $this->subject = new StubRule($this->criteria, $this->answers);
    }

    public function testConstructor()
    {
        $this->assertSame($this->criteria, $this->subject->criteria());
        $this->assertSame($this->answers, $this->subject->answers());
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
        $this->subject = new StubRule($this->criteria, array());

        $this->setExpectedException('Eloquent\Phony\Stub\Rule\Exception\UndefinedAnswerException');
        $this->subject->next();
    }
}
