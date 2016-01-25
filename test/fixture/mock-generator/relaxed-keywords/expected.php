<?php

class MockGeneratorRelaxedKeywords
implements \Eloquent\Phony\Mock\MockInterface,
           \Eloquent\Phony\Test\TestInterfaceWithKeywordMethods
{
    public function return()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    public function throw()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_proxy->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    private static $_uncallableMethods = array(
  'return' => true,
);
    private static $_traitMethods = array();
    private static $_customMethods = array();
    private static $_staticProxy;
    private $_proxy;
}
