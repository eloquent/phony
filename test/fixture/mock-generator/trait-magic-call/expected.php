<?php

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Mock\Handle\StaticHandleRegistry;
use Eloquent\Phony\Mock\Mock;

class MockGeneratorTraitMagicCall
implements Mock
{
    use \Eloquent\Phony\Test\TestTraitJ
    {
        \Eloquent\Phony\Test\TestTraitJ::__callStatic
            as private _callTrait_Eloquent¦Phony¦Test¦TestTraitJ»__callStatic;
        \Eloquent\Phony\Test\TestTraitJ::__call
            as private _callTrait_Eloquent¦Phony¦Test¦TestTraitJ»__call;
    }

    public static function __callStatic(
        $name,
        array $arguments
    ) {
        $¤result = StaticHandleRegistry::$handles['mockgeneratortraitmagiccall']->spy($name)
            ->invokeWith(new Arguments($arguments));

        return $¤result;
    }

    public function __call(
        $name,
        array $arguments
    ) {
        $¤result = $this->_handle->spy($name)
            ->invokeWith(new Arguments($arguments));

        return $¤result;
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

    private static function _callMagicStatic(
        $name,
        Arguments $arguments
    ) {
        $methodName = '_callTrait_Eloquent¦Phony¦Test¦TestTraitJ»__callStatic';

        return self::$methodName($name, $arguments->all());
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

    private function _callMagic(
        $name,
        Arguments $arguments
    ) {
        $methodName = '_callTrait_Eloquent¦Phony¦Test¦TestTraitJ»__call';

        return $this->$methodName($name, $arguments->all());
    }

    private readonly InstanceHandle $_handle;
}
