<?php

declare(strict_types=1);

namespace Eloquent\Phony\Verification;

use Eloquent\Phony\Verification\Exception\InvalidCardinalityException;
use Eloquent\Phony\Verification\Exception\InvalidCardinalityStateException;
use Eloquent\Phony\Verification\Exception\InvalidSingularCardinalityException;

/**
 * Represents the cardinality of a verification.
 */
class Cardinality
{
    /**
     * Construct a new cardinality.
     *
     * Negative values for $maximum are treated as "no maximum".
     *
     * @param int  $minimum  The minimum.
     * @param int  $maximum  The maximum.
     * @param bool $isAlways True if 'always' should be enabled.
     *
     * @throws InvalidCardinalityException If the cardinality is invalid.
     */
    public function __construct(
        int $minimum = 1,
        int $maximum = -1,
        bool $isAlways = false
    ) {
        if ($minimum < 0) {
            throw new InvalidCardinalityStateException();
        }

        if ($maximum >= 0 && $minimum > $maximum) {
            throw new InvalidCardinalityStateException();
        }

        if ($maximum < 0 && !$minimum) {
            throw new InvalidCardinalityStateException();
        }

        $this->minimum = $minimum;
        $this->maximum = $maximum;
        $this->setIsAlways($isAlways);
    }

    /**
     * Get the minimum.
     *
     * @return int The minimum.
     */
    public function minimum(): int
    {
        return $this->minimum;
    }

    /**
     * Get the maximum.
     *
     * @return int The maximum.
     */
    public function maximum(): int
    {
        return $this->maximum;
    }

    /**
     * Returns true if this cardinality is 'never'.
     *
     * @return bool True if this cardinality is 'never'.
     */
    public function isNever(): bool
    {
        return 0 === $this->maximum;
    }

    /**
     * Turn 'always' on or off.
     *
     * @param bool $isAlways True to enable 'always'.
     *
     * @throws InvalidCardinalityException If the cardinality is invalid.
     */
    public function setIsAlways(bool $isAlways): void
    {
        if ($isAlways && $this->isNever()) {
            throw new InvalidCardinalityStateException();
        }

        $this->isAlways = $isAlways;
    }

    /**
     * Returns true if 'always' is enabled.
     *
     * @return bool True if 'always' is enabled.
     */
    public function isAlways(): bool
    {
        return $this->isAlways;
    }

    /**
     * Returns true if the supplied count matches this cardinality.
     *
     * @param int|bool $count        The count or result to check.
     * @param int      $maximumCount The maximum possible count.
     *
     * @return bool True if the supplied count matches this cardinality.
     */
    public function matches($count, int $maximumCount): bool
    {
        $count = intval($count);
        $result = true;

        if ($count < $this->minimum) {
            $result = false;
        }

        if ($this->maximum >= 0 && $count > $this->maximum) {
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
     * @return $this                       This cardinality.
     * @throws InvalidCardinalityException If the cardinality is invalid.
     */
    public function assertSingular(): self
    {
        if ($this->minimum > 1 || $this->maximum > 1 || $this->isAlways) {
            throw new InvalidSingularCardinalityException($this);
        }

        return $this;
    }

    /**
     * @var int
     */
    private $minimum;

    /**
     * @var int
     */
    private $maximum;

    /**
     * @var bool
     */
    private $isAlways;
}
