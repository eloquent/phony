<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Event\Factory;

use Eloquent\Phony\Call\Argument\ArgumentsInterface;
use Eloquent\Phony\Call\Event\CalledEventInterface;
use Eloquent\Phony\Call\Event\ConsumedEventInterface;
use Eloquent\Phony\Call\Event\ResponseEventInterface;
use Eloquent\Phony\Call\Event\ReturnedEventInterface;
use Eloquent\Phony\Call\Event\ThrewEventInterface;
use Error;
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
     * @param callable|null            $callback  The callback.
     * @param ArgumentsInterface|array $arguments The arguments.
     *
     * @return CalledEventInterface The newly created event.
     */
    public function createCalled($callback = null, $arguments = array());

    /**
     * Create a new response event.
     *
     * @param mixed                $returnValue The return value.
     * @param Exception|Error|null $exception   The thrown exception, or null if no exception was thrown.
     *
     * @return ResponseEventInterface The newly created event.
     */
    public function createResponse($returnValue = null, $exception = null);

    /**
     * Create a new 'returned' event.
     *
     * @param mixed $value The return value.
     *
     * @return ReturnedEventInterface The newly created event.
     */
    public function createReturned($value = null);

    /**
     * Create a new 'returned' event for a generator.
     *
     * @param Generator|null $generator The generator.
     *
     * @return ReturnedEventInterface The newly created event.
     */
    public function createGenerated(Generator $generator = null);

    /**
     * Create a new 'thrown' event.
     *
     * @param Exception|Error|null $exception The thrown exception.
     *
     * @return ThrewEventInterface The newly created event.
     */
    public function createThrew($exception = null);

    /**
     * Create a new 'produced' event.
     *
     * If called with one argument, the argument is treated as the value.
     *
     * If called with two arguments, the first is treated as the key, and the
     * second as the value.
     *
     * @param mixed $keyOrValue The produced key or value.
     * @param mixed $value      The produced value.
     *
     * @return ProducedEventInterface The newly created event.
     */
    public function createProduced($keyOrValue = null, $value = null);

    /**
     * Create a new 'received' event.
     *
     * @param mixed $value The received value.
     *
     * @return ReceivedEventInterface The newly created event.
     */
    public function createReceived($value = null);

    /**
     * Create a new 'received exception' event.
     *
     * @param Exception|Error|null $exception The received exception.
     *
     * @return ReceivedExceptionEventInterface The newly created event.
     */
    public function createReceivedException($exception = null);

    /**
     * Create a new 'consumed' event.
     *
     * @return ConsumedEventInterface The newly created event.
     */
    public function createConsumed();
}
