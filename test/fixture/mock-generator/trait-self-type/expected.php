<?php

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Mock\Handle\StaticHandleRegistry;
use Eloquent\Phony\Mock\Mock;

class MockGeneratorTraitSelfType
implements Mock
{
    use \Eloquent\Phony\Test\TestTraitWithSelfType
    {
    }

    public static function staticMethod(
        \MockGeneratorTraitSelfType $a
    ) : \MockGeneratorTraitSelfType {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        if ($¤argumentCount > 0) {
            $¤arguments[] = $a;
        }

        for ($¤i = 1; $¤i < $¤argumentCount; ++$¤i) {
            $¤arguments[] = \func_get_arg($¤i);
        }

        if (isset(StaticHandleRegistry::$handles['mockgeneratortraitselftype'])) {
            $¤result = StaticHandleRegistry::$handles['mockgeneratortraitselftype']->spy(__FUNCTION__)->invokeWith(
                new Arguments($¤arguments)
            );

            return $¤result;
        } else {
            $¤result = null;

            return $¤result;
        }
    }

    public function method(
        \MockGeneratorTraitSelfType $a
    ) : \MockGeneratorTraitSelfType {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        if ($¤argumentCount > 0) {
            $¤arguments[] = $a;
        }

        for ($¤i = 1; $¤i < $¤argumentCount; ++$¤i) {
            $¤arguments[] = \func_get_arg($¤i);
        }

        if (isset($this->_handle)) {
            $¤result = $this->_handle->spy(__FUNCTION__)->invokeWith(
                new Arguments($¤arguments)
            );

            return $¤result;
        } else {
            $¤result = null;

            return $¤result;
        }
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
