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

class TestClassB extends TestClassA implements TestInterfaceB
{
    public static function testClassAStaticMethodB(
        $first,
        $second,
        &$third = null
    ) {
        $result = implode(func_get_args());

        $third = 'third';

        return $result;
    }

    public function __construct()
    {
        $this->constructorArguments = func_get_args();
    }

    public function testClassAMethodB($first, $second, &$third = null)
    {
        $result = implode(func_get_args());

        $third = 'third';

        return $result;
    }

    public static function testClassBStaticMethodA()
    {
        return implode(func_get_args());
    }

    public static function testClassBStaticMethodB($first, $second)
    {
        return implode(func_get_args());
    }

    public function testClassBMethodA()
    {
        return implode(func_get_args());
    }

    public function testClassBMethodB(&$first, &$second)
    {
        $result = implode(func_get_args());

        $first = 'first';
        $second = 'second';

        return $result;
    }
}
