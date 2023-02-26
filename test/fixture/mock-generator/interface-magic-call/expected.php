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
        $a0,
        array $a1
    ) {
        $result = StaticHandleRegistry::$handles['mockgeneratorinterfacemagiccall']->spy($a0)
            ->invokeWith(new Arguments($a1));

        return $result;
    }

    public function __call(
        $a0,
        array $a1
    ) {
        $result = $this->_handle->spy($a0)
            ->invokeWith(new Arguments($a1));

        return $result;
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
