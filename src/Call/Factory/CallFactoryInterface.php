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
use Eloquent\Phony\Call\Event\GeneratedEventInterface;
use Eloquent\Phony\Call\Event\GeneratorEventInterface;
use Eloquent\Phony\Call\Event\ResponseEventInterface;
use Eloquent\Phony\Call\Event\ReturnedEventInterface;
use Eloquent\Phony\Call\Event\SentEventInterface;
use Eloquent\Phony\Call\Event\SentExceptionEventInterface;
use Eloquent\Phony\Call\Event\ThrewEventInterface;
use Eloquent\Phony\Call\Event\YieldedEventInterface;
use Eloquent\Phony\Spy\SpyInterface;
use Exception;
use Generator;

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
     * @param CalledEventInterface|null                   $calledEvent     The 'called' event.
     * @param ResponseEventInterface|null                 $responseEvent   The response event, or null if the call has not yet completed.
     * @param array<integer,GeneratorEventInterface>|null $generatorEvents The generator events.
     *
     * @return CallInterface The newly created call.
     */
    public function create(
        CalledEventInterface $calledEvent = null,
        ResponseEventInterface $responseEvent = null,
        array $generatorEvents = null
    );

    /**
     * Create a new 'called' event.
     *
     * @param callable|null             $callback  The callback.
     * @param array<integer,mixed>|null $arguments The arguments.
     *
     * @return CalledEventInterface The newly created event.
     */
    public function createCalledEvent(
        $callback = null,
        array $arguments = null
    );

    /**
     * Create a new response event.
     *
     * @param mixed          $returnValue The return value.
     * @param Exception|null $exception   The thrown exception, or null if no exception was thrown.
     *
     * @return ResponseEventInterface The newly created event.
     */
    public function createResponseEvent(
        $returnValue = null,
        Exception $exception = null
    );

    /**
     * Create a new 'returned' event.
     *
     * @param mixed $value The return value.
     *
     * @return ReturnedEventInterface The newly created event.
     */
    public function createReturnedEvent($value = null);

    /**
     * Create a new 'thrown' event.
     *
     * @param Exception|null $exception The thrown exception.
     *
     * @return ThrewEventInterface The newly created event.
     */
    public function createThrewEvent(Exception $exception = null);

    /**
     * Create a new 'generated' event.
     *
     * @param Generator|null $generator The generator.
     *
     * @return GeneratedEventInterface The newly created event.
     */
    public function createGeneratedEvent(Generator $generator = null);

    /**
     * Create a new 'yielded' event.
     *
     * @param mixed $value The yielded value.
     * @param mixed $key   The yielded key.
     *
     * @return YieldedEventInterface The newly created event.
     */
    public function createYieldedEvent($value = null, $key = null);

    /**
     * Create a new 'sent' event.
     *
     * @param mixed $value The sent value.
     *
     * @return SentEventInterface The newly created event.
     */
    public function createSentEvent($value = null);

    /**
     * Create a new 'sent exception' event.
     *
     * @param Exception|null $exception The sent exception.
     *
     * @return SentExceptionEventInterface The newly created event.
     */
    public function createSentExceptionEvent(Exception $exception = null);
}
