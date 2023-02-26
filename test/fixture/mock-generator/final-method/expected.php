<?php

namespace Phony\Test;

class MockGeneratorFinalMethod
extends \Eloquent\Phony\Test\TestClassF
implements \Eloquent\Phony\Mock\Mock
{
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

    private readonly \Eloquent\Phony\Mock\Handle\InstanceHandle $_handle;
}
