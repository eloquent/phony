<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

interface TestInterfaceD
{
    public static function __callStatic($name, array $arguments);

    public function __call($name, array $arguments);
}
