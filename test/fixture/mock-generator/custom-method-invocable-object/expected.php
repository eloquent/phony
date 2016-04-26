<?php

namespace Phony\Test;

class MockGeneratorCustomMethodInvocableObject
implements \Eloquent\Phony\Mock\Mock
{
    public function methodA()
    {
        $argumentCount = \func_num_args();
        $arguments = array();

        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Arguments($arguments)
        );

        return $result;
    }

    private static $_uncallableMethods = array();
    private static $_traitMethods = array();
    private static $_customMethods = array();
    private static $_staticHandle;
    private $_handle;
}
