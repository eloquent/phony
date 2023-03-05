<?php

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Mock\Handle\StaticHandleRegistry;
use Eloquent\Phony\Mock\Mock;

class MockGeneratorReturnByReference
extends \Eloquent\Phony\Test\TestClassG
implements Mock
{
    public static function &testClassGStaticMethodA(
        $a,
        &$b,
        &$c
    ) {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        if ($¤argumentCount > 0) {
            $¤arguments[] = $a;
        }
        if ($¤argumentCount > 1) {
            $¤arguments[] = &$b;
        }
        if ($¤argumentCount > 2) {
            $¤arguments[] = &$c;
        }

        for ($¤i = 3; $¤i < $¤argumentCount; ++$¤i) {
            $¤arguments[] = \func_get_arg($¤i);
        }

        if (isset(StaticHandleRegistry::$handles['mockgeneratorreturnbyreference'])) {
            $¤result = StaticHandleRegistry::$handles['mockgeneratorreturnbyreference']->spy(__FUNCTION__)->invokeWith(
                new Arguments($¤arguments)
            );

            return $¤result;
        } else {
            $¤result = parent::testClassGStaticMethodA(...$¤arguments);

            return $¤result;
        }
    }

    public static function &__callStatic(
        $name,
        array $arguments
    ) {
        $¤result = StaticHandleRegistry::$handles['mockgeneratorreturnbyreference']->spy($name)
            ->invokeWith(new Arguments($arguments));

        return $¤result;
    }

    public function &testClassGMethodA(
        $a,
        &$b,
        &$c
    ) {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        if ($¤argumentCount > 0) {
            $¤arguments[] = $a;
        }
        if ($¤argumentCount > 1) {
            $¤arguments[] = &$b;
        }
        if ($¤argumentCount > 2) {
            $¤arguments[] = &$c;
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
            $¤result = parent::testClassGMethodA(...$¤arguments);

            return $¤result;
        }
    }

    public function &__call(
        $name,
        array $arguments
    ) {
        $¤result = $this->_handle->spy($name)
            ->invokeWith(new Arguments($arguments));

        return $¤result;
    }

    private static function _callParentStatic(
        $name,
        Arguments $arguments
    ) {
        return parent::$name(...$arguments->all());
    }

    private static function _callMagicStatic(
        $name,
        Arguments $arguments
    ) {
        return parent::__callStatic($name, $arguments->all());
    }

    private function _callParent(
        $name,
        Arguments $arguments
    ) {
        return parent::$name(...$arguments->all());
    }

    private function _callMagic(
        $name,
        Arguments $arguments
    ) {
        return parent::__call($name, $arguments->all());
    }

    private readonly InstanceHandle $_handle;
}
