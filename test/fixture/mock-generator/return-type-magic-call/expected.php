<?php

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Mock\Handle\StaticHandleRegistry;
use Eloquent\Phony\Mock\Mock;

class MockGeneratorReturnTypeMagicCall
implements Mock
{
    public static function __callStatic(
        $a0,
        array $a1
    ) : \stdClass {
        $result = StaticHandleRegistry::$handles['mockgeneratorreturntypemagiccall']->spy($a0)
            ->invokeWith(new Arguments($a1));

        return $result;
    }

    public function __call(
        $a0,
        array $a1
    ) : \stdClass {
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
