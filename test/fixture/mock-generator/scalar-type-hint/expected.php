<?php

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Mock\Handle\StaticHandleRegistry;
use Eloquent\Phony\Mock\Mock;

class MockGeneratorScalarTypeHint
implements Mock,
           \Eloquent\Phony\Test\TestInterfaceWithScalarTypeHint
{
    public function method(
        int $a,
        float $b,
        string $c,
        bool $d
    ) {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        if ($¤argumentCount > 0) {
            $¤arguments[] = $a;
        }
        if ($¤argumentCount > 1) {
            $¤arguments[] = $b;
        }
        if ($¤argumentCount > 2) {
            $¤arguments[] = $c;
        }
        if ($¤argumentCount > 3) {
            $¤arguments[] = $d;
        }

        for ($¤i = 4; $¤i < $¤argumentCount; ++$¤i) {
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
        int $int
    ) {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        if ($¤argumentCount > 0) {
            $¤arguments[] = $int;
        }

        for ($¤i = 1; $¤i < $¤argumentCount; ++$¤i) {
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

    private readonly InstanceHandle $_handle;
}
