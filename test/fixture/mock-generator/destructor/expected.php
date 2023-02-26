<?php

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Mock\Handle\StaticHandleRegistry;
use Eloquent\Phony\Mock\Mock;

class MockGeneratorDestructor
extends \Eloquent\Phony\Test\TestClassJ
implements Mock
{
    public function __destruct()
    {
        if (isset($this->_handle)) {
            $this->_handle->spy('__destruct')->invokeWith([]);
        } else {
            parent::__destruct();
        }
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

    private readonly InstanceHandle $_handle;
}
