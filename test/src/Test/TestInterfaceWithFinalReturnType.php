<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

interface TestInterfaceWithFinalReturnType
{
    public function finalReturnType(): TestFinalClass;

    public function __call(string $name, array $arguments): TestFinalClass;
}
