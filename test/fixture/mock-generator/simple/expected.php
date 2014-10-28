<?php

class MockGeneratorSimple
extends \stdClass
implements \Eloquent\Phony\Mock\MockInterface
{
    private static function _callParentStatic(
        $name,
        \Eloquent\Phony\Call\Argument\ArgumentsInterface $arguments
    ) {
        return \call_user_func_array(
            array(__CLASS__, 'parent::' . $name),
            $arguments->all()
        );
    }

    private function _callParent(
        $name,
        \Eloquent\Phony\Call\Argument\ArgumentsInterface $arguments
    ) {
        return \call_user_func_array(
            array($this, 'parent::' . $name),
            $arguments->all()
        );
    }

    private static $_customMethods = array();
    private static $_staticProxy;
    private $_proxy;
}
