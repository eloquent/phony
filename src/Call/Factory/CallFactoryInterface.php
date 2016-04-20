<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Factory;

use Eloquent\Phony\Call\Argument\ArgumentsInterface;
use Eloquent\Phony\Call\CallInterface;
use Eloquent\Phony\Spy\SpyInterface;

/**
 * The interface implemented by call factories.
 */
interface CallFactoryInterface
{
    /**
     * Record call details by invoking a callback.
     *
     * @param callable           $callback  The callback.
     * @param ArgumentsInterface $arguments The arguments.
     * @param SpyInterface       $spy       The spy to record the call to.
     *
     * @return CallInterface The newly created call.
     */
    public function record(
        $callback,
        ArgumentsInterface $arguments,
        SpyInterface $spy
    );
}
