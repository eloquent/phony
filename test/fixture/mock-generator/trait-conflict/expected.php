<?php

class MockGeneratorTraitConflict
implements \Eloquent\Phony\Mock\MockInterface
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
        \Eloquent\Phony\Test\TestTraitB::testClassAStaticMethodA
            as private _callTrait_Eloquent¦Phony¦Test¦TestTraitB»testClassAStaticMethodA;
        \Eloquent\Phony\Test\TestTraitC::testClassAStaticMethodA
            as private _callTrait_Eloquent¦Phony¦Test¦TestTraitC»testClassAStaticMethodA;
        \Eloquent\Phony\Test\TestTraitC::testClassAMethodB
            as private _callTrait_Eloquent¦Phony¦Test¦TestTraitC»testClassAMethodB;
    }

    public static function testClassAStaticMethodA()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return self::$_staticProxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function testClassAMethodB()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function testTraitCMethodA()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; $i++) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    private static function _callTraitStatic(
        $traitName,
        $name,
        \Eloquent\Phony\Call\Argument\ArgumentsInterface $arguments
    ) {
        return \call_user_func_array(
            array(
                __CLASS__,
                '_callTrait_' .
                    \str_replace('\\', "\xc2\xa6", $traitName) .
                    "\xc2\xbb" .
                    $name
            ),
            $arguments->all()
        );
    }

    private function _callTrait(
        $traitName,
        $name,
        \Eloquent\Phony\Call\Argument\ArgumentsInterface $arguments
    ) {
        return \call_user_func_array(
            array(
                $this,
                '_callTrait_' .
                    \str_replace('\\', "\xc2\xa6", $traitName) .
                    "\xc2\xbb" .
                    $name
            ),
            $arguments->all()
        );
    }

    private static $_uncallableMethods = array (
  'testTraitCMethodA' => true,
);
    private static $_traitMethods = array (
  'testClassAStaticMethodA' => 'Eloquent\\Phony\\Test\\TestTraitC',
  'testClassAMethodB' => 'Eloquent\\Phony\\Test\\TestTraitC',
);
    private static $_customMethods = array();
    private static $_staticProxy;
    private $_proxy;
}
