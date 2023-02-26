<?php

class MockGeneratorTypedPropertiesAdHoc
implements \Eloquent\Phony\Mock\Mock
{
    public $propertyA = 'a';
    public string $propertyB = 'b';
    public ?int $propertyC = null;
    private readonly \Eloquent\Phony\Mock\Handle\InstanceHandle $_handle;
}
