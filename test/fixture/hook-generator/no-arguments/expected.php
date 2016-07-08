<?php

function functionName()
{
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

    for ($i = 0; $i < $argumentCount; ++$i) {
        $arguments[] = \func_get_arg($i);
    }

    $callback =
        \Eloquent\Phony\Stub\FunctionHookManager::$hooks[$name]['callback'];

    if ($callback instanceof \Eloquent\Phony\Invocation\Invocable) {
        return $callback->invokeWith($arguments);
    }

    return \call_user_func_array($callback, $arguments);
}
