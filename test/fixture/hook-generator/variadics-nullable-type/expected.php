<?php

namespace Foo\Bar;

function functionName(
    $a,
    $b,
    ?\stdClass ...$c
) {
    $argumentCount = \func_num_args();
    $arguments = [];

    if ($argumentCount > 0) {
        $arguments[] = $a;
    }
    if ($argumentCount > 1) {
        $arguments[] = $b;
    }

    for ($i = 2; $i < $argumentCount; ++$i) {
        $arguments[] = $c[$i - 2];
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
