<?php

class MockGeneratorReadonlyPropertiesAdHoc
implements \Eloquent\Phony\Mock\Mock
{
    public readonly string $propertyA;
    public readonly ?int $propertyB;
    private readonly \Eloquent\Phony\Mock\Handle\InstanceHandle $_handle;
}
