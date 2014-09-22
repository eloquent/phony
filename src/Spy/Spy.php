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
use Eloquent\Phony\Spy\Exception\UndefinedSubjectException;
use Exception;
use ReflectionClass;
use ReflectionFunction;

/**
 * Spies on a function or method.
 */
class Spy implements SpyInterface
{
    /**
     * Construct a new spy.
     *
     * @param callable|null             $subject     The subject, or null to create an unbound spy.
     * @param SequencerInterface|null   $sequencer   The sequencer to use.
     * @param ClockInterface|null       $clock       The clock to use.
     * @param CallFactoryInterface|null $callFactory The call factory to use.
     */
    public function __construct(
        $subject = null,
        SequencerInterface $sequencer = null,
        ClockInterface $clock = null,
        CallFactoryInterface $callFactory = null
    ) {
        if (null === $sequencer) {
            $sequencer = new Sequencer();
        }
        if (null === $clock) {
            $clock = SystemClock::instance();
        }
        if (null === $callFactory) {
            $callFactory = CallFactory::instance();
        }

        $this->subject = $subject;
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
     * Returns true if this spy has a subject.
     *
     * @return boolean True if this spy has a subject.
     */
    public function hasSubject()
    {
        return null !== $this->subject;
    }

    /**
     * Get the subject.
     *
     * @return callable                  The subject.
     * @throws UndefinedSubjectException If there is no subject.
     */
    public function subject()
    {
        if (null === $this->subject) {
            throw new UndefinedSubjectException($this);
        }

        return $this->subject;
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

        if (null === $this->subject) {
            $thisValue = null;
            $returnValue = null;
            $exception = null;
            $startTime = $this->clock->time();
            $endTime = $startTime;
        } else {
            $reflectorReflector = new ReflectionClass('ReflectionFunction');
            $reflector = new ReflectionFunction($this->subject);

            if ($reflectorReflector->hasMethod('getClosureThis')) {
                $thisValue = $reflector->getClosureThis();
            } else { // @codeCoverageIgnoreStart
                $thisValue = null;
            } // @codeCoverageIgnoreEnd

            $returnValue = null;
            $exception = null;
            $startTime = $this->clock->time();
            try {
                $returnValue = call_user_func_array($this->subject, $arguments);
            } catch (Exception $exception) {
                // returned in tuple
            }

            $endTime = $this->clock->time();
        }

        $this->calls[] = $this->callFactory->create(
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

    private $subject;
    private $sequencer;
    private $clock;
    private $callFactory;
    private $calls;
}
