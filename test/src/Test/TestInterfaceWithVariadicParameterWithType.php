<?php

namespace Eloquent\Phony\Test;

use stdClass;

interface TestInterfaceWithVariadicParameterWithType
{
    public function method(stdClass ...$arguments);
}
