<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

interface TestInterfaceWithVariadicParameterByReference
{
    public function method(&...$arguments);
}
