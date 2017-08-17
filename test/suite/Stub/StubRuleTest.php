<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2017 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Stub;

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Matcher\MatcherFactory;
use Eloquent\Phony\Stub\Answer\Answer;
use Eloquent\Phony\Stub\Answer\CallRequest;
use PHPUnit\Framework\TestCase;

class StubRuleTest extends TestCase
{
    protected function setUp()
    {
        $this->matcherFactory = MatcherFactory::instance();
        $this->criteria = [$this->matcherFactory->equalTo('a'), $this->matcherFactory->equalTo('b')];
        $this->answerA = new Answer(new CallRequest('implode', Arguments::create(), false, false, false), []);
        $this->answerB = new Answer(new CallRequest('implode', Arguments::create(), false, false, false), []);
        $this->answers = [$this->answerA, $this->answerB];
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
        $this->subject = new StubRule($this->criteria, []);

        $this->expectException('Eloquent\Phony\Stub\Exception\UndefinedAnswerException');
        $this->subject->next();
    }
}
