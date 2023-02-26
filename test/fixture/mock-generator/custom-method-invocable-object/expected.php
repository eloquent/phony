<?php

namespace Phony\Test;

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Mock\Handle\StaticHandleRegistry;
use Eloquent\Phony\Mock\Mock;

class MockGeneratorCustomMethodInvocableObject
implements Mock
{
    public function methodA(
        ...$a0
    ) {
        $argumentCount = \func_num_args();
        $arguments = [];


        for ($i = 0; $i < $argumentCount; ++$i) {
            $arguments[] = $a0[$i - 0];
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
