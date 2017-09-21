<?php

declare(strict_types=1);

namespace Eloquent\Phony\Facade;

use Eloquent\Phony\Assertion\ExceptionAssertionRecorder;

/**
 * A place for namespaced global state.
 */
class Globals
{
    /**
     * The container used by the static facade and facade functions.
     *
     * @var FacadeContainer
     */
    public static $container;
}

Globals::$container = new FacadeContainer(new ExceptionAssertionRecorder());
