<?php

class MockGeneratorVoidReturnTypeWithParent
extends \Eloquent\Phony\Test\TestClassWithVoidReturnType
implements \Eloquent\Phony\Mock\Mock
{
    public static function staticMethod() : void
    {
        $argumentCount = \func_num_args();
        $arguments = [];

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        if (isset(\Eloquent\Phony\Mock\Handle\StaticHandleRegistry::$handles['mockgeneratorvoidreturntypewithparent'])) {
            \Eloquent\Phony\Mock\Handle\StaticHandleRegistry::$handles['mockgeneratorvoidreturntypewithparent']->spy(__FUNCTION__)->invokeWith(
                new \Eloquent\Phony\Call\Arguments($arguments)
            );
        } else {
            parent::staticMethod(...$arguments);
        }
    }

    public static function __callStatic(
        $a0,
        array $a1
    ) : void {
        \Eloquent\Phony\Mock\Handle\StaticHandleRegistry::$handles['mockgeneratorvoidreturntypewithparent']->spy($a0)
            ->invokeWith(new \Eloquent\Phony\Call\Arguments($a1));
    }

    public function method() : void
    {
        $argumentCount = \func_num_args();
        $arguments = [];

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        if (isset($this->_handle)) {
            $this->_handle->spy(__FUNCTION__)->invokeWith(
                new \Eloquent\Phony\Call\Arguments($arguments)
            );
        } else {
            parent::method(...$arguments);
        }
    }

    public function __call(
        $a0,
        array $a1
    ) : void {
        $this->_handle->spy($a0)
            ->invokeWith(new \Eloquent\Phony\Call\Arguments($a1));
    }

    private static function _callParentStatic(
        $name,
        \Eloquent\Phony\Call\Arguments $arguments
    ) {
        return parent::$name(...$arguments->all());
    }

    private static function _callMagicStatic(
        $name,
        \Eloquent\Phony\Call\Arguments $arguments
    ) {
        return parent::__callStatic($name, $arguments->all());
    }

    private function _callParent(
        $name,
        \Eloquent\Phony\Call\Arguments $arguments
    ) {
        return parent::$name(...$arguments->all());
    }

    private function _callMagic(
        $name,
        \Eloquent\Phony\Call\Arguments $arguments
    ) {
        return parent::__call($name, $arguments->all());
    }

    private readonly \Eloquent\Phony\Mock\Handle\InstanceHandle $_handle;
}
