<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call\Event;

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Clock\Clock;
use Eloquent\Phony\Clock\SystemClock;
use Eloquent\Phony\Sequencer\Sequencer;
use Throwable;

/**
 * Creates call events.
 */
class CallEventFactory
{
    /**
     * Get the static instance of this class.
     *
     * @return self The static instance.
     */
    public static function instance(): self
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
     * @param Sequencer $sequencer The sequencer to use.
     * @param Clock     $clock     The clock to use.
     */
    public function __construct(Sequencer $sequencer, Clock $clock)
    {
        $this->sequencer = $sequencer;
        $this->clock = $clock;
    }

    /**
     * Create a new 'called' event.
     *
     * @param callable  $callback  The callback.
     * @param Arguments $arguments The arguments.
     *
     * @return CalledEvent The newly created event.
     */
    public function createCalled(
        callable $callback,
        Arguments $arguments
    ): CalledEvent {
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
     * @return ReturnedEvent The newly created event.
     */
    public function createReturned($value): ReturnedEvent
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
     * @param Throwable $exception The thrown exception.
     *
     * @return ThrewEvent The newly created event.
     */
    public function createThrew(Throwable $exception): ThrewEvent
    {
        return new ThrewEvent(
            $this->sequencer->next(),
            $this->clock->time(),
            $exception
        );
    }

    /**
     * Create a new 'used' event.
     *
     * @return UsedEvent The newly created event.
     */
    public function createUsed(): UsedEvent
    {
        return new UsedEvent($this->sequencer->next(), $this->clock->time());
    }

    /**
     * Create a new 'produced' event.
     *
     * @param mixed $key   The produced key.
     * @param mixed $value The produced value.
     *
     * @return ProducedEvent The newly created event.
     */
    public function createProduced($key, $value): ProducedEvent
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
     * @return ReceivedEvent The newly created event.
     */
    public function createReceived($value): ReceivedEvent
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
     * @param Throwable $exception The received exception.
     *
     * @return ReceivedExceptionEvent The newly created event.
     */
    public function createReceivedException(
        Throwable $exception
    ): ReceivedExceptionEvent {
        return new ReceivedExceptionEvent(
            $this->sequencer->next(),
            $this->clock->time(),
            $exception
        );
    }

    /**
     * Create a new 'consumed' event.
     *
     * @return ConsumedEvent The newly created event.
     */
    public function createConsumed(): ConsumedEvent
    {
        return new ConsumedEvent(
            $this->sequencer->next(),
            $this->clock->time()
        );
    }

    /**
     * @var ?self
     */
    private static $instance;

    /**
     * @var Sequencer
     */
    private $sequencer;

    /**
     * @var Clock
     */
    private $clock;
}
