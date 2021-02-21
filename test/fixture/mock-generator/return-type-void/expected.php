<?php

class MockGeneratorVoidReturnType
implements \Eloquent\Phony\Mock\Mock,
           \Eloquent\Phony\Test\TestInterfaceWithVoidReturnType
{
    public static function staticMethod() : void
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($¢i = 0; $¢i < $¢argumentCount; ++$¢i) {
            $¢arguments[] = \func_get_arg($¢i);
        }

        if (!self::$_staticHandle) {
            null;

            return;
        }

        self::$_staticHandle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );
    }

    public static function __callStatic(
        $name,
        array $arguments
    ) : void {
        self::$_staticHandle
            ->spy($name)
            ->invokeWith(
                new \Eloquent\Phony\Call\Arguments($arguments)
            );
    }

    public function method() : void
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($¢i = 0; $¢i < $¢argumentCount; ++$¢i) {
            $¢arguments[] = \func_get_arg($¢i);
        }

        if (!$this->_handle) {
            null;

            return;
        }

        $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );
    }

    public function customMethod() : void
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($¢i = 0; $¢i < $¢argumentCount; ++$¢i) {
            $¢arguments[] = \func_get_arg($¢i);
        }

        if (!$this->_handle) {
            null;

            return;
        }

        $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );
    }

    public function __call(
        $name,
        array $arguments
    ) : void {
        $this->_handle
            ->spy($name)
            ->invokeWith(
                new \Eloquent\Phony\Call\Arguments($arguments)
            );
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
