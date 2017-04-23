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

final class TestClassH
{
    final public static function testClassAStaticMethodA()
    {
        return 'final ' . implode(func_get_args());
    }

    final public static function testClassAStaticMethodB($first, $second)
    {
        return 'final ' . implode(func_get_args());
    }

    final public function __construct(&$first = null, &$second = null)
    {
        $this->constructorArguments = func_get_args();

        $first = 'first';
        $second = 'second';
    }

    final public function testClassAMethodA()
    {
        return 'final ' . implode(func_get_args());
    }

    final public function testClassAMethodB($first, $second)
    {
        return 'final ' . implode(func_get_args());
    }

    final protected static function testClassAStaticMethodC()
    {
        return 'final protected ' . implode(func_get_args());
    }

    final protected static function testClassAStaticMethodD($first, $second)
    {
        return 'final protected ' . implode(func_get_args());
    }

    final protected function testClassAMethodC()
    {
        return 'final protected ' . implode(func_get_args());
    }

    final protected function testClassAMethodD(&$first, &$second)
    {
        return 'final protected ' . implode(func_get_args());
    }

    final private static function testClassAStaticMethodE()
    {
        return 'final private ' . implode(func_get_args());
    }

    final private function testClassAMethodE()
    {
        return 'final private ' . implode(func_get_args());
    }

    public $constructorArguments;
}
