<?php

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Mock\Handle\StaticHandleRegistry;
use Eloquent\Phony\Mock\Mock;

class MockGeneratorInterfaceMagicCall
implements Mock,
           \Eloquent\Phony\Test\TestInterfaceD
{
    public static function __callStatic(
        $name,
        array $arguments
    ) {
        $¤result = StaticHandleRegistry::$handles['mockgeneratorinterfacemagiccall']->spy($name)
            ->invokeWith(new Arguments($arguments));

        return $¤result;
    }

    public function __call(
        $name,
        array $arguments
    ) {
        $¤result = $this->_handle->spy($name)
            ->invokeWith(new Arguments($arguments));

        return $¤result;
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
