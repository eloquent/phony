<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

abstract class AbstractTestClassWithFinalReturnType
{
    abstract public function finalReturnType(): TestFinalClass;

    abstract public function __call(string $name, array $arguments): TestFinalClass;
}
