<?php

namespace Foo\Bar;

function functionName(
    $first,
    &$second,
    \Eloquent\Phony\Test\TestClassA $third,
    $fourth = null,
    $fifth = 111,
    $sixth = array (
),
    $seventh = array (
  0 => 'valueA',
  1 => 'valueB',
),
    $eighth = array (
  'keyA' => 'valueA',
  'keyB' => 'valueB',
),
    ?\Eloquent\Phony\Test\TestClassA $ninth = null,
    array $tenth = array (
),
    ?array $eleventh = null
) {
    $argumentCount = \func_num_args();
    $arguments = [];

    if ($argumentCount > 0) {
        $arguments[] = $first;
    }
    if ($argumentCount > 1) {
        $arguments[] = &$second;
    }
    if ($argumentCount > 2) {
        $arguments[] = $third;
    }
    if ($argumentCount > 3) {
        $arguments[] = $fourth;
    }
    if ($argumentCount > 4) {
        $arguments[] = $fifth;
    }
    if ($argumentCount > 5) {
        $arguments[] = $sixth;
    }
    if ($argumentCount > 6) {
        $arguments[] = $seventh;
    }
    if ($argumentCount > 7) {
        $arguments[] = $eighth;
    }
    if ($argumentCount > 8) {
        $arguments[] = $ninth;
    }
    if ($argumentCount > 9) {
        $arguments[] = $tenth;
    }
    if ($argumentCount > 10) {
        $arguments[] = $eleventh;
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
