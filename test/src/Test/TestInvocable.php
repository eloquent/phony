<?php

namespace Eloquent\Phony\Test;

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Invocation\Invocable;

class TestInvocable implements Invocable
{
    public function invokeWith($arguments = [])
    {
        if (!$arguments instanceof Arguments) {
            $arguments = Arguments::fromArray($arguments);
        }

        return [__FUNCTION__, $arguments->all()];
    }

    public function invoke(...$arguments)
    {
        return [__FUNCTION__, $arguments];
    }

    public function __invoke(...$arguments)
    {
        return [__FUNCTION__, $arguments];
    }
}
