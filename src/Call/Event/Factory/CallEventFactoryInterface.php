<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Event\Factory;

use Eloquent\Phony\Call\Event\CalledEventInterface;
use Eloquent\Phony\Call\Event\GeneratedEventInterface;
use Eloquent\Phony\Call\Event\ResponseEventInterface;
use Eloquent\Phony\Call\Event\ReturnedEventInterface;
use Eloquent\Phony\Call\Event\SentEventInterface;
use Eloquent\Phony\Call\Event\SentExceptionEventInterface;
use Eloquent\Phony\Call\Event\ThrewEventInterface;
use Eloquent\Phony\Call\Event\YieldedEventInterface;
use Exception;
use Generator;

/**
 * The interface implemented by call event factories.
 */
interface CallEventFactoryInterface
{
    /**
     * Create a new 'called' event.
     *
     * @param callable|null             $callback  The callback.
     * @param array<integer,mixed>|null $arguments The arguments.
     *
     * @return CalledEventInterface The newly created event.
     */
    public function createCalled($callback = null, array $arguments = null);

    /**
     * Create a new response event.
     *
     * @param mixed          $returnValue The return value.
     * @param Exception|null $exception   The thrown exception, or null if no exception was thrown.
     *
     * @return ResponseEventInterface The newly created event.
     */
    public function createResponse(
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
    public function createReturned($value = null);

    /**
     * Create a new 'thrown' event.
     *
     * @param Exception|null $exception The thrown exception.
     *
     * @return ThrewEventInterface The newly created event.
     */
    public function createThrew(Exception $exception = null);

    /**
     * Create a new 'generated' event.
     *
     * @param Generator|null $generator The generator.
     *
     * @return GeneratedEventInterface The newly created event.
     */
    public function createGenerated(Generator $generator = null);

    /**
     * Create a new 'yielded' event.
     *
     * @param mixed $value The yielded value.
     * @param mixed $key   The yielded key.
     *
     * @return YieldedEventInterface The newly created event.
     */
    public function createYielded($value = null, $key = null);

    /**
     * Create a new 'sent' event.
     *
     * @param mixed $value The sent value.
     *
     * @return SentEventInterface The newly created event.
     */
    public function createSent($value = null);

    /**
     * Create a new 'sent exception' event.
     *
     * @param Exception|null $exception The sent exception.
     *
     * @return SentExceptionEventInterface The newly created event.
     */
    public function createSentException(Exception $exception = null);
}
