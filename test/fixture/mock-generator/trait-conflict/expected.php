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
        &$first = null
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = &$first;
        }

        for ($¢i = 1; $¢i < $¢argumentCount; ++$¢i) {
            $¢arguments[] = \func_get_arg($¢i);
        }

        if (!self::$_staticHandle) {
            $¢result = null;

            return $¢result;
        }

        $¢result = self::$_staticHandle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $¢result;
    }

    public function testClassAMethodB(
        $first,
        $second,
        &$third = null,
        &$fourth = null
    ) {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        if ($¢argumentCount > 0) {
            $¢arguments[] = $first;
        }
        if ($¢argumentCount > 1) {
            $¢arguments[] = $second;
        }
        if ($¢argumentCount > 2) {
            $¢arguments[] = &$third;
        }
        if ($¢argumentCount > 3) {
            $¢arguments[] = &$fourth;
        }

        for ($¢i = 4; $¢i < $¢argumentCount; ++$¢i) {
            $¢arguments[] = \func_get_arg($¢i);
        }

        if (!$this->_handle) {
            $¢result = null;

            return $¢result;
        }

        $¢result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $¢result;
    }

    public function testTraitBMethodA()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($¢i = 0; $¢i < $¢argumentCount; ++$¢i) {
            $¢arguments[] = \func_get_arg($¢i);
        }

        if (!$this->_handle) {
            $¢result = null;

            return $¢result;
        }

        $¢result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $¢result;
    }

    public function testTraitCMethodA()
    {
        $¢argumentCount = \func_num_args();
        $¢arguments = [];

        for ($¢i = 0; $¢i < $¢argumentCount; ++$¢i) {
            $¢arguments[] = \func_get_arg($¢i);
        }

        if (!$this->_handle) {
            $¢result = null;

            return $¢result;
        }

        $¢result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($¢arguments)
        );

        return $¢result;
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

    private static $_uncallableMethods = array (
  'testtraitcmethoda' => true,
);
    private static $_traitMethods = array (
  'testclassastaticmethoda' => 'Eloquent\\Phony\\Test\\TestTraitA',
  'testclassamethodb' => 'Eloquent\\Phony\\Test\\TestTraitA',
  'testtraitbmethoda' => 'Eloquent\\Phony\\Test\\TestTraitB',
);
    private static $_customMethods = [];
    private static $_staticHandle;
    private $_handle;
}
