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
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Sequencer\SequencerInterface;
use Exception;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;

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
     * @return CallInterface            The newly created call.
     * @throws InvalidArgumentException If the supplied callback is invalid.
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
            $returnValue = call_user_func_array($callback, $arguments);
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
    public function create(array $events)
    {
        return new Call($events);
    }

    /**
     * Create a new 'called' event.
     *
     * @param callable|null             $callback  The callback.
     * @param array<integer,mixed>|null $arguments The arguments.
     *
     * @return CalledEventInterface     The newly created event.
     * @throws InvalidArgumentException If the supplied callback is invalid.
     */
    public function createCalledEvent(
        $callback = null,
        array $arguments = null
    ) {
        if (null === $callback) {
            $callback = function () {};
        }
        if (null === $arguments) {
            $arguments = array();
        }

        list($reflector, $thisValue) = $this->callbackDetails($callback);

        return new CalledEvent(
            $reflector,
            $thisValue,
            $arguments,
            $this->sequencer->next(),
            $this->clock->time()
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
    public function createEndEvent($returnValue, Exception $exception = null)
    {
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
    public function createReturnedEvent($returnValue)
    {
        return new ReturnedEvent(
            $returnValue,
            $this->sequencer->next(),
            $this->clock->time()
        );
    }

    /**
     * Create a new 'thrown' event.
     *
     * @param Exception $exception The thrown exception.
     *
     * @return ThrewEventInterface The newly created event.
     */
    public function createThrewEvent(Exception $exception)
    {
        return new ThrewEvent(
            $exception,
            $this->sequencer->next(),
            $this->clock->time()
        );
    }

    /**
     * Get the appropriate reflector and $this value for the supplied callback.
     *
     * @param callable $callback The callback.
     *
     * @return tuple<ReflectionFunctionAbstract,object|null> A 2-tuple of the reflector and $this value.
     * @throws InvalidArgumentException                      If the supplied callback is invalid.
     */
    protected function callbackDetails($callback)
    {
        if (!is_callable($callback, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Unsupported callback of type %s.',
                    var_export(gettype($callback), true)
                )
            );
        }

        if (is_array($callback)) {
            $reflector = new ReflectionMethod($callback[0], $callback[1]);
            $thisValue = $callback[0];
        } elseif (is_string($callback) && false !== strpos($callback, '::')) {
            list($className, $methodName) = explode('::', $callback);

            $reflector = new ReflectionMethod($className, $methodName);
            $thisValue = null;
        } else {
            $reflector = new ReflectionFunction($callback);

            if ($reflector->isClosure() && static::isBoundClosureSupported()) {
                $thisValue = $reflector->getClosureThis();
            } else {
                $thisValue = null;
            }
        }

        return array($reflector, $thisValue);
    }

    /**
     * Returns true if bound closures are supported.
     *
     * @return boolean True if bound closures are supported.
     */
    protected static function isBoundClosureSupported()
    {
        if (null === self::$isBoundClosureSupported) {
            $reflectorReflector = new ReflectionClass('ReflectionFunction');

            self::$isBoundClosureSupported = $reflectorReflector
                ->hasMethod('getClosureThis');
        }

        return self::$isBoundClosureSupported;
    }

    private static $instance;
    private static $isBoundClosureSupported;
    private $sequencer;
    private $clock;
}
