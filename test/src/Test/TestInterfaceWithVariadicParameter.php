<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

interface TestInterfaceWithVariadicParameter
{
    public function method(...$arguments);
}
