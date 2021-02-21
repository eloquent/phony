<?php

class MockGeneratorTraitMagicCall
implements \Eloquent\Phony\Mock\Mock
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
        $¢result = self::$_staticHandle
            ->spy($name)
            ->invokeWith(
                new \Eloquent\Phony\Call\Arguments($arguments)
            );

        return $¢result;
    }

    public function __call(
        $name,
        array $arguments
    ) {
        $¢result = $this->_handle
            ->spy($name)
            ->invokeWith(
                new \Eloquent\Phony\Call\Arguments($arguments)
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

    private static function _callMagicStatic(
        $name,
        \Eloquent\Phony\Call\Arguments $arguments
    ) {
        $methodName = '_callTrait_Eloquent¦Phony¦Test¦TestTraitJ»__callStatic';

        return self::$methodName($name, $arguments->all());
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

    private function _callMagic(
        $name,
        \Eloquent\Phony\Call\Arguments $arguments
    ) {
        $methodName = '_callTrait_Eloquent¦Phony¦Test¦TestTraitJ»__call';

        return $this->$methodName($name, $arguments->all());
    }

    private static $_uncallableMethods = [];
    private static $_traitMethods = array (
  '__callstatic' => 'Eloquent\\Phony\\Test\\TestTraitJ',
  '__call' => 'Eloquent\\Phony\\Test\\TestTraitJ',
);
    private static $_customMethods = [];
    private static $_staticHandle;
    private $_handle;
}
