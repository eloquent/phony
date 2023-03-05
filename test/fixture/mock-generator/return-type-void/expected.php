<?php

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Mock\Handle\StaticHandleRegistry;
use Eloquent\Phony\Mock\Mock;

class MockGeneratorVoidReturnType
implements Mock,
           \Eloquent\Phony\Test\TestInterfaceWithVoidReturnType
{
    public static function staticMethod() : void
    {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        for ($¤i = 0; $¤i < $¤argumentCount; ++$¤i) {
            $¤arguments[] = \func_get_arg($¤i);
        }

        if (isset(StaticHandleRegistry::$handles['mockgeneratorvoidreturntype'])) {
            StaticHandleRegistry::$handles['mockgeneratorvoidreturntype']->spy(__FUNCTION__)->invokeWith(
                new Arguments($¤arguments)
            );
        }
    }

    public static function __callStatic(
        $name,
        array $arguments
    ) : void {
        StaticHandleRegistry::$handles['mockgeneratorvoidreturntype']->spy($name)
            ->invokeWith(new Arguments($arguments));
    }

    public function method() : void
    {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        for ($¤i = 0; $¤i < $¤argumentCount; ++$¤i) {
            $¤arguments[] = \func_get_arg($¤i);
        }

        if (isset($this->_handle)) {
            $this->_handle->spy(__FUNCTION__)->invokeWith(
                new Arguments($¤arguments)
            );
        }
    }

    public function customMethod() : void
    {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        for ($¤i = 0; $¤i < $¤argumentCount; ++$¤i) {
            $¤arguments[] = \func_get_arg($¤i);
        }

        if (isset($this->_handle)) {
            $this->_handle->spy(__FUNCTION__)->invokeWith(
                new Arguments($¤arguments)
            );
        }
    }

    public function __call(
        $name,
        array $arguments
    ) : void {
        $this->_handle->spy($name)
            ->invokeWith(new Arguments($arguments));
    }

    private static function _callMagicStatic(
        $name,
        Arguments $arguments
    ) {}

    private function _callMagic(
        $name,
        Arguments $arguments
    ) {}

    private readonly InstanceHandle $_handle;
}
