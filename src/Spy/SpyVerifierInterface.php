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
use Eloquent\Phony\Call\Event\CallEventCollectionInterface;
use Eloquent\Phony\Call\Exception\UndefinedCallException;
use Eloquent\Phony\Cardinality\Verification\CardinalityVerifierInterface;
use Exception;
use InvalidArgumentException;

/**
 * The interface implemented by spy verifiers.
 */
interface SpyVerifierInterface extends SpyInterface,
    CardinalityVerifierInterface
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
     * Checks if called.
     *
     * @return CallEventCollectionInterface|null The result.
     */
    public function checkCalled();

    /**
     * Throws an exception unless called.
     *
     * @return CallEventCollectionInterface The result.
     * @throws Exception                    If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function called();

    /**
     * Checks if called with the supplied arguments.
     *
     * @param mixed $argument,... The arguments.
     *
     * @return CallEventCollectionInterface|null The result.
     */
    public function checkCalledWith();

    /**
     * Throws an exception unless called with the supplied arguments.
     *
     * @param mixed $argument,... The arguments.
     *
     * @return CallEventCollectionInterface The result.
     * @throws Exception                    If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function calledWith();

    /**
     * Checks if the $this value is the same as the supplied value.
     *
     * @param object|null $value The possible $this value.
     *
     * @return CallEventCollectionInterface|null The result.
     */
    public function checkCalledOn($value);

    /**
     * Throws an exception unless the $this value is the same as the supplied
     * value.
     *
     * @param object|null $value The possible $this value.
     *
     * @return CallEventCollectionInterface The result.
     * @throws Exception                    If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function calledOn($value);

    /**
     * Checks if this spy returned the supplied value.
     *
     * When called with no arguments, this method simply checks that the spy
     * returned any value.
     *
     * @param mixed $value The value.
     *
     * @return CallEventCollectionInterface|null The result.
     */
    public function checkReturned($value = null);

    /**
     * Throws an exception unless this spy returned the supplied value.
     *
     * When called with no arguments, this method simply checks that the spy
     * returned any value.
     *
     * @param mixed $value The value.
     *
     * @return CallEventCollectionInterface The result.
     * @throws Exception                    If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function returned($value = null);

    /**
     * Checks if an exception of the supplied type was thrown.
     *
     * @param Exception|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return CallEventCollectionInterface|null The result.
     * @throws InvalidArgumentException          If the type is invalid.
     */
    public function checkThrew($type = null);

    /**
     * Throws an exception unless an exception of the supplied type was thrown.
     *
     * @param Exception|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return CallEventCollectionInterface The result.
     * @throws InvalidArgumentException     If the type is invalid.
     * @throws Exception                    If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function threw($type = null);

    /**
     * Checks if this spy produced the supplied values.
     *
     * When called with no arguments, this method simply checks that the spy
     * produced any value.
     *
     * With a single argument, it checks that a value matching the argument was
     * produced.
     *
     * With two arguments, it checks that a key and value matching the
     * respective arguments were produced together.
     *
     * @param mixed $keyOrValue The key or value.
     * @param mixed $value      The value.
     *
     * @return CallEventCollectionInterface|null The result.
     */
    public function checkProduced($keyOrValue = null, $value = null);

    /**
     * Throws an exception unless this spy produced the supplied values.
     *
     * When called with no arguments, this method simply checks that the spy
     * produced any value.
     *
     * With a single argument, it checks that a value matching the argument was
     * produced.
     *
     * With two arguments, it checks that a key and value matching the
     * respective arguments were produced together.
     *
     * @param mixed $keyOrValue The key or value.
     * @param mixed $value      The value.
     *
     * @return CallEventCollectionInterface The result.
     * @throws Exception                    If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function produced($keyOrValue = null, $value = null);

    /**
     * Checks if this spy produced all of the supplied key-value pairs, in the
     * supplied order.
     *
     * @param mixed $pairs,... The key-value pairs.
     *
     * @return CallEventCollectionInterface|null The result.
     */
    public function checkProducedAll();

    /**
     * Throws an exception unless this spy produced all of the supplied
     * key-value pairs, in the supplied order.
     *
     * @param mixed $pairs,... The key-value pairs.
     *
     * @return CallEventCollectionInterface The result.
     * @throws Exception                    If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function producedAll();

    /**
     * Checks if this spy received the supplied value.
     *
     * When called with no arguments, this method simply checks that the spy
     * received any value.
     *
     * @param mixed $value The value.
     *
     * @return CallEventCollectionInterface|null The result.
     */
    public function checkReceived($value = null);

    /**
     * Throws an exception unless this spy received the supplied value.
     *
     * When called with no arguments, this method simply checks that the spy
     * received any value.
     *
     * @param mixed $value The value.
     *
     * @return CallEventCollectionInterface The result.
     * @throws Exception                    If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function received($value = null);

    /**
     * Checks if this spy received an exception of the supplied type.
     *
     * @param Exception|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return CallEventCollectionInterface|null The result.
     * @throws InvalidArgumentException          If the type is invalid.
     */
    public function checkReceivedException($type = null);

    /**
     * Throws an exception unless this spy received an exception of the
     * supplied type.
     *
     * @param Exception|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return CallEventCollectionInterface The result.
     * @throws InvalidArgumentException     If the type is invalid.
     * @throws Exception                    If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function receivedException($type = null);
}
