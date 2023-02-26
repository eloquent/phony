<?php

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Mock\Handle\StaticHandleRegistry;
use Eloquent\Phony\Mock\Mock;

class MockGeneratorSelfReturnType
implements Mock,
           \Eloquent\Phony\Test\TestInterfaceWithSelfReturnType
{
    public static function staticMethod() : \Eloquent\Phony\Test\TestInterfaceWithSelfReturnType
    {
        $argumentCount = \func_num_args();
        $arguments = [];

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        if (isset(StaticHandleRegistry::$handles['mockgeneratorselfreturntype'])) {
            $result = StaticHandleRegistry::$handles['mockgeneratorselfreturntype']->spy(__FUNCTION__)->invokeWith(
                new Arguments($arguments)
            );

            return $result;
        } else {
            $result = null;

            return $result;
        }
    }

    public static function __callStatic(
        $a0,
        array $a1
    ) : \Eloquent\Phony\Test\TestInterfaceWithSelfReturnType {
        $result = StaticHandleRegistry::$handles['mockgeneratorselfreturntype']->spy($a0)
            ->invokeWith(new Arguments($a1));

        return $result;
    }

    public function method() : \Eloquent\Phony\Test\TestInterfaceWithSelfReturnType
    {
        $argumentCount = \func_num_args();
        $arguments = [];

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        if (isset($this->_handle)) {
            $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
                new Arguments($arguments)
            );

            return $result;
        } else {
            $result = null;

            return $result;
        }
    }

    public function __call(
        $a0,
        array $a1
    ) : \Eloquent\Phony\Test\TestInterfaceWithSelfReturnType {
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
