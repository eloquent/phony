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
        \Eloquent\Phony\Call\Arguments $arguments
    ) {
        $name = '_callTrait_' .
            \str_replace('\\', "\u{a6}", $traitName) .
            "\u{bb}" .
            $name;

        return self::$name(...$arguments->all());
    }

    private function _callParentConstructor(
        \Eloquent\Phony\Call\Arguments $arguments
    ) {
        $this->_callTrait_Eloquent¦Phony¦Test¦TestTraitE»__construct(...$arguments->all());
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
