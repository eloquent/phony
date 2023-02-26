<?php

class MockGeneratorReturnTypeMagicCall
implements \Eloquent\Phony\Mock\Mock
{
    public static function __callStatic(
        $a0,
        array $a1
    ) : \stdClass {
        $result = self::$_staticHandle->spy($a0)
            ->invokeWith(new \Eloquent\Phony\Call\Arguments($a1));

        return $result;
    }

    public function __call(
        $a0,
        array $a1
    ) : \stdClass {
        $result = $this->_handle->spy($a0)
            ->invokeWith(new \Eloquent\Phony\Call\Arguments($a1));

        return $result;
    }

    private static function _callMagicStatic(
        $name,
        \Eloquent\Phony\Call\Arguments $arguments
    ) {}

    private function _callMagic(
        $name,
        \Eloquent\Phony\Call\Arguments $arguments
    ) {}

    private static $_staticHandle;
    private readonly \Eloquent\Phony\Mock\Handle\InstanceHandle $_handle;
}
