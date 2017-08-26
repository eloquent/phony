<?php

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
