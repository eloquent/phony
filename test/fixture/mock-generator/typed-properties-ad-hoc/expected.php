<?php

class MockGeneratorTypedPropertiesAdHoc
implements \Eloquent\Phony\Mock\Mock
{
    public $propertyA = 'a';
    public string $propertyB = 'b';
    public ?int $propertyC = null;
    private static $_uncallableMethods = [];
    private static $_traitMethods = [];
    private static $_customMethods = [];
    private static $_staticHandle;
    private readonly \Eloquent\Phony\Mock\Handle\InstanceHandle $_handle;
}
