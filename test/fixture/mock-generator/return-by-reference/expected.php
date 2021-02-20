<?php

class MockGeneratorReturnByReference
extends \Eloquent\Phony\Test\TestClassG
implements \Eloquent\Phony\Mock\Mock
{
    public static function &testClassGStaticMethodA(
        $a0,
        &$a1,
        &$a2
    ) {
        $argumentCount = \func_num_args();
        $arguments = [];

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }
        if ($argumentCount > 1) {
            $arguments[] = &$a1;
        }
        if ($argumentCount > 2) {
            $arguments[] = &$a2;
        }

        for ($i = 3; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        if (!self::$_staticHandle) {
            $result = parent::testClassGStaticMethodA(...$arguments);

            return $result;
        }

        $result = self::$_staticHandle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($arguments)
        );

        return $result;
    }

    public static function &__callStatic(
        $name,
        array $arguments
    ) {
        $result = self::$_staticHandle
            ->spy($name)
            ->invokeWith(
                new \Eloquent\Phony\Call\Arguments($arguments)
            );

        return $result;
    }

    public function &testClassGMethodA(
        $a0,
        &$a1,
        &$a2
    ) {
        $argumentCount = \func_num_args();
        $arguments = [];

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }
        if ($argumentCount > 1) {
            $arguments[] = &$a1;
        }
        if ($argumentCount > 2) {
            $arguments[] = &$a2;
        }

        for ($i = 3; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::testClassGMethodA(...$arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($arguments)
        );

        return $result;
    }

    public function &__call(
        $name,
        array $arguments
    ) {
        $result = $this->_handle
            ->spy($name)
            ->invokeWith(
                new \Eloquent\Phony\Call\Arguments($arguments)
            );

        return $result;
    }

    private static function _callParentStatic(
        $name,
        \Eloquent\Phony\Call\Arguments $arguments
    ) {
        return parent::$name(...$arguments->all());
    }

    private static function _callMagicStatic(
        $name,
        \Eloquent\Phony\Call\Arguments $arguments
    ) {
        return parent::__callStatic($name, $arguments->all());
    }

    private function _callParent(
        $name,
        \Eloquent\Phony\Call\Arguments $arguments
    ) {
        return parent::$name(...$arguments->all());
    }

    private function _callMagic(
        $name,
        \Eloquent\Phony\Call\Arguments $arguments
    ) {
        return parent::__call($name, $arguments->all());
    }

    private static $_uncallableMethods = [];
    private static $_traitMethods = [];
    private static $_customMethods = [];
    private static $_staticHandle;
    private $_handle;
}
