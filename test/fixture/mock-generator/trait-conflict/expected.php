<?php

class MockGeneratorTraitConflict
implements \Eloquent\Phony\Mock\MockInterface
{
    use \Eloquent\Phony\Test\TestTraitA,
        \Eloquent\Phony\Test\TestTraitB,
        \Eloquent\Phony\Test\TestTraitC
    {
        \Eloquent\Phony\Test\TestTraitC::testClassAStaticMethodA
            insteadof \Eloquent\Phony\Test\TestTraitA;
        \Eloquent\Phony\Test\TestTraitC::testClassAStaticMethodA
            insteadof \Eloquent\Phony\Test\TestTraitB;
        \Eloquent\Phony\Test\TestTraitC::testClassAMethodB
            insteadof \Eloquent\Phony\Test\TestTraitA;
        \Eloquent\Phony\Test\TestTraitC::testClassAMethodB
            insteadof \Eloquent\Phony\Test\TestTraitB;
        \Eloquent\Phony\Test\TestTraitC::testClassAStaticMethodA
            as private _callTrait_testClassAStaticMethodA;
        \Eloquent\Phony\Test\TestTraitC::testClassAMethodB
            as private _callTrait_testClassAMethodB;
    }

    public static function testClassAStaticMethodA(
        &$a0 = null
    ) {
        $argumentCount = func_num_args();
        $arguments = array();

        if ($argumentCount > 0) $arguments[] = &$a0;

        for ($i = 1; $i < $argumentCount; $i++) {
            $arguments[] = func_get_arg($i);
        }

        return self::$_staticProxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function testClassAMethodB(
        $a0,
        $a1,
        &$a2 = null,
        &$a3 = null,
        &$a4 = null
    ) {
        $argumentCount = func_num_args();
        $arguments = array();

        if ($argumentCount > 0) $arguments[] = $a0;
        if ($argumentCount > 1) $arguments[] = $a1;
        if ($argumentCount > 2) $arguments[] = &$a2;
        if ($argumentCount > 3) $arguments[] = &$a3;
        if ($argumentCount > 4) $arguments[] = &$a4;

        for ($i = 5; $i < $argumentCount; $i++) {
            $arguments[] = func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    private static function _callParentStatic(
        $name,
        \Eloquent\Phony\Call\Argument\ArgumentsInterface $arguments
    ) {
        $callback = array(__CLASS__, 'parent::' . $name);

        if (!\is_callable($callback)) {
            $callback = array(__CLASS__, '_callTrait_' . $name);
        }

        return \call_user_func_array($callback, $arguments->all());
    }

    private function _callParent(
        $name,
        \Eloquent\Phony\Call\Argument\ArgumentsInterface $arguments
    ) {
        $callback = array($this, 'parent::' . $name);

        if (!\is_callable($callback)) {
            $callback = array($this, '_callTrait_' . $name);
        }

        return \call_user_func_array($callback, $arguments->all());
    }

    private static $_customMethods = array();
    private static $_staticProxy;
    private $_proxy;
}
