<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Event;

use ReflectionFunctionAbstract;

/**
 * The interface implemented by 'called' events.
 */
interface CalledEventInterface extends CallEventInterface
{
    /**
     * Get the called function or method called.
     *
     * @return ReflectionFunctionAbstract The function or method called.
     */
    public function reflector();

    /**
     * Get the $this value.
     *
     * @return object|null The $this value, or null if unbound.
     */
    public function thisValue();

    /**
     * Get the received arguments.
     *
     * @return array<integer,mixed> The received arguments.
     */
    public function arguments();
}
