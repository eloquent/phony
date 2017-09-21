<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

class TestClassG
{
    public static function &testClassGStaticMethodA($a, &$b, &$c)
    {
        if ($a) {
            return $b;
        }

        return $c;
    }

    public static function &__callStatic($name, array $arguments)
    {
        if ($arguments[0]) {
            return $arguments[1];
        }

        return $arguments[2];
    }

    public function &testClassGMethodA($a, &$b, &$c)
    {
        if ($a) {
            return $b;
        }

        return $c;
    }

    public function &__call($name, array $arguments)
    {
        if ($arguments[0]) {
            return $arguments[1];
        }

        return $arguments[2];
    }
}
