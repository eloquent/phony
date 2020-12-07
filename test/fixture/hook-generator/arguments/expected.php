<?php

namespace Foo\Bar;

function functionName(
    $a0,
    &$a1,
    \Eloquent\Phony\Test\TestClassA $a2,
    $a3 = null,
    $a4 = 111,
    $a5 = array (
),
    $a6 = array (
  0 => 'valueA',
  1 => 'valueB',
),
    $a7 = array (
  'keyA' => 'valueA',
  'keyB' => 'valueB',
),
    ?\Eloquent\Phony\Test\TestClassA $a8 = null,
    array $a9 = array (
),
    ?array $a10 = null
) {
    $argumentCount = \func_num_args();
    $arguments = [];

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
