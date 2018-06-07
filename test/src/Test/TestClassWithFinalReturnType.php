<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

class TestClassWithFinalReturnType
{
    public function finalReturnType(): TestFinalClass
    {
        return new TestFinalClass();
    }

    public function __call(string $name, array $arguments): TestFinalClass
    {
        return new TestFinalClass();
    }
}
