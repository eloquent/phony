<?php

class MockGeneratorTraitConflict
implements \Eloquent\Phony\Mock\Mock
{
    use \Eloquent\Phony\Test\TestTraitA,
        \Eloquent\Phony\Test\TestTraitB,
        \Eloquent\Phony\Test\TestTraitC
    {
        \Eloquent\Phony\Test\TestTraitA::testClassAStaticMethodA
            as private _callTrait_Eloquent¦Phony¦Test¦TestTraitA»testClassAStaticMethodA;
        \Eloquent\Phony\Test\TestTraitA::testClassAMethodB
            as private _callTrait_Eloquent¦Phony¦Test¦TestTraitA»testClassAMethodB;
        \Eloquent\Phony\Test\TestTraitB::testClassAMethodB
            as private _callTrait_Eloquent¦Phony¦Test¦TestTraitB»testClassAMethodB;
        \Eloquent\Phony\Test\TestTraitB::testTraitBMethodA
            as private _callTrait_Eloquent¦Phony¦Test¦TestTraitB»testTraitBMethodA;
        \Eloquent\Phony\Test\TestTraitB::testClassAStaticMethodA
            as private _callTrait_Eloquent¦Phony¦Test¦TestTraitB»testClassAStaticMethodA;
        \Eloquent\Phony\Test\TestTraitC::testClassAStaticMethodA
            as private _callTrait_Eloquent¦Phony¦Test¦TestTraitC»testClassAStaticMethodA;
        \Eloquent\Phony\Test\TestTraitC::testClassAMethodB
            as private _callTrait_Eloquent¦Phony¦Test¦TestTraitC»testClassAMethodB;
    }

    public static function testClassAStaticMethodA(
        &$a0 = null
    ) {
        $argumentCount = \func_num_args();
        $arguments = [];

        if ($argumentCount > 0) {
            $arguments[] = &$a0;
        }

        for ($i = 1; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        if (isset(\Eloquent\Phony\Mock\Handle\StaticHandleRegistry::$handles['mockgeneratortraitconflict'])) {
            $result = \Eloquent\Phony\Mock\Handle\StaticHandleRegistry::$handles['mockgeneratortraitconflict']->spy(__FUNCTION__)->invokeWith(
                new \Eloquent\Phony\Call\Arguments($arguments)
            );

            return $result;
        } else {
            $result = null;

            return $result;
        }
    }

    public function testClassAMethodB(
        $a0,
        $a1,
        &$a2 = null,
        &$a3 = null
    ) {
        $argumentCount = \func_num_args();
        $arguments = [];

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }
        if ($argumentCount > 1) {
            $arguments[] = $a1;
        }
        if ($argumentCount > 2) {
            $arguments[] = &$a2;
        }
        if ($argumentCount > 3) {
            $arguments[] = &$a3;
        }

        for ($i = 4; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        if (isset($this->_handle)) {
            $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
                new \Eloquent\Phony\Call\Arguments($arguments)
            );

            return $result;
        } else {
            $result = null;

            return $result;
        }
    }

    public function testTraitBMethodA()
    {
        $argumentCount = \func_num_args();
        $arguments = [];

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        if (isset($this->_handle)) {
            $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
                new \Eloquent\Phony\Call\Arguments($arguments)
            );

            return $result;
        } else {
            $result = null;

            return $result;
        }
    }

    public function testTraitCMethodA()
    {
        $argumentCount = \func_num_args();
        $arguments = [];

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        if (isset($this->_handle)) {
            $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
                new \Eloquent\Phony\Call\Arguments($arguments)
            );

            return $result;
        } else {
            $result = null;

            return $result;
        }
    }

    private static function _callTraitStatic(
        $traitName,
        $name,
        \Eloquent\Phony\Call\Arguments $arguments
    ) {
        $name = '_callTrait_' .
            \str_replace('\\', "\u{a6}", $traitName) .
            "\u{bb}" .
            $name;

        return self::$name(...$arguments->all());
    }

    private function _callTrait(
        $traitName,
        $name,
        \Eloquent\Phony\Call\Arguments $arguments
    ) {
        $name = '_callTrait_' .
            \str_replace('\\', "\u{a6}", $traitName) .
            "\u{bb}" .
            $name;

        return $this->$name(...$arguments->all());
    }

    private readonly \Eloquent\Phony\Mock\Handle\InstanceHandle $_handle;
}
