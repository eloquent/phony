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

class TestClassA implements TestInterfaceA
{
    public static function testClassAStaticMethodA()
    {
        return implode(func_get_args());
    }

    public static function testClassAStaticMethodB($first, $second)
    {
        return implode(func_get_args());
    }

    public function testClassAMethodA()
    {
        return implode(func_get_args());
    }

    public function testClassAMethodB($first, $second)
    {
        return implode(func_get_args());
    }

    protected static function testClassAStaticMethodC()
    {
        return implode(func_get_args());
    }

    protected static function testClassAStaticMethodD($first, $second)
    {
        return implode(func_get_args());
    }

    protected function testClassAMethodC()
    {
        return implode(func_get_args());
    }

    protected function testClassAMethodD($first, $second)
    {
        return implode(func_get_args());
    }

    private static function testClassAStaticMethodE()
    {
        return implode(func_get_args());
    }

    private function testClassAMethodE()
    {
        return implode(func_get_args());
    }
}
