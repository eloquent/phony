<?php

class MockGeneratorReadonlyPropertiesAdHoc
implements \Eloquent\Phony\Mock\Mock
{
    public readonly string $propertyA;
    public readonly ?int $propertyB;
    private static $_uncallableMethods = [];
    private static $_traitMethods = [];
    private static $_customMethods = [];
    private static $_staticHandle;
    private readonly \Eloquent\Phony\Mock\Handle\InstanceHandle $_handle;
}
