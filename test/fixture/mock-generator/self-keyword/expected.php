<?php

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Mock\Handle\StaticHandleRegistry;
use Eloquent\Phony\Mock\Mock;

class MockGeneratorSelfKeyword
extends \Eloquent\Phony\Test\TestClassC
implements Mock
{
    public function methodA(
        \Eloquent\Phony\Test\TestClassC $first,
        $second = 'a'
    ) {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        if ($¤argumentCount > 0) {
            $¤arguments[] = $first;
        }
        if ($¤argumentCount > 1) {
            $¤arguments[] = $second;
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
            $¤result = parent::methodA(...$¤arguments);

            return $¤result;
        }
    }

    public function methodB(
        $first,
        $second = 111,
        $third = 'second'
    ) {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        if ($¤argumentCount > 0) {
            $¤arguments[] = $first;
        }
        if ($¤argumentCount > 1) {
            $¤arguments[] = $second;
        }
        if ($¤argumentCount > 2) {
            $¤arguments[] = $third;
        }

        for ($¤i = 3; $¤i < $¤argumentCount; ++$¤i) {
            $¤arguments[] = \func_get_arg($¤i);
        }

        if (isset($this->_handle)) {
            $¤result = $this->_handle->spy(__FUNCTION__)->invokeWith(
                new Arguments($¤arguments)
            );

            return $¤result;
        } else {
            $¤result = parent::methodB(...$¤arguments);

            return $¤result;
        }
    }

    private static function _callParentStatic(
        $name,
        Arguments $arguments
    ) {
        return parent::$name(...$arguments->all());
    }

    private function _callParent(
        $name,
        Arguments $arguments
    ) {
        return parent::$name(...$arguments->all());
    }

    private readonly InstanceHandle $_handle;
}
