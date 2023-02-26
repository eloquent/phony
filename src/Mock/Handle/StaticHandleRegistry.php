<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock\Handle;

/**
 * Stores static handles for all classes.
 */
class StaticHandleRegistry
{
    /**
     * @var array<class-string,StaticHandle>
     */
    public static $handles = [];
}
