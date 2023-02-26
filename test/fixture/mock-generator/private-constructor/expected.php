<?php

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Mock\Handle\StaticHandleRegistry;
use Eloquent\Phony\Mock\Mock;

class MockGeneratorPrivateConstructor
extends \Eloquent\Phony\Test\TestClassD
implements Mock
{
    public function __construct()
    {
    }

    private static function _callParentStatic(
        $name,
        Arguments $arguments
    ) {
        return parent::$name(...$arguments->all());
    }

    private function _callParent(
        $name,
        Arguments $arguments
    ) {
        return parent::$name(...$arguments->all());
    }

    private function _callParentConstructor(
        Arguments $arguments
    ) {
        $constructor = new ReflectionMethod('Eloquent\\Phony\\Test\\TestClassD', "__construct");
        $constructor->setAccessible(true);
        $constructor->invokeArgs($this,$arguments->all());
    }

    private readonly InstanceHandle $_handle;
}
