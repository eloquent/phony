<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

trait TestTraitJ
{
    public static function __callStatic($name, array $arguments)
    {
        return 'magic ' . $name . ' ' . implode($arguments);
    }

    public function __call($name, array $arguments)
    {
        return 'magic ' . $name . ' ' . implode($arguments);
    }
}
