<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

function testFunctionWithFinalReturnType(): TestFinalClassA
{
    return new TestFinalClassA();
}
