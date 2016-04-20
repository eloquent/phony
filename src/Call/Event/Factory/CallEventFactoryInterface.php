<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Event\Factory;

use Eloquent\Phony\Call\Argument\ArgumentsInterface;
use Eloquent\Phony\Call\Event\CalledEventInterface;
use Eloquent\Phony\Call\Event\ConsumedEventInterface;
use Eloquent\Phony\Call\Event\ReturnedEventInterface;
use Eloquent\Phony\Call\Event\ThrewEventInterface;
use Error;
use Exception;

/**
 * The interface implemented by call event factories.
 */
interface CallEventFactoryInterface
{
    /**
     * Create a new 'called' event.
     *
     * @param callable           $callback  The callback.
     * @param ArgumentsInterface $arguments The arguments.
     *
     * @return CalledEventInterface The newly created event.
     */
    public function createCalled($callback, ArgumentsInterface $arguments);

    /**
     * Create a new 'returned' event.
     *
     * @param mixed $value The return value.
     *
     * @return ReturnedEventInterface The newly created event.
     */
    public function createReturned($value);

    /**
     * Create a new 'thrown' event.
     *
     * @param Exception|Error $exception The thrown exception.
     *
     * @return ThrewEventInterface The newly created event.
     */
    public function createThrew($exception);

    /**
     * Create a new 'produced' event.
     *
     * @param mixed $key   The produced key.
     * @param mixed $value The produced value.
     *
     * @return ProducedEventInterface The newly created event.
     */
    public function createProduced($key, $value);

    /**
     * Create a new 'received' event.
     *
     * @param mixed $value The received value.
     *
     * @return ReceivedEventInterface The newly created event.
     */
    public function createReceived($value);

    /**
     * Create a new 'received exception' event.
     *
     * @param Exception|Error $exception The received exception.
     *
     * @return ReceivedExceptionEventInterface The newly created event.
     */
    public function createReceivedException($exception);

    /**
     * Create a new 'consumed' event.
     *
     * @return ConsumedEventInterface The newly created event.
     */
    public function createConsumed();
}
