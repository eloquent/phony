<?php

namespace Eloquent\Phony\Test;

trait TestTraitA
{
    public static function testClassAStaticMethodA(&$first = null)
    {
        return implode(func_get_args());
    }

    public function testClassAMethodB(
        $first,
        $second,
        &$third = null,
        &$fourth = null
    ) {
        return implode(func_get_args());
    }
}
