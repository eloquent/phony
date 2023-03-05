<?php

namespace Phony\Test;

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Mock\Handle\StaticHandleRegistry;
use Eloquent\Phony\Mock\Mock;

class MockGeneratorVariadics
implements Mock
{
    public function methodA(
        $a,
        $b,
        ...$c
    ) {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        if ($¤argumentCount > 0) {
            $¤arguments[] = $a;
        }
        if ($¤argumentCount > 1) {
            $¤arguments[] = $b;
        }

        for ($¤i = 2; $¤i < $¤argumentCount; ++$¤i) {
            $¤arguments[] = $c[$¤i - 2];
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

    public function methodB(
        $a,
        $b,
        \stdClass ...$c
    ) {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        if ($¤argumentCount > 0) {
            $¤arguments[] = $a;
        }
        if ($¤argumentCount > 1) {
            $¤arguments[] = $b;
        }

        for ($¤i = 2; $¤i < $¤argumentCount; ++$¤i) {
            $¤arguments[] = $c[$¤i - 2];
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

    public function methodC(
        $a,
        $b,
        &...$c
    ) {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        if ($¤argumentCount > 0) {
            $¤arguments[] = $a;
        }
        if ($¤argumentCount > 1) {
            $¤arguments[] = $b;
        }

        for ($¤i = 2; $¤i < $¤argumentCount; ++$¤i) {
            $¤arguments[] = &$c[$¤i - 2];
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

    public function methodD(
        $a,
        $b,
        ?\stdClass ...$c
    ) {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        if ($¤argumentCount > 0) {
            $¤arguments[] = $a;
        }
        if ($¤argumentCount > 1) {
            $¤arguments[] = $b;
        }

        for ($¤i = 2; $¤i < $¤argumentCount; ++$¤i) {
            $¤arguments[] = $c[$¤i - 2];
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
