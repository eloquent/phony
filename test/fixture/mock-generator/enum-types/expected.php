<?php

class MockGeneratorEnumTypes
implements \Eloquent\Phony\Mock\Mock,
           \Eloquent\Phony\Test\Php81\TestInterfaceUsingEnums
{
    public static function staticMethodA() : \Eloquent\Phony\Test\Php81\TestBasicEnum
    {
        $argumentCount = \func_num_args();
        $arguments = [];

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        if (self::$_staticHandle) {
            $result = self::$_staticHandle->spy(__FUNCTION__)->invokeWith(
                new \Eloquent\Phony\Call\Arguments($arguments)
            );

            return $result;
        } else {
            $result = null;

            return $result;
        }
    }

    public static function staticMethodB(
        \Eloquent\Phony\Test\Php81\TestBasicEnum $a0
    ) : \Eloquent\Phony\Test\Php81\TestBasicEnum {
        $argumentCount = \func_num_args();
        $arguments = [];

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }

        for ($i = 1; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        if (self::$_staticHandle) {
            $result = self::$_staticHandle->spy(__FUNCTION__)->invokeWith(
                new \Eloquent\Phony\Call\Arguments($arguments)
            );

            return $result;
        } else {
            $result = null;

            return $result;
        }
    }

    public static function staticMethodC() : \Eloquent\Phony\Test\Php81\TestBackedEnum
    {
        $argumentCount = \func_num_args();
        $arguments = [];

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        if (self::$_staticHandle) {
            $result = self::$_staticHandle->spy(__FUNCTION__)->invokeWith(
                new \Eloquent\Phony\Call\Arguments($arguments)
            );

            return $result;
        } else {
            $result = null;

            return $result;
        }
    }

    public static function staticMethodD(
        \Eloquent\Phony\Test\Php81\TestBackedEnum $a0
    ) : \Eloquent\Phony\Test\Php81\TestBackedEnum {
        $argumentCount = \func_num_args();
        $arguments = [];

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }

        for ($i = 1; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        if (self::$_staticHandle) {
            $result = self::$_staticHandle->spy(__FUNCTION__)->invokeWith(
                new \Eloquent\Phony\Call\Arguments($arguments)
            );

            return $result;
        } else {
            $result = null;

            return $result;
        }
    }

    public function methodA() : \Eloquent\Phony\Test\Php81\TestBasicEnum
    {
        $argumentCount = \func_num_args();
        $arguments = [];

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        if ($this->_handle) {
            $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
                new \Eloquent\Phony\Call\Arguments($arguments)
            );

            return $result;
        } else {
            $result = null;

            return $result;
        }
    }

    public function methodB(
        \Eloquent\Phony\Test\Php81\TestBasicEnum $a0
    ) : \Eloquent\Phony\Test\Php81\TestBasicEnum {
        $argumentCount = \func_num_args();
        $arguments = [];

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }

        for ($i = 1; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        if ($this->_handle) {
            $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
                new \Eloquent\Phony\Call\Arguments($arguments)
            );

            return $result;
        } else {
            $result = null;

            return $result;
        }
    }

    public function methodC() : \Eloquent\Phony\Test\Php81\TestBackedEnum
    {
        $argumentCount = \func_num_args();
        $arguments = [];

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        if ($this->_handle) {
            $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
                new \Eloquent\Phony\Call\Arguments($arguments)
            );

            return $result;
        } else {
            $result = null;

            return $result;
        }
    }

    public function methodD(
        \Eloquent\Phony\Test\Php81\TestBackedEnum $a0
    ) : \Eloquent\Phony\Test\Php81\TestBackedEnum {
        $argumentCount = \func_num_args();
        $arguments = [];

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }

        for ($i = 1; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        if ($this->_handle) {
            $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
                new \Eloquent\Phony\Call\Arguments($arguments)
            );

            return $result;
        } else {
            $result = null;

            return $result;
        }
    }

    private static $_uncallableMethods = array (
  'staticmethoda' => true,
  'staticmethodb' => true,
  'staticmethodc' => true,
  'staticmethodd' => true,
  'methoda' => true,
  'methodb' => true,
  'methodc' => true,
  'methodd' => true,
);
    private static $_traitMethods = [];
    private static $_customMethods = [];
    private static $_staticHandle;
    private $_handle;
}
