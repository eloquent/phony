<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Test;

use Eloquent\Phony\Call\Argument\Arguments;
use Eloquent\Phony\Invocation\InvocableInterface;

class TestInvocable implements InvocableInterface
{
    public function invokeWith($arguments = null)
    {
        return array(__FUNCTION__, Arguments::adapt($arguments)->all());
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
