<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

interface TestInterfaceWithNeverReturnType
{
    public static function staticMethod(): never;

    public static function __callStatic($name, array $arguments): never;

    public function method(): never;

    public function __call($name, array $arguments): never;
}
