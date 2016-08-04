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

        $this->_handle->spy('__destruct')->invokeWith(array());
    }

    private static function _callParentStatic(
        $name,
        \Eloquent\Phony\Call\Arguments $arguments
    ) {
        return \call_user_func_array(
            array(__CLASS__, 'parent::' . $name),
            $arguments->all()
        );
    }

    private function _callParent(
        $name,
        \Eloquent\Phony\Call\Arguments $arguments
    ) {
        return \call_user_func_array(
            array($this, 'parent::' . $name),
            $arguments->all()
        );
    }

    private static $_uncallableMethods = array();
    private static $_traitMethods = array();
    private static $_customMethods = array();
    private static $_staticHandle;
    private $_handle;
}
