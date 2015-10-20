<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Test;

class TestClassF
{
    final public static function testClassFStaticMethodA()
    {
        return implode(func_get_args());
    }

    final public function testClassFMethodA()
    {
        return implode(func_get_args());
    }
}
