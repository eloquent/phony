<?php

class MockGeneratorPrivateConstructor
extends \Eloquent\Phony\Test\TestClassD
implements \Eloquent\Phony\Mock\Mock
{
    public function __construct()
    {
    }

    private static function _callParentStatic(
        $name,
        \Eloquent\Phony\Call\Arguments $arguments
    ) {
        return parent::$name(...$arguments->all());
    }

    private function _callParent(
        $name,
        \Eloquent\Phony\Call\Arguments $arguments
    ) {
        return parent::$name(...$arguments->all());
    }

    private function _callParentConstructor(
        \Eloquent\Phony\Call\Arguments $arguments
    ) {
        $constructor = new ReflectionMethod('Eloquent\\Phony\\Test\\TestClassD', "__construct");
        $constructor->setAccessible(true);
        $constructor->invokeArgs($this,$arguments->all());
    }

    private readonly \Eloquent\Phony\Mock\Handle\InstanceHandle $_handle;
}
