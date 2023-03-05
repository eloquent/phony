<?php

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Mock\Handle\StaticHandleRegistry;
use Eloquent\Phony\Mock\Mock;

class MockGeneratorIterableTypeHint
implements Mock
{
    public function methodA(
        \Traversable|array $first,
        \Traversable|array|null $second = null
    ) {
        $¤argumentCount = \func_num_args();
        $¤arguments = [];

        if ($¤argumentCount > 0) {
            $¤arguments[] = $first;
        }
        if ($¤argumentCount > 1) {
            $¤arguments[] = $second;
        }
        for ($¤i = 2; $¤i < $¤argumentCount; ++$¤i) {
            $¤arguments[] = \func_get_arg($¤i);
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
