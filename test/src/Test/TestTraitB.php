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

trait TestTraitB
{
    use TestTraitA;

    public function testClassAMethodB(
        $first,
        $second,
        &$third = null,
        &$fourth = null,
        &$fifth = null
    ) {
        return implode(func_get_args());
    }

    public function testTraitBMethodA()
    {
        return implode(func_get_args());
    }
}
