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

use Eloquent\Phony\Call\Argument\Arguments;
use Eloquent\Phony\Call\Argument\ArgumentsInterface;
use Eloquent\Phony\Call\Event\CalledEvent;
use Eloquent\Phony\Call\Event\CalledEventInterface;
use Eloquent\Phony\Call\Event\ConsumedEvent;
use Eloquent\Phony\Call\Event\ConsumedEventInterface;
use Eloquent\Phony\Call\Event\ProducedEvent;
use Eloquent\Phony\Call\Event\ReceivedEvent;
use Eloquent\Phony\Call\Event\ReceivedEventInterface;
use Eloquent\Phony\Call\Event\ReceivedExceptionEvent;
use Eloquent\Phony\Call\Event\ReceivedExceptionEventInterface;
use Eloquent\Phony\Call\Event\ReturnedEvent;
use Eloquent\Phony\Call\Event\ReturnedEventInterface;
use Eloquent\Phony\Call\Event\ThrewEvent;
use Eloquent\Phony\Call\Event\ThrewEventInterface;
use Eloquent\Phony\Clock\ClockInterface;
use Eloquent\Phony\Clock\SystemClock;
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Sequencer\SequencerInterface;
use Error;
use Exception;

/**
 * Creates call events.
 */
class CallEventFactory implements CallEventFactoryInterface
{
    /**
     * Get the static instance of this factory.
     *
     * @return CallEventFactoryInterface The static factory.
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self(
                Sequencer::sequence('event-sequence-number'),
                SystemClock::instance()
            );
        }

        return self::$instance;
    }

    /**
     * Construct a new call event factory.
     *
     * @param SequencerInterface $sequencer The sequencer to use.
     * @param ClockInterface     $clock     The clock to use.
     */
    public function __construct(
        SequencerInterface $sequencer,
        ClockInterface $clock
    ) {
        $this->sequencer = $sequencer;
        $this->clock = $clock;
    }

    /**
     * Create a new 'called' event.
     *
     * @param callable           $callback  The callback.
     * @param ArgumentsInterface $arguments The arguments.
     *
     * @return CalledEventInterface The newly created event.
     */
    public function createCalled($callback, ArgumentsInterface $arguments)
    {
        return new CalledEvent(
            $this->sequencer->next(),
            $this->clock->time(),
            $callback,
            $arguments
        );
    }

    /**
     * Create a new 'returned' event.
     *
     * @param mixed $value The return value.
     *
     * @return ReturnedEventInterface The newly created event.
     */
    public function createReturned($value)
    {
        return new ReturnedEvent(
            $this->sequencer->next(),
            $this->clock->time(),
            $value
        );
    }

    /**
     * Create a new 'thrown' event.
     *
     * @param Exception|Error $exception The thrown exception.
     *
     * @return ThrewEventInterface The newly created event.
     */
    public function createThrew($exception)
    {
        return new ThrewEvent(
            $this->sequencer->next(),
            $this->clock->time(),
            $exception
        );
    }

    /**
     * Create a new 'produced' event.
     *
     * @param mixed $key   The produced key.
     * @param mixed $value The produced value.
     *
     * @return ProducedEventInterface The newly created event.
     */
    public function createProduced($key, $value)
    {
        return new ProducedEvent(
            $this->sequencer->next(),
            $this->clock->time(),
            $key,
            $value
        );
    }

    /**
     * Create a new 'received' event.
     *
     * @param mixed $value The received value.
     *
     * @return ReceivedEventInterface The newly created event.
     */
    public function createReceived($value)
    {
        return new ReceivedEvent(
            $this->sequencer->next(),
            $this->clock->time(),
            $value
        );
    }

    /**
     * Create a new 'received exception' event.
     *
     * @param Exception|Error $exception The received exception.
     *
     * @return ReceivedExceptionEventInterface The newly created event.
     */
    public function createReceivedException($exception)
    {
        return new ReceivedExceptionEvent(
            $this->sequencer->next(),
            $this->clock->time(),
            $exception
        );
    }

    /**
     * Create a new 'consumed' event.
     *
     * @return ConsumedEventInterface The newly created event.
     */
    public function createConsumed()
    {
        return new ConsumedEvent(
            $this->sequencer->next(),
            $this->clock->time()
        );
    }

    private static $instance;
    private $sequencer;
    private $clock;
    private $invoker;
}
