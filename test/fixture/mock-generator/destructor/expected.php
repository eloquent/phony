<?php

class MockGeneratorDestructor
extends \Eloquent\Phony\Test\TestClassJ
implements \Eloquent\Phony\Mock\Mock
{
    public function __destruct()
    {
        if (!$this->_handle) {
            parent::__destruct();

            return;
        }

        $this->_handle->spy('__destruct')->invokeWith([]);
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

    private static $_uncallableMethods = [];
    private static $_traitMethods = [];
    private static $_customMethods = [];
    private static $_staticHandle;
    private $_handle;
}
