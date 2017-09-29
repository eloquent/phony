<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

class TestClassWithFinalReturnType
{
    public function finalReturnType(): TestFinalClass
    {
        return new TestFinalClass();
    }
}
