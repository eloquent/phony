<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Factory;

use Eloquent\Phony\Call\CallInterface;
use Eloquent\Phony\Call\Event\CalledEventInterface;
use Eloquent\Phony\Call\Event\ResponseEventInterface;
use Eloquent\Phony\Call\Event\TraversableEventInterface;
use Eloquent\Phony\Spy\SpyInterface;
use InvalidArgumentException;

/**
 * The interface implemented by call factories.
 */
interface CallFactoryInterface
{
    /**
     * Record call details by invoking a callback.
     *
     * @param callable|null             $callback  The callback.
     * @param array<integer,mixed>|null $arguments The arguments.
     * @param SpyInterface|null         $spy       The spy to record the call to.
     *
     * @return CallInterface The newly created call.
     */
    public function record(
        $callback = null,
        array $arguments = null,
        SpyInterface $spy = null
    );

    /**
     * Create a new call.
     *
     * @param CalledEventInterface|null                     $calledEvent       The 'called' event.
     * @param ResponseEventInterface|null                   $responseEvent     The response event, or null if the call has not yet responded.
     * @param array<integer,TraversableEventInterface>|null $traversableEvents The traversable events.
     * @param ResponseEventInterface|null                   $endEvent          The end event, or null if the call has not yet completed.
     *
     * @return CallInterface            The newly created call.
     * @throws InvalidArgumentException If the supplied calls respresent an invalid call state.
     */
    public function create(
        CalledEventInterface $calledEvent = null,
        ResponseEventInterface $responseEvent = null,
        array $traversableEvents = null,
        ResponseEventInterface $endEvent = null
    );
}
