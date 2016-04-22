<?php

class MockGeneratorTraitConstructorConflict
implements \Eloquent\Phony\Mock\Mock
{
    use \Eloquent\Phony\Test\TestTraitD,
        \Eloquent\Phony\Test\TestTraitE
    {
        \Eloquent\Phony\Test\TestTraitD::__construct
            as private _callTrait_Eloquent¦Phony¦Test¦TestTraitD»__construct;
        \Eloquent\Phony\Test\TestTraitE::__construct
            as private _callTrait_Eloquent¦Phony¦Test¦TestTraitE»__construct;
    }

    public function __construct()
    {
    }

    private static function _callTraitStatic(
        $traitName,
        $name,
        \Eloquent\Phony\Call\Argument\Arguments $arguments
    ) {
        return \call_user_func_array(
            array(
                __CLASS__,
                '_callTrait_' .
                    \str_replace('\\', "\xc2\xa6", $traitName) .
                    "\xc2\xbb" .
                    $name,
            ),
            $arguments->all()
        );
    }

    private function _callParentConstructor(
        \Eloquent\Phony\Call\Argument\Arguments $arguments
    ) {
        \call_user_func_array(
            array(
                $this,
                '_callTrait_Eloquent¦Phony¦Test¦TestTraitE»__construct',
            ),
            $arguments->all()
        );
    }

    private function _callTrait(
        $traitName,
        $name,
        \Eloquent\Phony\Call\Argument\Arguments $arguments
    ) {
        return \call_user_func_array(
            array(
                $this,
                '_callTrait_' .
                    \str_replace('\\', "\xc2\xa6", $traitName) .
                    "\xc2\xbb" .
                    $name,
            ),
            $arguments->all()
        );
    }

    private static $_uncallableMethods = array();
    private static $_traitMethods = array(
  '__construct' => 'Eloquent\\Phony\\Test\\TestTraitD',
);
    private static $_customMethods = array();
    private static $_staticHandle;
    private $_handle;
}
