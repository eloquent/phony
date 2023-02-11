<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

class TestClassWithVoidReturnType
{
    public static function staticMethod(): void {}

    public static function __callStatic($name, array $arguments): void {}

    public function method(): void {}

    public function __call($name, array $arguments): void {}
}
