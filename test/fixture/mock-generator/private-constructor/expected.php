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

    private function _callParentConstructor(
        \Eloquent\Phony\Call\Arguments $arguments
    ) {
        $constructor = function () use ($arguments) {
            \call_user_func_array(
                array($this, 'parent::__construct'),
                $arguments->all()
            );
        };
        $constructor = $constructor->bindTo($this, 'Eloquent\Phony\Test\TestClassD');
        $constructor();
    }

    private static $_uncallableMethods = array();
    private static $_traitMethods = array();
    private static $_customMethods = array();
    private static $_staticHandle;
    private $_handle;
}
