<?php

class MockGeneratorOldConstructor
extends \TestClassOldConstructor
implements \Eloquent\Phony\Mock\Mock
{
    public function __construct()
    {
    }

    private static function _callParentStatic(
        $name,
        \Eloquent\Phony\Call\Arguments $arguments
    ) {
        return \call_user_func_array(
            [__CLASS__, 'parent::' . $name],
            $arguments->all()
        );
    }

    private function _callParent(
        $name,
        \Eloquent\Phony\Call\Arguments $arguments
    ) {
        return \call_user_func_array(
            [$this, 'parent::' . $name],
            $arguments->all()
        );
    }

    private function _callParentConstructor(
        \Eloquent\Phony\Call\Arguments $arguments
    ) {
        \call_user_func_array(
            [$this, 'parent::TestClassOldConstructor'],
            $arguments->all()
        );
    }

    private static $_uncallableMethods = [];
    private static $_traitMethods = [];
    private static $_customMethods = [];
    private static $_staticHandle;
    private $_handle;
}
