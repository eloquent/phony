<?php

namespace Phony\Test;

class MockGeneratorVariadics
implements \Eloquent\Phony\Mock\Mock
{
    public function methodA(
        $a,
        $b,
        ...$c
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $a;
        }
        if ($¢argumentCount > 1) {
            $¢arguments[] = $b;
        }

        for ($i = 2; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = $c[$i - 2];
        }

        if (!$this->_handle) {
            $result = null;

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function methodB(
        $a,
        $b,
        \stdClass ...$c
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $a;
        }
        if ($¢argumentCount > 1) {
            $¢arguments[] = $b;
        }

        for ($i = 2; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = $c[$i - 2];
        }

        if (!$this->_handle) {
            $result = null;

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function methodC(
        $a,
        $b,
        &...$c
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $a;
        }
        if ($¢argumentCount > 1) {
            $¢arguments[] = $b;
        }

        for ($i = 2; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = &$c[$i - 2];
        }

        if (!$this->_handle) {
            $result = null;

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    public function methodD(
        $a,
        $b,
        ?\stdClass ...$c
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $a;
        }
        if ($¢argumentCount > 1) {
            $¢arguments[] = $b;
        }

        for ($i = 2; $i < $¢argumentCount; ++$i) {
            $¢arguments[] = $c[$i - 2];
        }

        if (!$this->_handle) {
            $result = null;

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $result;
    }

    private static $_uncallableMethods = [];
    private static $_traitMethods = [];
    private static $_customMethods = [];
    private static $_staticHandle;
    private $_handle;
}
