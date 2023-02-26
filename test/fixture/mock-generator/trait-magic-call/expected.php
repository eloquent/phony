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
        $a0,
        array $a1
    ) {
        $result = StaticHandleRegistry::$handles['mockgeneratortraitmagiccall']->spy($a0)
            ->invokeWith(new Arguments($a1));

        return $result;
    }

    public function __call(
        $a0,
        array $a1
    ) {
        $result = $this->_handle->spy($a0)
            ->invokeWith(new Arguments($a1));

        return $result;
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
