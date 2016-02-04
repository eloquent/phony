<?php

class MockGeneratorReturnByReference
extends \Eloquent\Phony\Test\TestClassG
implements \Eloquent\Phony\Mock\MockInterface
{
    public static function &testClassGStaticMethodA(
        $a0,
        &$a1,
        &$a2
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

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

        $result = self::$_staticHandle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public static function &__callStatic(
        $a0,
        array $a1
    ) {
        $result = self::$_staticHandle->spy($a0)
            ->invokeWith(new \Eloquent\Phony\Call\Argument\Arguments($a1));

        return $result;
    }

    public function &testClassGMethodA(
        $a0,
        &$a1,
        &$a2
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

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

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function &__call(
        $a0,
        array $a1
    ) {
        $result = $this->_handle->spy($a0)
            ->invokeWith(new \Eloquent\Phony\Call\Argument\Arguments($a1));

        return $result;
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

    private static function _callMagicStatic(
        $name,
        \Eloquent\Phony\Call\Argument\ArgumentsInterface $arguments
    ) {
        return self::$_staticHandle
            ->spy('__callStatic')->invoke($name, $arguments->all());
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

    private function _callMagic(
        $name,
        \Eloquent\Phony\Call\Argument\ArgumentsInterface $arguments
    ) {
        return \call_user_func_array(
            array($this, 'parent::__call'),
            array($name, $arguments->all())
        );
    }

    private static $_uncallableMethods = array();
    private static $_traitMethods = array();
    private static $_customMethods = array();
    private static $_staticHandle;
    private $_handle;
}
