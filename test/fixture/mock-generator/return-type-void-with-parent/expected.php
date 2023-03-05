<?php

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Mock\Handle\StaticHandleRegistry;
use Eloquent\Phony\Mock\Mock;

class MockGeneratorVoidReturnTypeWithParent
extends \Eloquent\Phony\Test\TestClassWithVoidReturnType
implements Mock
{
    public static function staticMethod() : void
    {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        for ($¤i = 0; $¤i < $¤argumentCount; ++$¤i) {
            $¤arguments[] = \func_get_arg($¤i);
        }

        if (isset(StaticHandleRegistry::$handles['mockgeneratorvoidreturntypewithparent'])) {
            StaticHandleRegistry::$handles['mockgeneratorvoidreturntypewithparent']->spy(__FUNCTION__)->invokeWith(
                new Arguments($¤arguments)
            );
        } else {
            parent::staticMethod(...$¤arguments);
        }
    }

    public static function __callStatic(
        $name,
        array $arguments
    ) : void {
        StaticHandleRegistry::$handles['mockgeneratorvoidreturntypewithparent']->spy($name)
            ->invokeWith(new Arguments($arguments));
    }

    public function method() : void
    {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        for ($¤i = 0; $¤i < $¤argumentCount; ++$¤i) {
            $¤arguments[] = \func_get_arg($¤i);
        }

        if (isset($this->_handle)) {
            $this->_handle->spy(__FUNCTION__)->invokeWith(
                new Arguments($¤arguments)
            );
        } else {
            parent::method(...$¤arguments);
        }
    }

    public function __call(
        $name,
        array $arguments
    ) : void {
        $this->_handle->spy($name)
            ->invokeWith(new Arguments($arguments));
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
