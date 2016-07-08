<?php

function functionName(
    $a0,
    &$a1,
    $a2,
    $a3,
    $a4,
    $a5,
    $a6,
    \Eloquent\Phony\Test\TestClassA $a7,
    \Eloquent\Phony\Test\TestClassA $a8 = null,
    array $a9 = array(
),
    array $a10 = null
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
        $arguments[] = &$a1;
    }
    if ($argumentCount > 2) {
        $arguments[] = $a2;
    }
    if ($argumentCount > 3) {
        $arguments[] = $a3;
    }
    if ($argumentCount > 4) {
        $arguments[] = $a4;
    }
    if ($argumentCount > 5) {
        $arguments[] = $a5;
    }
    if ($argumentCount > 6) {
        $arguments[] = $a6;
    }
    if ($argumentCount > 7) {
        $arguments[] = $a7;
    }
    if ($argumentCount > 8) {
        $arguments[] = $a8;
    }
    if ($argumentCount > 9) {
        $arguments[] = $a9;
    }
    if ($argumentCount > 10) {
        $arguments[] = $a10;
    }

    for ($i = 11; $i < $argumentCount; ++$i) {
        $arguments[] = \func_get_arg($i);
    }

    $callback =
        \Eloquent\Phony\Stub\FunctionHookManager::$hooks[$name]['callback'];

    if ($callback instanceof \Eloquent\Phony\Invocation\Invocable) {
        return $callback->invokeWith($arguments);
    }

    return \call_user_func_array($callback, $arguments);
}
