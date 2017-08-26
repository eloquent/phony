<?php

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
