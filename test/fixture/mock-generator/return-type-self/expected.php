<?php

class MockGeneratorSelfReturnType
implements \Eloquent\Phony\Mock\Mock,
           \Eloquent\Phony\Test\TestInterfaceWithSelfReturnType
{
    public static function staticMethod() : \Eloquent\Phony\Test\TestInterfaceWithSelfReturnType
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        if (!self::$_staticHandle) {
            $result = \call_user_func_array(
                array(__CLASS__, 'parent::' . 'staticMethod'),
                $arguments
            );

            return $result;
        }

        $result = self::$_staticHandle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($arguments)
        );

        return $result;
    }

    public static function __callStatic(
        $a0,
        array $a1
    ) : \Eloquent\Phony\Test\TestInterfaceWithSelfReturnType {
        $result = self::$_staticHandle->spy($a0)
            ->invokeWith(new \Eloquent\Phony\Call\Arguments($a1));

        return $result;
    }

    public function method() : \Eloquent\Phony\Test\TestInterfaceWithSelfReturnType
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        if (!$this->_handle) {
            $result = \call_user_func_array(
                array($this, 'parent::' . 'method'),
                $arguments
            );

            return $result;
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($arguments)
        );

        return $result;
    }

    public function __call(
        $a0,
        array $a1
    ) : \Eloquent\Phony\Test\TestInterfaceWithSelfReturnType {
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
            array($name, $arguments->all())
        );
    }

    private function _callMagic(
        $name,
        \Eloquent\Phony\Call\Arguments $arguments
    ) {
        return \call_user_func_array(
            array($this, 'parent::__call'),
            array($name, $arguments->all())
        );
    }

    private static $_uncallableMethods = array (
  'staticmethod' => true,
  '__callstatic' => true,
  'method' => true,
  '__call' => true,
);
    private static $_traitMethods = array();
    private static $_customMethods = array();
    private static $_staticHandle;
    private $_handle;
}
