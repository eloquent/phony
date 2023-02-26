<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

interface TestInterfaceWithUnionFinalReturnType
{
    public function finalReturnType(): TestFinalClassA|TestFinalClassB;
}
