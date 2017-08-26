<?php

namespace Eloquent\Phony\Test;

class TestClassC
{
    const CONSTANT_A = 'a';

    public function methodA(self $first, $second = self::CONSTANT_A)
    {
        return implode(func_get_args());
    }

    public function methodB($first, $second = 111, $third = 'second')
    {
        return implode(func_get_args());
    }
}
