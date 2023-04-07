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
        ...$arguments
    ) {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        foreach ($arguments as $¤argumentName => $¤argumentValue) {
            $¤arguments[$¤argumentName] = $¤argumentValue;
        }

        if (isset($this->_handle)) {
            $¤result = $this->_handle->spy(__FUNCTION__)->invokeWith(
                new Arguments($¤arguments)
            );

            return $¤result;
        } else {
            $¤result = null;

            return $¤result;
        }
    }

    private readonly InstanceHandle $_handle;
}
