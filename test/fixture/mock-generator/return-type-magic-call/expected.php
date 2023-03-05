<?php

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Mock\Handle\StaticHandleRegistry;
use Eloquent\Phony\Mock\Mock;

class MockGeneratorReturnTypeMagicCall
implements Mock
{
    public static function __callStatic(
        $name,
        array $arguments
    ) : \stdClass {
        $¤result = StaticHandleRegistry::$handles['mockgeneratorreturntypemagiccall']->spy($name)
            ->invokeWith(new Arguments($arguments));

        return $¤result;
    }

    public function __call(
        $name,
        array $arguments
    ) : \stdClass {
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
