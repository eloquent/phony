<?php

function functionName(
    $a0,
    $a1,
    \stdClass ...$a2
) {
    $name = 'functionName';

    if (
        !isset(
            \Eloquent\Phony\Stub\FunctionHookManager::$hooks[$name]['callback']
        )
    ) {
        throw new \Error('Call to undefined function functionName()');
    }

    $argumentCount = \func_num_args();
    $arguments = array();

    if ($argumentCount > 0) {
        $arguments[] = $a0;
    }
    if ($argumentCount > 1) {
        $arguments[] = $a1;
    }

    for ($i = 2; $i < $argumentCount; ++$i) {
        $arguments[] = $a2[$i - 2];
    }

    $callback =
        \Eloquent\Phony\Stub\FunctionHookManager::$hooks[$name]['callback'];

    if ($callback instanceof \Eloquent\Phony\Invocation\Invocable) {
        return $callback->invokeWith($arguments);
    }

    return \call_user_func_array($callback, $arguments);
}
