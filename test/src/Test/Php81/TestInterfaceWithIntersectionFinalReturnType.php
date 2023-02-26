<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test\Php81;

use Countable;
use Eloquent\Phony\Test\TestFinalClassA;

interface TestInterfaceWithIntersectionFinalReturnType
{
    public function finalReturnType(): Countable&TestFinalClassA;
}
