<?php

declare(strict_types=1);

namespace Eloquent\Phony\Stub;

use AllowDynamicProperties;
use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Stub\Answer\Answer;
use Eloquent\Phony\Stub\Answer\CallRequest;
use Eloquent\Phony\Stub\Exception\UndefinedAnswerException;
use Eloquent\Phony\Test\Facade\FacadeContainer;
use PHPUnit\Framework\TestCase;

#[AllowDynamicProperties]
class StubRuleTest extends TestCase
{
    protected function setUp(): void
    {
        $container = new FacadeContainer();
        $this->criteria = [$container->matcherFactory->equalTo('a'), $container->matcherFactory->equalTo('b')];
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

        $this->expectException(UndefinedAnswerException::class);
        $this->subject->next();
    }
}
