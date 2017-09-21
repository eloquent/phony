<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

trait TestTraitI
{
    public static function testClassFStaticMethodA()
    {
        return implode(func_get_args());
    }

    public function testClassFMethodA()
    {
        return implode(func_get_args());
    }
}
