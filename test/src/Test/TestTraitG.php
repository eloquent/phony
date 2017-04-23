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

trait TestTraitG
{
    final public static function testTraitGStaticMethodA()
    {
        return implode(func_get_args());
    }

    final public function testTraitGMethodA()
    {
        return implode(func_get_args());
    }
}
