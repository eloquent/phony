<?php

class MockGeneratorSelfReturnType
implements \Eloquent\Phony\Mock\Mock,
           \Eloquent\Phony\Test\TestInterfaceWithSelfReturnType
{
    public static function staticMethod() : \Eloquent\Phony\Test\TestInterfaceWithSelfReturnType
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($i = 0; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!self::$_staticHandle) {
            $result = null;

            return $result;
        }

        $result = self::$_staticHandle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public static function __callStatic(
        $name,
        array $arguments
    ) : \Eloquent\Phony\Test\TestInterfaceWithSelfReturnType {
        $result = self::$_staticHandle
            ->spy($name)
            ->invokeWith(
                new \Eloquent\Phony\Call\Arguments($arguments)
            );

        return $result;
    }

    public function method() : \Eloquent\Phony\Test\TestInterfaceWithSelfReturnType
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($i = 0; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = null;

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function __call(
        $name,
        array $arguments
    ) : \Eloquent\Phony\Test\TestInterfaceWithSelfReturnType {
        $result = $this->_handle
            ->spy($name)
            ->invokeWith(
                new \Eloquent\Phony\Call\Arguments($arguments)
            );

        return $result;
    }

    private static function _callMagicStatic(
        $name,
        \Eloquent\Phony\Call\Arguments $arguments
    ) {}

    private function _callMagic(
        $name,
        \Eloquent\Phony\Call\Arguments $arguments
    ) {}

    private static $_uncallableMethods = array (
  'staticmethod' => true,
  '__callstatic' => true,
  'method' => true,
  '__call' => true,
);
    private static $_traitMethods = [];
    private static $_customMethods = [];
    private static $_staticHandle;
    private $_handle;
}
