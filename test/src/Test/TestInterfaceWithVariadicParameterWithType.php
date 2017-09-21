<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

use stdClass;

interface TestInterfaceWithVariadicParameterWithType
{
    public function method(stdClass ...$arguments);
}
