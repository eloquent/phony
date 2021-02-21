<?php

class MockGeneratorReturnType
implements \Eloquent\Phony\Mock\Mock,
           \Eloquent\Phony\Test\TestInterfaceWithReturnType
{
    public static function __callStatic(
        $name,
        array $arguments
    ) : string {
        $¢result = self::$_staticHandle
            ->spy($name)
            ->invokeWith(
                new \Eloquent\Phony\Call\Arguments($arguments)
            );

        return $¢result;
    }

    public function classType() : \Eloquent\Phony\Test\TestClassA
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($¢i = 0; $¢i < $¢argumentCount; ++$¢i) {
            $¢arguments[] = \func_get_arg($¢i);
        }

        if (!$this->_handle) {
            $¢result = null;

            return $¢result;
        }

        $¢result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $¢result;
    }

    public function scalarType() : int
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($¢i = 0; $¢i < $¢argumentCount; ++$¢i) {
            $¢arguments[] = \func_get_arg($¢i);
        }

        if (!$this->_handle) {
            $¢result = null;

            return $¢result;
        }

        $¢result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $¢result;
    }

    public function customMethodWithClassType() : \stdClass
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($¢i = 0; $¢i < $¢argumentCount; ++$¢i) {
            $¢arguments[] = \func_get_arg($¢i);
        }

        if (!$this->_handle) {
            $¢result = null;

            return $¢result;
        }

        $¢result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $¢result;
    }

    public function customMethodWithScalarType() : int
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($¢i = 0; $¢i < $¢argumentCount; ++$¢i) {
            $¢arguments[] = \func_get_arg($¢i);
        }

        if (!$this->_handle) {
            $¢result = null;

            return $¢result;
        }

        $¢result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $¢result;
    }

    public function __call(
        $name,
        array $arguments
    ) : string {
        $¢result = $this->_handle
            ->spy($name)
            ->invokeWith(
                new \Eloquent\Phony\Call\Arguments($arguments)
            );

        return $¢result;
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
  'classtype' => true,
  'scalartype' => true,
  '__call' => true,
  '__callstatic' => true,
);
    private static $_traitMethods = [];
    private static $_customMethods = [];
    private static $_staticHandle;
    private $_handle;
}
