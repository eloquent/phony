<?php

declare(strict_types=1);

namespace Eloquent\Phony\Stub\Answer;

use AllowDynamicProperties;
use Eloquent\Phony\Call\Arguments;
use PHPUnit\Framework\TestCase;

#[AllowDynamicProperties]
class AnswerTest extends TestCase
{
    protected function setUp(): void
    {
        $this->primaryRequest = new CallRequest('implode', Arguments::create(), false, false, false);
        $this->secondaryRequestA = new CallRequest('implode', Arguments::create(), false, false, false);
        $this->secondaryRequestB = new CallRequest('implode', Arguments::create(), false, false, false);
        $this->secondaryRequests = [$this->secondaryRequestA, $this->secondaryRequestB];
        $this->subject = new Answer($this->primaryRequest, $this->secondaryRequests);
    }

    public function testConstructor()
    {
        $this->assertSame($this->primaryRequest, $this->subject->primaryRequest());
        $this->assertSame($this->secondaryRequests, $this->subject->secondaryRequests());
    }
}
