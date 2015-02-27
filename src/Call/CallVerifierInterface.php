<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call;

use Eloquent\Phony\Call\Event\CallEventCollectionInterface;
use Eloquent\Phony\Cardinality\Exception\InvalidCardinalityExceptionInterface;
use Eloquent\Phony\Cardinality\Verification\CardinalityVerifierInterface;
use Exception;
use InvalidArgumentException;

/**
 * The interface implemented by call verifiers.
 */
interface CallVerifierInterface extends CallInterface,
    CardinalityVerifierInterface
{
    /**
     * Get the call duration.
     *
     * @return float|null The call duration in seconds, or null if the call has not yet completed.
     */
    public function duration();

    /**
     * Get the call response duration.
     *
     * @return float|null The call response duration in seconds, or null if the call has not yet responded.
     */
    public function responseDuration();

    /**
     * Get the number of arguments.
     *
     * @return integer The number of arguments.
     */
    public function argumentCount();

    /**
     * Checks if called with the supplied arguments.
     *
     * @param mixed $argument,... The arguments.
     *
     * @return CallEventCollectionInterface|null    The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     */
    public function checkCalledWith();

    /**
     * Throws an exception unless called with the supplied arguments.
     *
     * @param mixed $argument,... The arguments.
     *
     * @return CallEventCollectionInterface         The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     * @throws Exception                            If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function calledWith();

    /**
     * Checks if the $this value is equal to the supplied value.
     *
     * @param object|null $value The possible $this value.
     *
     * @return CallEventCollectionInterface|null    The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     */
    public function checkCalledOn($value);

    /**
     * Throws an exception unless the $this value is equal to the supplied
     * value.
     *
     * @param object|null $value The possible $this value.
     *
     * @return CallEventCollectionInterface         The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     * @throws Exception                            If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function calledOn($value);

    /**
     * Checks if this call returned the supplied value.
     *
     * When called with no arguments, this method simply checks that the call
     * returned any value.
     *
     * @param mixed $value The value.
     *
     * @return CallEventCollectionInterface|null    The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     */
    public function checkReturned($value = null);

    /**
     * Throws an exception unless this call returned the supplied value.
     *
     * When called with no arguments, this method simply checks that the call
     * returned any value.
     *
     * @param mixed $value The value.
     *
     * @return CallEventCollectionInterface         The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     * @throws Exception                            If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function returned($value = null);

    /**
     * Checks if an exception of the supplied type was thrown.
     *
     * @param Exception|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return CallEventCollectionInterface|null    The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     * @throws InvalidArgumentException             If the type is invalid.
     */
    public function checkThrew($type = null);

    /**
     * Throws an exception unless this call threw an exception of the supplied
     * type.
     *
     * @param Exception|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return CallEventCollectionInterface         The result.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     * @throws InvalidArgumentException             If the type is invalid.
     * @throws Exception                            If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function threw($type = null);

    /**
     * Checks if this call produced the supplied values.
     *
     * When called with no arguments, this method simply checks that the call
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
     * Throws an exception unless this call produced the supplied values.
     *
     * When called with no arguments, this method simply checks that the call
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
     * Checks if this call produced all of the supplied key-value pairs, in the
     * supplied order.
     *
     * @param mixed $pairs,... The key-value pairs.
     *
     * @return CallEventCollectionInterface|null The result.
     */
    public function checkProducedAll();

    /**
     * Throws an exception unless this call produced all of the supplied
     * key-value pairs, in the supplied order.
     *
     * @param mixed $pairs,... The key-value pairs.
     *
     * @return CallEventCollectionInterface The result.
     * @throws Exception                    If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function producedAll();

    /**
     * Checks if this call received the supplied value.
     *
     * When called with no arguments, this method simply checks that the call
     * received any value.
     *
     * @param mixed $value The value.
     *
     * @return CallEventCollectionInterface|null The result.
     */
    public function checkReceived($value = null);

    /**
     * Throws an exception unless this call received the supplied value.
     *
     * When called with no arguments, this method simply checks that the call
     * received any value.
     *
     * @param mixed $value The value.
     *
     * @return CallEventCollectionInterface The result.
     * @throws Exception                    If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function received($value = null);

    /**
     * Checks if this call received an exception of the supplied type.
     *
     * @param Exception|string|null $type An exception to match, the type of exception, or null for any exception.
     *
     * @return CallEventCollectionInterface|null The result.
     * @throws InvalidArgumentException          If the type is invalid.
     */
    public function checkReceivedException($type = null);

    /**
     * Throws an exception unless this call received an exception of the
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
