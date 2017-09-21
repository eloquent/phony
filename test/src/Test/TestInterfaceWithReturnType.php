<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

interface TestInterfaceWithReturnType
{
    public function classType(): TestClassA;

    public function scalarType(): int;

    public function __call($name, array $arguments): string;

    public static function __callStatic($name, array $arguments): string;
}
