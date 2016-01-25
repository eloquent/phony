<?php

namespace Phony\Test;

class MockGeneratorCustomMethodInvocableObject
implements \Eloquent\Phony\Mock\MockInterface
{
    public function methodA()
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

    private static $_uncallableMethods = array();
    private static $_traitMethods = array();
    private static $_customMethods = array();
    private static $_staticProxy;
    private $_proxy;
}
