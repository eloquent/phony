<?php

class MockGeneratorParameterConstant
implements \Eloquent\Phony\Mock\Mock
{
    public function methodA(
        $a0 = 1
    ) {
        $argumentCount = \func_num_args();
        $arguments = [];

        if ($argumentCount > 0) {
            $arguments[] = $a0;
        }

        for ($i = 1; $i < $argumentCount; ++$i) {
            $arguments[] = \func_get_arg($i);
        }

        if (isset($this->_handle)) {
            $result = $this->_handle->spy(__FUNCTION__)->invokeWith(
                new \Eloquent\Phony\Call\Arguments($arguments)
            );

            return $result;
        } else {
            $result = null;

            return $result;
        }
    }

    private static $_staticHandle;
    private readonly \Eloquent\Phony\Mock\Handle\InstanceHandle $_handle;
}
