<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

interface TestInterfaceWithFinalReturnType
{
    public function finalReturnType(): TestFinalClassA;

    public function __call(string $name, array $arguments): TestFinalClassA;
}
