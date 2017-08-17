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
    ) {
        return \call_user_func_array(
            'parent::__callStatic',
            [$name, $arguments->all()]
        );
    }

    private function _callMagic(
        $name,
        \Eloquent\Phony\Call\Arguments $arguments
    ) {
        return \call_user_func_array(
            [$this, 'parent::__call'],
            [$name, $arguments->all()]
        );
    }

    private static $_uncallableMethods = [];
    private static $_traitMethods = [];
    private static $_customMethods = [];
    private static $_staticHandle;
    private $_handle;
}
