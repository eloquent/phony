<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

use stdClass;

interface TestInterfaceWithVariadicParameterWithNullableType
{
    public function method(?stdClass ...$arguments);
}
