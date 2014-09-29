<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Spy;

use Eloquent\Phony\Call\Call;
use Eloquent\Phony\Call\CallInterface;
use Eloquent\Phony\Call\Factory\CallFactory;
use Eloquent\Phony\Call\Factory\CallFactoryInterface;
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
 * Spies on a function or method.
 *
 * @internal
 */
class Spy implements SpyInterface
{
    /**
     * Construct a new spy.
     *
     * @param callable|null                   $subject     The subject, or null to create an unbound spy.
     * @param ReflectionFunctionAbstract|null $reflector   The reflector to use.
     * @param SequencerInterface|null         $sequencer   The sequencer to use.
     * @param ClockInterface|null             $clock       The clock to use.
     * @param CallFactoryInterface|null       $callFactory The call factory to use.
     *
     * @throws InvalidArgumentException If the supplied subject is not supported.
     */
    public function __construct(
        $subject = null,
        ReflectionFunctionAbstract $reflector = null,
        SequencerInterface $sequencer = null,
        ClockInterface $clock = null,
        CallFactoryInterface $callFactory = null
    ) {
        if (null === $subject) {
            $subject = function () {};
        }
        if (null === $reflector) {
            $reflector = $this->reflectorBySubject($subject);
        }
        if (null === $sequencer) {
            $sequencer = Sequencer::instance();
        }
        if (null === $clock) {
            $clock = SystemClock::instance();
        }
        if (null === $callFactory) {
            $callFactory = CallFactory::instance();
        }

        $this->subject = $subject;
        $this->reflector = $reflector;
        $this->sequencer = $sequencer;
        $this->clock = $clock;
        $this->callFactory = $callFactory;
        $this->calls = array();
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
     * Get the call factory.
     *
     * @return CallFactoryInterface The call factory.
     */
    public function callFactory()
    {
        return $this->callFactory;
    }

    /**
     * Get the subject.
     *
     * @return callable The subject.
     */
    public function subject()
    {
        return $this->subject;
    }

    /**
     * Get the reflector.
     *
     * @return ReflectionFunctionAbstract The reflector.
     */
    public function reflector()
    {
        return $this->reflector;
    }

    /**
     * Set the calls.
     *
     * @param array<CallInterface> $calls The calls.
     */
    public function setCalls(array $calls)
    {
        $this->calls = $calls;
    }

    /**
     * Add a call.
     *
     * @param CallInterface $call The call.
     */
    public function addCall(CallInterface $call)
    {
        $this->calls[] = $call;
    }

    /**
     * Get the calls.
     *
     * @return array<CallInterface> The calls.
     */
    public function calls()
    {
        return $this->calls;
    }

    /**
     * Record a call by invocation.
     *
     * @param mixed $arguments,...
     *
     * @return mixed     The result of invocation.
     * @throws Exception If the subject throws an exception.
     */
    public function __invoke()
    {
        $arguments = func_get_args();
        $returnValue = null;
        $exception = null;
        $startTime = $this->clock->time();

        try {
            $returnValue = call_user_func_array($this->subject, $arguments);
        } catch (Exception $exception) {
            // returned in tuple
        }

        $endTime = $this->clock->time();

        if (static::isBoundClosureSupported()) {
            $thisValue = $this->reflector->getClosureThis();
        } else { // @codeCoverageIgnoreStart
            $thisValue = null;
        } // @codeCoverageIgnoreEnd

        $this->calls[] = $this->callFactory->create(
            $this->reflector,
            $arguments,
            $returnValue,
            $this->sequencer->next(),
            $startTime,
            $endTime,
            $exception,
            $thisValue
        );

        if ($exception) {
            throw $exception;
        }

        return $returnValue;
    }

    /**
     * Get the appropriate reflector for the supplied subject.
     *
     * @param callable $subject The subject.
     *
     * @return ReflectionFunctionAbstract The reflector.
     * @throws InvalidArgumentException   If the supplied subject is invalid.
     */
    protected function reflectorBySubject($subject)
    {
        if (!is_callable($subject)) {
            throw new InvalidArgumentException('Unsupported spy subject.');
        }

        if (is_array($subject)) {
            return new ReflectionMethod($subject[0], $subject[1]);
        }

        if (is_string($subject) && false !== strpos($subject, '::')) {
            list($className, $methodName) = explode('::', $subject);

            return new ReflectionMethod($className, $methodName);
        }

        return new ReflectionFunction($subject);
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

    private static $isBoundClosureSupported;
    private $subject;
    private $reflector;
    private $sequencer;
    private $clock;
    private $callFactory;
    private $calls;
}
