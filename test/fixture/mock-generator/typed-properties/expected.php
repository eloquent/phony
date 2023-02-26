<?php

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Mock\Handle\StaticHandleRegistry;
use Eloquent\Phony\Mock\Mock;

class MockGeneratorTypedProperties
extends \Eloquent\Phony\Test\TestClassWithTypedProperties
implements Mock
{
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

    private readonly InstanceHandle $_handle;
}
