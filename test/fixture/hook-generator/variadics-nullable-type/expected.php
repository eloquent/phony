<?php

namespace Foo\Bar;

function functionName(
    $a0,
    $a1,
    ?\stdClass ...$a2
) {
    $argumentCount = \func_num_args();
    $arguments = [];

    if ($argumentCount > 0) {
        $arguments[] = $a0;
    }
    if ($argumentCount > 1) {
        $arguments[] = $a1;
    }

    for ($i = 2; $i < $argumentCount; ++$i) {
        $arguments[] = $a2[$i - 2];
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
