<?php

class MockGeneratorReturnType
implements \Eloquent\Phony\Mock\MockInterface,
           \Eloquent\Phony\Test\TestInterfaceWithReturnType
{
    public static function __callStatic(
        $a0,
        array $a1
    ) : string {
        return self::$_staticProxy->spy($a0)
            ->invokeWith(new \Eloquent\Phony\Call\Argument\Arguments($a1));
    }

    public function classType() : \stdClass
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function scalarType() : int
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function customMethodWithClassType() : \stdClass
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function customMethodWithScalarType() : int
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        return $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );
    }

    public function __call(
        $a0,
        array $a1
    ) : string {
        return $this->_proxy->spy($a0)
            ->invokeWith(new \Eloquent\Phony\Call\Argument\Arguments($a1));
    }

    private static function _callMagicStatic(
        $name,
        \Eloquent\Phony\Call\Argument\ArgumentsInterface $arguments
    ) {
        return self::$_staticProxy
            ->spy('__callStatic')->invoke($name, $arguments->all());
    }

    private function _callMagic(
        $name,
        \Eloquent\Phony\Call\Argument\ArgumentsInterface $arguments
    ) {
        return \call_user_func_array(
            array($this, 'parent::__call'),
            array($name, $arguments->all())
        );
    }

    private static $_uncallableMethods = array(
  'classtype' => true,
  'scalartype' => true,
  '__call' => true,
  '__callstatic' => true,
);
    private static $_traitMethods = array();
    private static $_customMethods = array();
    private static $_staticProxy;
    private $_proxy;
}
