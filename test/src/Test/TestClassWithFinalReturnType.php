<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

class TestClassWithFinalReturnType
{
    public function finalReturnType(): TestFinalClassA
    {
        return new TestFinalClassA();
    }

    public function __call(string $name, array $arguments): TestFinalClassA
    {
        return new TestFinalClassA();
    }
}
