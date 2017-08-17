<?php

namespace Foo\Bar;

function functionName()
{
    $argumentCount = \func_num_args();
    $arguments = [];

    for ($i = 0; $i < $argumentCount; ++$i) {
        $arguments[] = \func_get_arg($i);
    }

    $name = 'foo\\bar\\functionname';

    if (
        !isset(
            \Eloquent\Phony\Hook\FunctionHookManager::$hooks[$name]['callback']
        )
    ) {
        return \functionName(...$arguments);
    }

    $callback =
        \Eloquent\Phony\Hook\FunctionHookManager::$hooks[$name]['callback'];

    if ($callback instanceof \Eloquent\Phony\Invocation\Invocable) {
        return $callback->invokeWith($arguments);
    }

    return $callback(...$arguments);
}
