<?php

class MockGeneratorSelfKeyword
extends \Eloquent\Phony\Test\TestClassC
implements \Eloquent\Phony\Mock\Mock
{
    public function methodA(
        \Eloquent\Phony\Test\TestClassC $first,
        $second = 'a'
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $first;
        }
        if ($¢argumentCount > 1) {
            $¢arguments[] = $second;
        }

        for ($i = 2; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::methodA(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function methodB(
        $first,
        $second = 111,
        $third = 'second'
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $first;
        }
        if ($¢argumentCount > 1) {
            $¢arguments[] = $second;
        }
        if ($¢argumentCount > 2) {
            $¢arguments[] = $third;
        }

        for ($i = 3; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::methodB(...$¢arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
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
