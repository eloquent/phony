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

        if (isset(self::$_staticHandle)) {
            $result = self::$_staticHandle->spy(__FUNCTION__)->invokeWith(
                new \Eloquent\Phony\Call\Arguments($arguments)
            );

            return $result;
        } else {
            $result = parent::testClassGStaticMethodA(...$arguments);

            return $result;
        }
    }

    public static function &__callStatic(
        $a0,
        array $a1
    ) {
        $result = self::$_staticHandle->spy($a0)
            ->invokeWith(new \Eloquent\Phony\Call\Arguments($a1));

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

        if (isset($this->_handle)) {
            $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
                new \Eloquent\Phony\Call\Arguments($arguments)
            );

            return $result;
        } else {
            $result = parent::testClassGMethodA(...$arguments);

            return $result;
        }
    }

    public function &__call(
        $a0,
        array $a1
    ) {
        $result = $this->_handle->spy($a0)
            ->invokeWith(new \Eloquent\Phony\Call\Arguments($a1));

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

    private static $_staticHandle;
    private readonly \Eloquent\Phony\Mock\Handle\InstanceHandle $_handle;
}
