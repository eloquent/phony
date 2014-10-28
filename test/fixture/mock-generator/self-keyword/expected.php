<?php

class MockGeneratorSelfKeyword
extends \Eloquent\Phony\Test\TestClassC
implements \Eloquent\Phony\Mock\MockInterface
{
    public function methodA(
        \Eloquent\Phony\Test\TestClassC $a0,
        $a1 = \Eloquent\Phony\Test\TestClassC::CONSTANT_A
    ) {
        $argumentCount = func_num_args();
        $arguments = array();

        if ($argumentCount > 0) $arguments[] = $a0;
        if ($argumentCount > 1) $arguments[] = $a1;

        for ($i = 2; $i < $argumentCount; $i++) {
            $arguments[] = func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function methodB(
        $a0,
        $a1 = 111,
        $a2 = 'second'
    ) {
        $argumentCount = func_num_args();
        $arguments = array();

        if ($argumentCount > 0) $arguments[] = $a0;
        if ($argumentCount > 1) $arguments[] = $a1;
        if ($argumentCount > 2) $arguments[] = $a2;

        for ($i = 3; $i < $argumentCount; $i++) {
            $arguments[] = func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

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
