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
use Eloquent\Phony\Call\Event\CallEventInterface;
use Eloquent\Phony\Call\Event\CalledEvent;
use Eloquent\Phony\Call\Event\CalledEventInterface;
use Eloquent\Phony\Call\Event\EndEventInterface;
use Eloquent\Phony\Call\Event\ReturnedEvent;
use Eloquent\Phony\Call\Event\ReturnedEventInterface;
use Eloquent\Phony\Call\Event\ThrewEvent;
use Eloquent\Phony\Call\Event\ThrewEventInterface;
use Eloquent\Phony\Clock\ClockInterface;
use Eloquent\Phony\Clock\SystemClock;
use Eloquent\Phony\Invocable\InvocableUtils;
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
     */
    public function __construct(
        SequencerInterface $sequencer = null,
        ClockInterface $clock = null
    ) {
        if (null === $sequencer) {
            $sequencer = Sequencer::instance();
        }
        if (null === $clock) {
            $clock = SystemClock::instance();
        }

        $this->sequencer = $sequencer;
        $this->clock = $clock;
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
            $returnValue = InvocableUtils::callWith($callback, $arguments);
        } catch (Exception $exception) {}

        return $this->create(
            array($calledEvent, $this->createEndEvent($returnValue, $exception))
        );
    }

    /**
     * Create a new call.
     *
     * @param array<integer,CallEventInterface> $events The events.
     *
     * @return CallInterface The newly created call.
     */
    public function create(array $events = null)
    {
        if (null === $events) {
            $events = array(
                $this->createCalledEvent(),
                $this->createReturnedEvent(),
            );
        }

        return new Call($events);
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
     * Create a new end event.
     *
     * @param mixed          $returnValue The return value.
     * @param Exception|null $exception   The thrown exception, or null if no exception was thrown.
     *
     * @return EndEventInterface The newly created event.
     */
    public function createEndEvent(
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

    private static $instance;
    private $sequencer;
    private $clock;
}
