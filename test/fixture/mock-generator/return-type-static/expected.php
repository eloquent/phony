<?php

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Mock\Handle\StaticHandleRegistry;
use Eloquent\Phony\Mock\Mock;

class MockGeneratorStaticReturnType
implements Mock,
           \Eloquent\Phony\Test\TestInterfaceWithStaticReturnType
{
    public static function staticMethod() : static
    {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        for ($¤i = 0; $¤i < $¤argumentCount; ++$¤i) {
            $¤arguments[] = \func_get_arg($¤i);
        }

        if (isset(StaticHandleRegistry::$handles['mockgeneratorstaticreturntype'])) {
            $¤result = StaticHandleRegistry::$handles['mockgeneratorstaticreturntype']->spy(__FUNCTION__)->invokeWith(
                new Arguments($¤arguments)
            );

            return $¤result;
        } else {
            $¤result = null;

            return $¤result;
        }
    }

    public static function __callStatic(
        $name,
        array $arguments
    ) : static {
        $¤result = StaticHandleRegistry::$handles['mockgeneratorstaticreturntype']->spy($name)
            ->invokeWith(new Arguments($arguments));

        return $¤result;
    }

    public function method() : static
    {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        for ($¤i = 0; $¤i < $¤argumentCount; ++$¤i) {
            $¤arguments[] = \func_get_arg($¤i);
        }

        if (isset($this->_handle)) {
            $¤result = $this->_handle->spy(__FUNCTION__)->invokeWith(
                new Arguments($¤arguments)
            );

            return $¤result;
        } else {
            $¤result = null;

            return $¤result;
        }
    }

    public function __call(
        $name,
        array $arguments
    ) : static {
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
