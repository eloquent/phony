<?php

class MockGeneratorTraitMagicCall
implements \Eloquent\Phony\Mock\MockInterface
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
        $result = self::$_staticProxy->spy($a0)
            ->invokeWith(new \Eloquent\Phony\Call\Argument\Arguments($a1));

        return $result;
    }

    public function __call(
        $a0,
        array $a1
    ) {
        $result = $this->_proxy->spy($a0)
            ->invokeWith(new \Eloquent\Phony\Call\Argument\Arguments($a1));

        return $result;
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
                    $name,
            ),
            $arguments->all()
        );
    }

    private static function _callMagicStatic(
        $name,
        \Eloquent\Phony\Call\Argument\ArgumentsInterface $arguments
    ) {
        return self::$_staticProxy
            ->spy('__callStatic')->invoke($name, $arguments->all());
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
                    $name,
            ),
            $arguments->all()
        );
    }

    private function _callMagic(
        $name,
        \Eloquent\Phony\Call\Argument\ArgumentsInterface $arguments
    ) {
        return \call_user_func_array(
            array($this, '_callTrait_Eloquent¦Phony¦Test¦TestTraitJ»__call'),
            array($name, $arguments->all())
        );
    }

    private static $_uncallableMethods = array();
    private static $_traitMethods = array(
  '__callstatic' => 'Eloquent\\Phony\\Test\\TestTraitJ',
  '__call' => 'Eloquent\\Phony\\Test\\TestTraitJ',
);
    private static $_customMethods = array();
    private static $_staticProxy;
    private $_proxy;
}
