<?php

namespace Phony\Test;

class MockGeneratorCustomMethodInvocableObject
implements \Eloquent\Phony\Mock\Mock
{
    public function methodA(
        ...$a0
    ) {
        $argumentCount = \func_num_args();
        $arguments = [];


        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = $a0[$i - 0];
        }

        if ($this->_handle) {
            $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
                new \Eloquent\Phony\Call\Arguments($arguments)
            );

            return $result;
        } else {
            $result = null;

            return $result;
        }
    }

    private static $_uncallableMethods = [];
    private static $_traitMethods = [];
    private static $_customMethods = [];
    private static $_staticHandle;
    private $_handle;
}
