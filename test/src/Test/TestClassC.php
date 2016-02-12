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

class TestClassC
{
    const CONSTANT_A = 'a';

    public function methodA(self $first, $second = self::CONSTANT_A)
    {
        return implode(func_get_args());
    }

    public function methodB($first, $second = 111, $third = 'second')
    {
        return implode(func_get_args());
    }
}
