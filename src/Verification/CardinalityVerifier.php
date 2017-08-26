<?php

declare(strict_types=1);

namespace Eloquent\Phony\Verification;

use Eloquent\Phony\Verification\Exception\InvalidCardinalityException;

/**
 * The interface implemented by cardinality verifiers.
 */
interface CardinalityVerifier
{
    /**
     * Requires that the next verification never matches.
     *
     * @return $this This verifier.
     */
    public function never(): self;

    /**
     * Requires that the next verification matches only once.
     *
     * @return $this This verifier.
     */
    public function once(): self;

    /**
     * Requires that the next verification matches exactly two times.
     *
     * @return $this This verifier.
     */
    public function twice(): self;

    /**
     * Requires that the next verification matches exactly three times.
     *
     * @return $this This verifier.
     */
    public function thrice(): self;

    /**
     * Requires that the next verification matches an exact number of times.
     *
     * @param int $times The match count.
     *
     * @return $this This verifier.
     */
    public function times(int $times): self;

    /**
     * Requires that the next verification matches a number of times greater
     * than or equal to $minimum.
     *
     * @param int $minimum The minimum match count.
     *
     * @return $this This verifier.
     */
    public function atLeast(int $minimum): self;

    /**
     * Requires that the next verification matches a number of times less than
     * or equal to $maximum.
     *
     * @param int $maximum The maximum match count.
     *
     * @return $this This verifier.
     */
    public function atMost(int $maximum): self;

    /**
     * Requires that the next verification matches a number of times greater
     * than or equal to $minimum, and less than or equal to $maximum.
     *
     * @param int $minimum The minimum match count.
     * @param int $maximum The maximum match count.
     *
     * @return $this                       This verifier.
     * @throws InvalidCardinalityException If the cardinality is invalid.
     */
    public function between(int $minimum, int $maximum): self;

    /**
     * Requires that the next verification matches for all possible items.
     *
     * @return $this This verifier.
     */
    public function always(): self;

    /**
     * Reset the cardinality to its default value.
     *
     * @return Cardinality The current cardinality.
     */
    public function resetCardinality(): Cardinality;

    /**
     * Get the cardinality.
     *
     * @return Cardinality The cardinality.
     */
    public function cardinality(): Cardinality;
}
