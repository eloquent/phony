<?php

class MockGeneratorOldConstructor
extends \TestClassOldConstructor
implements \Eloquent\Phony\Mock\MockInterface
{
    public function __construct()
    {
    }

    private static function _callParentStatic(
        $name,
        \Eloquent\Phony\Call\Argument\ArgumentsInterface $arguments
    ) {
        $callback = array(__CLASS__, 'parent::' . $name);

        return \call_user_func_array($callback, $arguments->all());
    }

    private function _callParent(
        $name,
        \Eloquent\Phony\Call\Argument\ArgumentsInterface $arguments
    ) {
        $callback = array($this, 'parent::' . $name);

        return \call_user_func_array($callback, $arguments->all());
    }

    private static $_customMethods = array();
    private static $_staticProxy;
    private $_proxy;
}
