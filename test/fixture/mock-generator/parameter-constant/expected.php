<?php

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Mock\Handle\StaticHandleRegistry;
use Eloquent\Phony\Mock\Mock;

class MockGeneratorParameterConstant
implements Mock
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
                new Arguments($arguments)
            );

            return $result;
        } else {
            $result = null;

            return $result;
        }
    }

    private readonly InstanceHandle $_handle;
}
