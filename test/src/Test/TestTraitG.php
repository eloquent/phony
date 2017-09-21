<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

trait TestTraitG
{
    final public static function testTraitGStaticMethodA()
    {
        return implode(func_get_args());
    }

    final public function testTraitGMethodA()
    {
        return implode(func_get_args());
    }
}
