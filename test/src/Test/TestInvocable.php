<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2017 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Test;

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Invocation\Invocable;

class TestInvocable implements Invocable
{
    public function invokeWith($arguments = array())
    {
        if (!$arguments instanceof Arguments) {
            $arguments = Arguments::fromArray($arguments);
        }

        return array(__FUNCTION__, $arguments->all());
    }

    public function invoke()
    {
        return array(__FUNCTION__, func_get_args());
    }

    public function __invoke()
    {
        return array(__FUNCTION__, func_get_args());
    }
}
