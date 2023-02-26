<?php

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Mock\Handle\StaticHandleRegistry;
use Eloquent\Phony\Mock\Mock;

class MockGeneratorReadonlyPropertiesAdHoc
implements Mock
{
    public readonly string $propertyA;
    public readonly ?int $propertyB;
    private readonly InstanceHandle $_handle;
}
