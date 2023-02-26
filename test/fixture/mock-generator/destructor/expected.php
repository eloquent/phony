<?php

class MockGeneratorDestructor
extends \Eloquent\Phony\Test\TestClassJ
implements \Eloquent\Phony\Mock\Mock
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

    private static $_staticHandle;
    private readonly \Eloquent\Phony\Mock\Handle\InstanceHandle $_handle;
}
