<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

abstract class AbstractTestClassWithFinalReturnType
{
    abstract public function finalReturnType(): TestFinalClassA;

    abstract public function __call(string $name, array $arguments): TestFinalClassA;
}
