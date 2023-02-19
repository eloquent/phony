<?php

declare(strict_types=1);

namespace Eloquent\Phony\Invocation;

use AllowDynamicProperties;
use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Test\TestInvocable;
use PHPUnit\Framework\TestCase;

#[AllowDynamicProperties]
class InvokerTest extends TestCase
{
    protected function setUp(): void
    {
        $this->subject = new Invoker();

        $this->invocable = new TestInvocable();
    }

    public function testCallWith()
    {
        $this->assertSame(phpversion(), $this->subject->callWith('phpversion', Arguments::create()));
        $this->assertSame(1, $this->subject->callWith('strlen', Arguments::create('a')));
        $this->assertSame(
            ['invokeWith', ['a', 'b']],
            $this->subject->callWith($this->invocable, Arguments::create('a', 'b'))
        );
    }
}
