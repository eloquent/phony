<?php

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

    public function __construct(&$first = null, &$second = null)
    {
        $this->constructorArguments = func_get_args();

        $first = 'first';
        $second = 'second';
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
        return 'protected ' . implode(func_get_args());
    }

    protected static function testClassAStaticMethodD($first, $second)
    {
        return 'protected ' . implode(func_get_args());
    }

    protected function testClassAMethodC()
    {
        return 'protected ' . implode(func_get_args());
    }

    protected function testClassAMethodD(&$first, &$second)
    {
        return 'protected ' . implode(func_get_args());
    }

    private static function testClassAStaticMethodE()
    {
        return 'private ' . implode(func_get_args());
    }

    private function testClassAMethodE()
    {
        return 'private ' . implode(func_get_args());
    }

    public $constructorArguments;
}
