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
use Eloquent\Phony\Call\Event\GeneratedEvent;
use Eloquent\Phony\Call\Event\GeneratedEventInterface;
use Eloquent\Phony\Call\Event\GeneratorEventInterface;
use Eloquent\Phony\Call\Event\ResponseEventInterface;
use Eloquent\Phony\Call\Event\ReturnedEvent;
use Eloquent\Phony\Call\Event\ReturnedEventInterface;
use Eloquent\Phony\Call\Event\SentEvent;
use Eloquent\Phony\Call\Event\SentEventInterface;
use Eloquent\Phony\Call\Event\SentExceptionEvent;
use Eloquent\Phony\Call\Event\SentExceptionEventInterface;
use Eloquent\Phony\Call\Event\ThrewEvent;
use Eloquent\Phony\Call\Event\ThrewEventInterface;
use Eloquent\Phony\Call\Event\YieldedEvent;
use Eloquent\Phony\Call\Event\YieldedEventInterface;
use Eloquent\Phony\Clock\ClockInterface;
use Eloquent\Phony\Clock\SystemClock;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Invocation\InvokerInterface;
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Sequencer\SequencerInterface;
use Eloquent\Phony\Spy\SpyInterface;
use Exception;
use Generator;

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
     * @param SpyInterface|null         $spy       The spy to record the call to.
     *
     * @return CallInterface The newly created call.
     */
    public function record(
        $callback = null,
        array $arguments = null,
        SpyInterface $spy = null
    ) {
        if (null === $callback) {
            $callback = function () {};
        }
        if (null === $arguments) {
            $arguments = array();
        }

        $call = $this->create($this->createCalledEvent($callback, $arguments));

        if ($spy) {
            $spy->addCall($call);
        }

        $returnValue = null;
        $exception = null;

        try {
            $returnValue = $this->invoker->callWith($callback, $arguments);
        } catch (Exception $exception) {}

        $call->setResponseEvent(
            $this->createResponseEvent($returnValue, $exception)
        );

        return $call;
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
     * @param mixed $value The return value.
     *
     * @return ReturnedEventInterface The newly created event.
     */
    public function createReturnedEvent($value = null)
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
     * Create a new 'generated' event.
     *
     * @param Generator|null $generator The generator.
     *
     * @return GeneratedEventInterface The newly created event.
     */
    public function createGeneratedEvent(Generator $generator = null)
    {
        return new GeneratedEvent(
            $this->sequencer->next(),
            $this->clock->time(),
            $generator
        );
    }

    /**
     * Create a new 'yielded' event.
     *
     * @param mixed $value The yielded value.
     * @param mixed $key   The yielded key.
     *
     * @return YieldedEventInterface The newly created event.
     */
    public function createYieldedEvent($value = null, $key = null)
    {
        return new YieldedEvent(
            $this->sequencer->next(),
            $this->clock->time(),
            $value,
            $key
        );
    }

    /**
     * Create a new 'sent' event.
     *
     * @param mixed $value The sent value.
     *
     * @return SentEventInterface The newly created event.
     */
    public function createSentEvent($value = null)
    {
        return new SentEvent(
            $this->sequencer->next(),
            $this->clock->time(),
            $value
        );
    }

    /**
     * Create a new 'sent exception' event.
     *
     * @param Exception|null $exception The sent exception.
     *
     * @return SentExceptionEventInterface The newly created event.
     */
    public function createSentExceptionEvent(Exception $exception = null)
    {
        return new SentExceptionEvent(
            $this->sequencer->next(),
            $this->clock->time(),
            $exception
        );
    }

    private static $instance;
    private $sequencer;
    private $clock;
    private $invoker;
}
