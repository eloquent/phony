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

use Eloquent\Phony\Call\Call;
use Eloquent\Phony\Call\CallInterface;
use Eloquent\Phony\Call\Event\CalledEvent;
use Eloquent\Phony\Call\Event\CalledEventInterface;
use Eloquent\Phony\Call\Event\GeneratorEventInterface;
use Eloquent\Phony\Call\Event\ResponseEventInterface;
use Eloquent\Phony\Call\Event\ReturnedEvent;
use Eloquent\Phony\Call\Event\ReturnedEventInterface;
use Eloquent\Phony\Call\Event\SentValueEvent;
use Eloquent\Phony\Call\Event\SentValueEventInterface;
use Eloquent\Phony\Call\Event\ThrewEvent;
use Eloquent\Phony\Call\Event\ThrewEventInterface;
use Eloquent\Phony\Clock\ClockInterface;
use Eloquent\Phony\Clock\SystemClock;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Invocation\InvokerInterface;
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Sequencer\SequencerInterface;
use Exception;

/**
 * Creates calls.
 *
 * @internal
 */
class CallFactory implements CallFactoryInterface
{
    /**
     * Get the static instance of this factory.
     *
     * @return CallFactoryInterface The static factory.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Construct a new call factory.
     *
     * @param SequencerInterface|null $sequencer The sequencer to use.
     * @param ClockInterface|null     $clock     The clock to use.
     * @param InvokerInterface|null   $invoker   The invoker to use.
     */
    public function __construct(
        SequencerInterface $sequencer = null,
        ClockInterface $clock = null,
        InvokerInterface $invoker = null
    ) {
        if (null === $sequencer) {
            $sequencer = Sequencer::instance();
        }
        if (null === $clock) {
            $clock = SystemClock::instance();
        }
        if (null === $invoker) {
            $invoker = Invoker::instance();
        }

        $this->sequencer = $sequencer;
        $this->clock = $clock;
        $this->invoker = $invoker;
    }

    /**
     * Get the sequencer.
     *
     * @return SequencerInterface The sequencer.
     */
    public function sequencer()
    {
        return $this->sequencer;
    }

    /**
     * Get the clock.
     *
     * @return ClockInterface The clock.
     */
    public function clock()
    {
        return $this->clock;
    }

    /**
     * Get the invoker.
     *
     * @return InvokerInterface The invoker.
     */
    public function invoker()
    {
        return $this->invoker;
    }

    /**
     * Record call details by invoking a callback.
     *
     * @param callable|null             $callback  The callback.
     * @param array<integer,mixed>|null $arguments The arguments.
     *
     * @return CallInterface The newly created call.
     */
    public function record(
        $callback = null,
        array $arguments = null
    ) {
        if (null === $callback) {
            $callback = function () {};
        }
        if (null === $arguments) {
            $arguments = array();
        }

        $returnValue = null;
        $exception = null;
        $calledEvent = $this->createCalledEvent($callback, $arguments);

        try {
            $returnValue = $this->invoker->callWith($callback, $arguments);
        } catch (Exception $exception) {}

        return $this->create(
            $calledEvent,
            $this->createResponseEvent($returnValue, $exception)
        );
    }

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
    ) {
        if (null === $calledEvent) {
            $calledEvent = $this->createCalledEvent();
        }

        return new Call($calledEvent, $responseEvent, $generatorEvents);
    }

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
    ) {
        return new CalledEvent(
            $this->sequencer->next(),
            $this->clock->time(),
            $callback,
            $arguments
        );
    }

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
    ) {
        if ($exception) {
            return $this->createThrewEvent($exception);
        }

        return $this->createReturnedEvent($returnValue);
    }

    /**
     * Create a new 'returned' event.
     *
     * @param mixed $returnValue The return value.
     *
     * @return ReturnedEventInterface The newly created event.
     */
    public function createReturnedEvent($returnValue = null)
    {
        return new ReturnedEvent(
            $this->sequencer->next(),
            $this->clock->time(),
            $returnValue
        );
    }

    /**
     * Create a new 'thrown' event.
     *
     * @param Exception|null $exception The thrown exception.
     *
     * @return ThrewEventInterface The newly created event.
     */
    public function createThrewEvent(Exception $exception = null)
    {
        return new ThrewEvent(
            $this->sequencer->next(),
            $this->clock->time(),
            $exception
        );
    }

    /**
     * Create a new 'sent value' event.
     *
     * @param mixed $sentValue The sent value.
     *
     * @return SentValueEventInterface The newly created event.
     */
    public function createSentValueEvent($sentValue = null)
    {
        return new SentValueEvent(
            $this->sequencer->next(),
            $this->clock->time(),
            $sentValue
        );
    }

    private static $instance;
    private $sequencer;
    private $clock;
    private $invoker;
}
