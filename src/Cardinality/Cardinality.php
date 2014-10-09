<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Cardinality;

use Eloquent\Phony\Cardinality\Exception\InvalidCardinalityException;
use Eloquent\Phony\Cardinality\Exception\InvalidCardinalityExceptionInterface;
use Eloquent\Phony\Cardinality\Exception\InvalidSingularCardinalityException;

/**
 * The interface implemented by cardinalities.
 */
class Cardinality implements CardinalityInterface
{
    /**
     * Construct a new cardinality.
     *
     * @param integer|null $minimum  The minimum, or null for no minimum.
     * @param integer|null $maximum  The maximum, or null for no maximum.
     * @param boolean|null $isAlways True if 'always' should be enabled.
     *
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     */
    public function __construct(
        $minimum = null,
        $maximum = null,
        $isAlways = null
    ) {
        if (null === $minimum) {
            $minimum = 0;
        }

        if ($minimum < 0 || $maximum < 0) {
            throw new InvalidCardinalityException();
        }

        if (null !== $maximum && $minimum > $maximum) {
            throw new InvalidCardinalityException();
        }

        if (null === $isAlways) {
            $isAlways = false;
        }

        $this->minimum = $minimum;
        $this->maximum = $maximum;
        $this->setIsAlways($isAlways);
    }

    /**
     * Get the minimum.
     *
     * @return integer|null The minimum.
     */
    public function minimum()
    {
        return $this->minimum;
    }

    /**
     * Get the maximum.
     *
     * @return integer|null The maximum.
     */
    public function maximum()
    {
        return $this->maximum;
    }

    /**
     * Returns true if this cardinality is 'never'.
     *
     * @return boolean True if this cardinality is 'never'.
     */
    public function isNever()
    {
        return 0 === $this->maximum;
    }

    /**
     * Turn 'always' on or off.
     *
     * @param  boolean                              $isAlways True to enable 'always'.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     */
    public function setIsAlways($isAlways)
    {
        if ($isAlways && $this->isNever()) {
            throw new InvalidCardinalityException();
        }

        $this->isAlways = $isAlways;
    }

    /**
     * Returns true if 'always' is enabled.
     *
     * @return boolean True if 'always' is enabled.
     */
    public function isAlways()
    {
        return $this->isAlways;
    }

    /**
     * Returns true if the supplied count matches this cardinality.
     *
     * @param integer|boolean $count        The count or result to check.
     * @param integer|null    $maximumCount The maximum possible count, defaults to 1.
     *
     * @return boolean True if the supplied count matches this cardinality.
     */
    public function matches($count, $maximumCount = null)
    {
        if (null === $maximumCount) {
            $maximumCount = 1;
        }

        $count = intval($count);
        $result = true;

        if ($count < $this->minimum) {
            $result = false;
        }

        if (null !== $this->maximum && $count > $this->maximum) {
            $result = false;
        }

        if ($this->isAlways && $count < $maximumCount) {
            $result = false;
        }

        return $result;
    }

    /**
     * Asserts that this cardinality is suitable for events that can only happen
     * once or not at all.
     *
     * @return CardinalityInterface                 This cardinality.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     */
    public function assertSingular()
    {
        if ($this->minimum > 1 || $this->maximum > 1) {
            throw new InvalidSingularCardinalityException($this);
        }

        return $this;
    }

    private $minimum;
    private $maximum;
    private $isAlways;
}
