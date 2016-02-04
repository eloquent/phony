<?php

class MockGeneratorParameterConstant
implements \Eloquent\Phony\Mock\MockInterface
{
    public function methodA(
        $a0 = 256
    ) {
        $argumentCount = \func_num_args();
        $arguments = array();

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }

        for ($i = 1; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
            new \Eloquent\Phony\Call\Argument\Arguments($arguments)
        );

        return $result;
    }

    private static $_uncallableMethods = array();
    private static $_traitMethods = array();
    private static $_customMethods = array();
    private static $_staticHandle;
    private $_handle;
}
