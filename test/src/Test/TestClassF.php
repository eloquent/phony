<?php

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
