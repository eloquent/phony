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

use Eloquent\Phony\Call\CallVerifierInterface;
use Eloquent\Phony\Call\Exception\UndefinedCallException;
use Exception;

/**
 * The interface implemented by spy verifiers.
 */
interface SpyVerifierInterface extends SpyInterface
{
    /**
     * Get the calls.
     *
     * @return array<CallVerifierInterface> The calls.
     */
    public function calls();

    /**
     * Get the number of calls.
     *
     * @return integer The number of calls.
     */
    public function callCount();

    /**
     * Get the call at a specific index.
     *
     * @param integer $index The call index.
     *
     * @return CallVerifierInterface  The call.
     * @throws UndefinedCallException If there is no call at the index.
     */
    public function callAt($index);

    /**
     * Get the first call.
     *
     * @return CallVerifierInterface  The call.
     * @throws UndefinedCallException If there is no first call.
     */
    public function firstCall();

    /**
     * Get the last call.
     *
     * @return CallVerifierInterface  The call.
     * @throws UndefinedCallException If there is no last call.
     */
    public function lastCall();

    /**
     * Returns true if called at least once.
     *
     * @return boolean True if called at least once.
     */
    public function called();

    /**
     * Returns true if called only once.
     *
     * @return boolean True if called only once.
     */
    public function calledOnce();

    /**
     * Returns true if called an exact amount of times.
     *
     * @return boolean True if called an exact amount of times.
     */
    public function calledTimes($times);

    /**
     * Returns true if this spy was called before the supplied spy.
     *
     * @param SpyInterface $spy Another spy.
     *
     * @return boolean True if this spy was called before the supplied spy.
     */
    public function calledBefore(SpyInterface $spy);

    /**
     * Returns true if this spy was called after the supplied spy.
     *
     * @param SpyInterface $spy Another spy.
     *
     * @return boolean True if this spy was called after the supplied spy.
     */
    public function calledAfter(SpyInterface $spy);

    /**
     * Returns true if called with the supplied arguments (and possibly others)
     * at least once.
     *
     * @param mixed $argument,... The arguments.
     *
     * @return boolean True if called with the supplied arguments at least once.
     */
    public function calledWith();

    /**
     * Returns true if always called with the supplied arguments (and possibly
     * others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @return boolean True if always called with the supplied arguments.
     */
    public function alwaysCalledWith();

    /**
     * Returns true if called with the supplied arguments and no others at least
     * once.
     *
     * @param mixed $argument,... The arguments.
     *
     * @return boolean True if called with the supplied arguments at least once.
     */
    public function calledWithExactly();

    /**
     * Returns true if always called with the supplied arguments and no others.
     *
     * @param mixed $argument,... The arguments.
     *
     * @return boolean True if always called with the supplied arguments.
     */
    public function alwaysCalledWithExactly();

    /**
     * Returns true if never called with the supplied arguments (and possibly
     * others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @return boolean True if never called with the supplied arguments.
     */
    public function neverCalledWith();

    /**
     * Returns true if never called with the supplied arguments and no others.
     *
     * @param mixed $argument,... The arguments.
     *
     * @return boolean True if never called with the supplied arguments.
     */
    public function neverCalledWithExactly();

    /**
     * Returns true if the $this value is the same as the supplied value for at
     * least one call.
     *
     * @param object|null $value The possible $this value.
     *
     * @return boolean True if the $this value is the same as the supplied value for at least one call.
     */
    public function calledOn($value);

    /**
     * Returns true if the $this value is the same as the supplied value for
     * all calls.
     *
     * @param object|null $value The possible $this value.
     *
     * @return boolean True if the $this value is the same as the supplied value for all calls.
     */
    public function alwaysCalledOn($value);

    /**
     * Returns true if this spy returned the supplied value at least once.
     *
     * @param mixed $value The value.
     *
     * @return boolean True if this spy returned the supplied value at least once.
     */
    public function returned($value);

    /**
     * Returns true if this spy always returned the supplied value.
     *
     * @param mixed $value The value.
     *
     * @return boolean True if this spy always returned the supplied value.
     */
    public function alwaysReturned($value);

    /**
     * Returns true if an exception of the supplied type was thrown at least
     * once.
     *
     * @param Exception|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return boolean                   True if a matching exception was thrown at least once.
     * @throws UndefinedSubjectException If there is no subject.
     */
    public function threw($type = null);

    /**
     * Returns true if an exception of the supplied type was always thrown.
     *
     * @param Exception|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return boolean                   True if a matching exception was always thrown.
     * @throws UndefinedSubjectException If there is no subject.
     */
    public function alwaysThrew($type = null);
}
