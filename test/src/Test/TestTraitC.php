<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Test;

trait TestTraitC
{
    public static function testClassAStaticMethodA()
    {
        return implode(func_get_args());
    }

    public function testClassAMethodB()
    {
        return implode(func_get_args());
    }

    abstract public function testTraitCMethodA();
}
