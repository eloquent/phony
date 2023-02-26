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
        $a0,
        &$a1,
        &$a2
    ) {
        $argumentCount = \func_num_args();
        $arguments = [];

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }
        if ($argumentCount > 1) {
            $arguments[] = &$a1;
        }
        if ($argumentCount > 2) {
            $arguments[] = &$a2;
        }

        for ($i = 3; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        if (isset(StaticHandleRegistry::$handles['mockgeneratorreturnbyreference'])) {
            $result = StaticHandleRegistry::$handles['mockgeneratorreturnbyreference']->spy(__FUNCTION__)->invokeWith(
                new Arguments($arguments)
            );

            return $result;
        } else {
            $result = parent::testClassGStaticMethodA(...$arguments);

            return $result;
        }
    }

    public static function &__callStatic(
        $a0,
        array $a1
    ) {
        $result = StaticHandleRegistry::$handles['mockgeneratorreturnbyreference']->spy($a0)
            ->invokeWith(new Arguments($a1));

        return $result;
    }

    public function &testClassGMethodA(
        $a0,
        &$a1,
        &$a2
    ) {
        $argumentCount = \func_num_args();
        $arguments = [];

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }
        if ($argumentCount > 1) {
            $arguments[] = &$a1;
        }
        if ($argumentCount > 2) {
            $arguments[] = &$a2;
        }

        for ($i = 3; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        if (isset($this->_handle)) {
            $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
                new Arguments($arguments)
            );

            return $result;
        } else {
            $result = parent::testClassGMethodA(...$arguments);

            return $result;
        }
    }

    public function &__call(
        $a0,
        array $a1
    ) {
        $result = $this->_handle->spy($a0)
            ->invokeWith(new Arguments($a1));

        return $result;
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
