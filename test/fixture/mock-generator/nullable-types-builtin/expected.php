<?php

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Mock\Handle\StaticHandleRegistry;
use Eloquent\Phony\Mock\Mock;

class MockGeneratorNullableBultinTypes
implements Mock,
           \Eloquent\Phony\Test\TestInterfaceWithNullableBuiltinTypes
{
    public static function customStaticMethod(
        ?string $string,
        ?\stdClass $object
    ) : ?\TestClassA {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        if ($¤argumentCount > 0) {
            $¤arguments[] = $string;
        }
        if ($¤argumentCount > 1) {
            $¤arguments[] = $object;
        }
        for ($¤i = 2; $¤i < $¤argumentCount; ++$¤i) {
            $¤arguments[] = \func_get_arg($¤i);
        }

        if (isset(StaticHandleRegistry::$handles['mockgeneratornullablebultintypes'])) {
            $¤result = StaticHandleRegistry::$handles['mockgeneratornullablebultintypes']->spy(__FUNCTION__)->invokeWith(
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
    ) : ?int {
        $¤result = StaticHandleRegistry::$handles['mockgeneratornullablebultintypes']->spy($name)
            ->invokeWith(new Arguments($arguments));

        return $¤result;
    }

    public function staticMethodA(
        ?string $string,
        ?int $object
    ) : ?bool {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        if ($¤argumentCount > 0) {
            $¤arguments[] = $string;
        }
        if ($¤argumentCount > 1) {
            $¤arguments[] = $object;
        }
        for ($¤i = 2; $¤i < $¤argumentCount; ++$¤i) {
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

    public function staticMethodB() : ?int
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

    public function methodA(
        ?string $string,
        ?int $object
    ) : ?bool {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        if ($¤argumentCount > 0) {
            $¤arguments[] = $string;
        }
        if ($¤argumentCount > 1) {
            $¤arguments[] = $object;
        }
        for ($¤i = 2; $¤i < $¤argumentCount; ++$¤i) {
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

    public function methodB() : ?int
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

    public function customMethod(
        ?string $string,
        ?\stdClass $object
    ) : ?\TestClassA {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        if ($¤argumentCount > 0) {
            $¤arguments[] = $string;
        }
        if ($¤argumentCount > 1) {
            $¤arguments[] = $object;
        }
        for ($¤i = 2; $¤i < $¤argumentCount; ++$¤i) {
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
    ) : ?int {
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
