<?php

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Mock\Handle\StaticHandleRegistry;
use Eloquent\Phony\Mock\Mock;

class MockGeneratorReturnType
implements Mock,
           \Eloquent\Phony\Test\TestInterfaceWithReturnType
{
    public static function __callStatic(
        $a0,
        array $a1
    ) : string {
        $result = StaticHandleRegistry::$handles['mockgeneratorreturntype']->spy($a0)
            ->invokeWith(new Arguments($a1));

        return $result;
    }

    public function classType() : \Eloquent\Phony\Test\TestClassA
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

    public function scalarType() : int
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

    public function customMethodWithClassType() : \stdClass
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

    public function customMethodWithScalarType() : int
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
    ) : string {
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
