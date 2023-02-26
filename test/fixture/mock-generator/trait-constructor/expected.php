<?php

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Mock\Handle\StaticHandleRegistry;
use Eloquent\Phony\Mock\Mock;

class MockGeneratorTraitConstructor
implements Mock
{
    use \Eloquent\Phony\Test\TestTraitD
    {
        \Eloquent\Phony\Test\TestTraitD::__construct
            as private _callTrait_Eloquent¦Phony¦Test¦TestTraitD»__construct;
    }

    public function __construct()
    {
    }

    private static function _callTraitStatic(
        $traitName,
        $name,
        Arguments $arguments
    ) {
        $name = '_callTrait_' .
            \str_replace('\\', "\u{a6}", $traitName) .
            "\u{bb}" .
            $name;

        return self::$name(...$arguments->all());
    }

    private function _callParentConstructor(
        Arguments $arguments
    ) {
        $this->_callTrait_Eloquent¦Phony¦Test¦TestTraitD»__construct(...$arguments->all());
    }

    private function _callTrait(
        $traitName,
        $name,
        Arguments $arguments
    ) {
        $name = '_callTrait_' .
            \str_replace('\\', "\u{a6}", $traitName) .
            "\u{bb}" .
            $name;

        return $this->$name(...$arguments->all());
    }

    private readonly InstanceHandle $_handle;
}
