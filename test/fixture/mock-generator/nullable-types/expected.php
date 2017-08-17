<?php

class MockGeneratorNullableTypes
implements \Eloquent\Phony\Mock\Mock,
           \Eloquent\Phony\Test\TestInterfaceWithNullableTypes
{
    public static function customStaticMethod(
        string $a0 = null,
        \stdClass $a1 = null
    ) : ?\TestClassA {
        $argumentCount = \func_num_args();
        $arguments = [];

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }
        if ($argumentCount > 1) {
            $arguments[] = $a1;
        }

        for ($i = 2; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        if (!self::$_staticHandle) {
            $result = parent::customStaticMethod(...$arguments);

            return $result;
        }

        $result = self::$_staticHandle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($arguments)
        );

        return $result;
    }

    public static function __callStatic(
        $a0,
        array $a1
    ) : ?\Eloquent\Phony\Test\TestClassA {
        $result = self::$_staticHandle->spy($a0)
            ->invokeWith(new \Eloquent\Phony\Call\Arguments($a1));

        return $result;
    }

    public function staticMethodA(
        string $a0 = null,
        \stdClass $a1 = null
    ) : ?\Eloquent\Phony\Test\TestClassA {
        $argumentCount = \func_num_args();
        $arguments = [];

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }
        if ($argumentCount > 1) {
            $arguments[] = $a1;
        }

        for ($i = 2; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::staticMethodA(...$arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($arguments)
        );

        return $result;
    }

    public function staticMethodB() : ?\Eloquent\Phony\Test\TestClassB
    {
        $argumentCount = \func_num_args();
        $arguments = [];

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::staticMethodB(...$arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($arguments)
        );

        return $result;
    }

    public function methodA(
        string $a0 = null,
        \stdClass $a1 = null
    ) : ?\Eloquent\Phony\Test\TestClassA {
        $argumentCount = \func_num_args();
        $arguments = [];

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }
        if ($argumentCount > 1) {
            $arguments[] = $a1;
        }

        for ($i = 2; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::methodA(...$arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($arguments)
        );

        return $result;
    }

    public function methodB() : ?\Eloquent\Phony\Test\TestClassB
    {
        $argumentCount = \func_num_args();
        $arguments = [];

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::methodB(...$arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($arguments)
        );

        return $result;
    }

    public function customMethod(
        string $a0 = null,
        \stdClass $a1 = null
    ) : ?\TestClassA {
        $argumentCount = \func_num_args();
        $arguments = [];

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }
        if ($argumentCount > 1) {
            $arguments[] = $a1;
        }

        for ($i = 2; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = parent::customMethod(...$arguments);

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($arguments)
        );

        return $result;
    }

    public function __call(
        $a0,
        array $a1
    ) : ?\Eloquent\Phony\Test\TestClassA {
        $result = $this->_handle->spy($a0)
            ->invokeWith(new \Eloquent\Phony\Call\Arguments($a1));

        return $result;
    }

    private static function _callMagicStatic(
        $name,
        \Eloquent\Phony\Call\Arguments $arguments
    ) {
        return parent::__callStatic($name, $arguments->all());
    }

    private function _callMagic(
        $name,
        \Eloquent\Phony\Call\Arguments $arguments
    ) {
        return parent::__call($name, $arguments->all());
    }

    private static $_uncallableMethods = array (
  'staticmethoda' => true,
  'staticmethodb' => true,
  'methoda' => true,
  'methodb' => true,
  '__call' => true,
  '__callstatic' => true,
);
    private static $_traitMethods = [];
    private static $_customMethods = [];
    private static $_staticHandle;
    private $_handle;
}
