<?php

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Mock\Handle\StaticHandleRegistry;
use Eloquent\Phony\Mock\Mock;

class MockGeneratorTypedPropertiesAdHoc
implements Mock
{
    public $propertyA = 'a';
    public string $propertyB = 'b';
    public ?int $propertyC = null;
    private readonly InstanceHandle $_handle;
}
