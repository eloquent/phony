<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Verification\Cardinality;

use Eloquent\Phony\Verification\Exception\InvalidCardinalityExceptionInterface;

/**
 * The interface implemented by cardinalities.
 */
interface CardinalityInterface
{
    /**
     * Get the minimum.
     *
     * @return integer|null The minimum.
     */
    public function minimum();

    /**
     * Get the maximum.
     *
     * @return integer|null The maximum.
     */
    public function maximum();

    /**
     * Returns true if this cardinality is 'never'.
     *
     * @return boolean True if this cardinality is 'never'.
     */
    public function isNever();

    /**
     * Turn 'always' on or off.
     *
     * @param  boolean                              $isAlways True to enable 'always'.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     */
    public function setIsAlways($isAlways);

    /**
     * Returns true if 'always' is enabled.
     *
     * @return boolean True if 'always' is enabled.
     */
    public function isAlways();

    /**
     * Returns true if the supplied count matches this cardinality.
     *
     * @param integer|boolean $count        The count or result to check.
     * @param integer|null    $maximumCount The maximum possible count, defaults to 1.
     *
     * @return boolean True if the supplied count matches this cardinality.
     */
    public function matches($count, $maximumCount = null);

    /**
     * Asserts that this cardinality is suitable for events that can only happen
     * once or not at all.
     *
     * @return CardinalityInterface                 This cardinality.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     */
    public function assertSingluar();
}
