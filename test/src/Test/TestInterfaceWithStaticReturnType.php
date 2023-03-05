<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

interface TestInterfaceWithStaticReturnType
{
    public static function staticMethod(): static;

    public static function __callStatic($name, array $arguments): static;

    public function method(): static;

    public function __call($name, array $arguments): static;
}
