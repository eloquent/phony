<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

use Eloquent\Phony\Invocation\WrappedInvocable;
use Eloquent\Phony\Invocation\WrappedInvocableTrait;

class TestWrappedInvocable implements WrappedInvocable
{
    use WrappedInvocableTrait;

    public function __construct($callback = null)
    {
        if (!$callback) {
            $this->isAnonymous = true;
            $this->callback = function (...$args) {};
        } else {
            $this->isAnonymous = false;
            $this->callback = $callback;
        }
    }

    public function invokeWith($arguments = null)
    {
    }
}
