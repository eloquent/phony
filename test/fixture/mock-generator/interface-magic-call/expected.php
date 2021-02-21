<?php

class MockGeneratorInterfaceMagicCall
implements \Eloquent\Phony\Mock\Mock,
           \Eloquent\Phony\Test\TestInterfaceD
{
    public static function __callStatic(
        $name,
        array $arguments
    ) {
        $¢result = self::$_staticHandle
            ->spy($name)
            ->invokeWith(
                new \Eloquent\Phony\Call\Arguments($arguments)
            );

        return $¢result;
    }

    public function __call(
        $name,
        array $arguments
    ) {
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
  '__callstatic' => true,
  '__call' => true,
);
    private static $_traitMethods = [];
    private static $_customMethods = [];
    private static $_staticHandle;
    private $_handle;
}
