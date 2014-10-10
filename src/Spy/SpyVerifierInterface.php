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
use Eloquent\Phony\Event\EventCollectionInterface;
use Exception;

/**
 * The interface implemented by spy verifiers.
 */
interface SpyVerifierInterface extends SpyInterface
{
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
     * Checks if called at least once.
     *
     * @return EventCollectionInterface|null The result.
     */
    public function called();

    /**
     * Throws an exception unless called at least once.
     *
     * @return EventCollectionInterface The result.
     * @throws Exception                If the assertion fails.
     */
    public function assertCalled();

    /**
     * Checks if called only once.
     *
     * @return EventCollectionInterface|null The result.
     */
    public function calledOnce();

    /**
     * Throws an exception unless called only once.
     *
     * @return EventCollectionInterface The result.
     * @throws Exception                If the assertion fails.
     */
    public function assertCalledOnce();

    /**
     * Checks if called an exact amount of times.
     *
     * @param integer $times The expected number of calls.
     *
     * @return EventCollectionInterface|null The result.
     */
    public function calledTimes($times);

    /**
     * Throws an exception unless called an exact amount of times.
     *
     * @param integer $times The expected number of calls.
     *
     * @return EventCollectionInterface The result.
     * @throws Exception                If the assertion fails.
     */
    public function assertCalledTimes($times);

    /**
     * Checks if this spy was called before the supplied spy.
     *
     * @param SpyInterface $spy Another spy.
     *
     * @return EventCollectionInterface|null The result.
     */
    public function calledBefore(SpyInterface $spy);

    /**
     * Throws an exception unless this spy was called before the supplied spy.
     *
     * @param SpyInterface $spy Another spy.
     *
     * @return EventCollectionInterface The result.
     * @throws Exception                If the assertion fails.
     */
    public function assertCalledBefore(SpyInterface $spy);

    /**
     * Checks if this spy was called after the supplied spy.
     *
     * @param SpyInterface $spy Another spy.
     *
     * @return EventCollectionInterface|null The result.
     */
    public function calledAfter(SpyInterface $spy);

    /**
     * Throws an exception unless this spy was called after the supplied spy.
     *
     * @param SpyInterface $spy Another spy.
     *
     * @return EventCollectionInterface The result.
     * @throws Exception                If the assertion fails.
     */
    public function assertCalledAfter(SpyInterface $spy);

    /**
     * Checks if called with the supplied arguments (and possibly others) at
     * least once.
     *
     * @param mixed $argument,... The arguments.
     *
     * @return EventCollectionInterface|null The result.
     */
    public function calledWith();

    /**
     * Throws an exception unless called with the supplied arguments (and
     * possibly others) at least once.
     *
     * @param mixed $argument,... The arguments.
     *
     * @return EventCollectionInterface The result.
     * @throws Exception                If the assertion fails.
     */
    public function assertCalledWith();

    /**
     * Checks if called only once with the supplied arguments (and possibly
     * others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @return EventCollectionInterface|null The result.
     */
    public function calledOnceWith();

    /**
     * Throws an exception unless called only once with the supplied arguments
     * (and possibly others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @return EventCollectionInterface The result.
     * @throws Exception                If the assertion fails.
     */
    public function assertCalledOnceWith();

    /**
     * Checks if called an exact amount of times with the supplied arguments
     * (and possibly others).
     *
     * @param integer $times        The expected number of calls.
     * @param mixed   $argument,... The arguments.
     *
     * @return EventCollectionInterface|null The result.
     */
    public function calledTimesWith($times);

    /**
     * Throws an exception unless called an exact amount of times with the
     * supplied arguments (and possibly others).
     *
     * @param integer $times        The expected number of calls.
     * @param mixed   $argument,... The arguments.
     *
     * @return EventCollectionInterface The result.
     * @throws Exception                If the assertion fails.
     */
    public function assertCalledTimesWith($times);

    /**
     * Checks if always called with the supplied arguments (and possibly
     * others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @return EventCollectionInterface|null The result.
     */
    public function alwaysCalledWith();

    /**
     * Throws an exception unless always called with the supplied arguments (and
     * possibly others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @return EventCollectionInterface The result.
     * @throws Exception                If the assertion fails.
     */
    public function assertAlwaysCalledWith();

    /**
     * Checks if called with the supplied arguments (and no others) at least
     * once.
     *
     * @param mixed $argument,... The arguments.
     *
     * @return EventCollectionInterface|null The result.
     */
    public function calledWithExactly();

    /**
     * Throws an exception unless called with the supplied arguments (and no
     * others) at least once.
     *
     * @param mixed $argument,... The arguments.
     *
     * @return EventCollectionInterface The result.
     * @throws Exception                If the assertion fails.
     */
    public function assertCalledWithExactly();

    /**
     * Checks if called only once with the supplied arguments (and no others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @return EventCollectionInterface|null The result.
     */
    public function calledOnceWithExactly();

    /**
     * Throws an exception unless called only once with the supplied arguments
     * (and no others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @return EventCollectionInterface The result.
     * @throws Exception                If the assertion fails.
     */
    public function assertCalledOnceWithExactly();

    /**
     * Checks if called an exact amount of times with the supplied arguments
     * (and no others).
     *
     * @param integer $times        The expected number of calls.
     * @param mixed   $argument,... The arguments.
     *
     * @return EventCollectionInterface|null The result.
     */
    public function calledTimesWithExactly($times);

    /**
     * Throws an exception unless called an exact amount of times with the
     * supplied arguments (and no others).
     *
     * @param integer $times        The expected number of calls.
     * @param mixed   $argument,... The arguments.
     *
     * @return EventCollectionInterface The result.
     * @throws Exception                If the assertion fails.
     */
    public function assertCalledTimesWithExactly($times);

    /**
     * Checks if always called with the supplied arguments (and no others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @return EventCollectionInterface|null The result.
     */
    public function alwaysCalledWithExactly();

    /**
     * Throws an exception unless always called with the supplied arguments (and
     * no others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @return EventCollectionInterface The result.
     * @throws Exception                If the assertion fails.
     */
    public function assertAlwaysCalledWithExactly();

    /**
     * Checks if never called with the supplied arguments (and possibly others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @return EventCollectionInterface|null The result.
     */
    public function neverCalledWith();

    /**
     * Throws an exception unless never called with the supplied arguments (and
     * possibly others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @return EventCollectionInterface The result.
     * @throws Exception                If the assertion fails.
     */
    public function assertNeverCalledWith();

    /**
     * Checks if never called with the supplied arguments (and no others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @return EventCollectionInterface|null The result.
     */
    public function neverCalledWithExactly();

    /**
     * Throws an exception unless never called with the supplied arguments (and
     * no others).
     *
     * @param mixed $argument,... The arguments.
     *
     * @return EventCollectionInterface The result.
     * @throws Exception                If the assertion fails.
     */
    public function assertNeverCalledWithExactly();

    /**
     * Checks if the $this value is the same as the supplied value for at least
     * one call.
     *
     * @param object|null $value The possible $this value.
     *
     * @return EventCollectionInterface|null The result.
     */
    public function calledOn($value);

    /**
     * Throws an exception unless the $this value is the same as the supplied
     * value for at least one call.
     *
     * @param object|null $value The possible $this value.
     *
     * @return EventCollectionInterface The result.
     * @throws Exception                If the assertion fails.
     */
    public function assertCalledOn($value);

    /**
     * Checks if the $this value is the same as the supplied value for all
     * calls.
     *
     * @param object|null $value The possible $this value.
     *
     * @return EventCollectionInterface|null The result.
     */
    public function alwaysCalledOn($value);

    /**
     * Throws an exception unless the $this value is the same as the supplied
     * value for all calls.
     *
     * @param object|null $value The possible $this value.
     *
     * @return EventCollectionInterface The result.
     * @throws Exception                If the assertion fails.
     */
    public function assertAlwaysCalledOn($value);

    /**
     * Checks if this spy returned the supplied value at least once.
     *
     * @param mixed $value The value.
     *
     * @return EventCollectionInterface|null The result.
     */
    public function returned($value = null);

    /**
     * Throws an exception unless this spy returned the supplied value at least
     * once.
     *
     * @param mixed $value The value.
     *
     * @return EventCollectionInterface The result.
     * @throws Exception                If the assertion fails.
     */
    public function assertReturned($value = null);

    /**
     * Checks if this spy always returned the supplied value.
     *
     * @param mixed $value The value.
     *
     * @return EventCollectionInterface|null The result.
     */
    public function alwaysReturned($value = null);

    /**
     * Throws an exception unless this spy always returned the supplied value.
     *
     * @param mixed $value The value.
     *
     * @return EventCollectionInterface The result.
     * @throws Exception                If the assertion fails.
     */
    public function assertAlwaysReturned($value = null);

    /**
     * Checks if an exception of the supplied type was thrown at least once.
     *
     * @param Exception|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return EventCollectionInterface|null The result.
     */
    public function threw($type = null);

    /**
     * Throws an exception unless an exception of the supplied type was thrown
     * at least once.
     *
     * @param Exception|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return EventCollectionInterface The result.
     * @throws Exception                If the assertion fails.
     */
    public function assertThrew($type = null);

    /**
     * Checks if an exception of the supplied type was always thrown.
     *
     * @param Exception|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return EventCollectionInterface|null The result.
     */
    public function alwaysThrew($type = null);

    /**
     * Throws an exception unless an exception of the supplied type was always
     * thrown.
     *
     * @param Exception|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return EventCollectionInterface The result.
     * @throws Exception                If the assertion fails.
     */
    public function assertAlwaysThrew($type = null);
}
