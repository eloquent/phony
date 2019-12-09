<?php

declare(strict_types=1);

namespace Eloquent\Phony\Verification;

use Eloquent\Phony\Verification\Exception\InvalidCardinalityException;

/**
 * Used for implementing cardinality verifiers.
 */
trait CardinalityVerifierTrait
{
    /**
     * Requires that the next verification never matches.
     *
     * @return $this This verifier.
     */
    public function never(): CardinalityVerifier
    {
        $this->cardinality = new Cardinality(0, 0);

        return $this;
    }

    /**
     * Requires that the next verification matches only once.
     *
     * @return $this This verifier.
     */
    public function once(): CardinalityVerifier
    {
        $this->cardinality = new Cardinality(1, 1);

        return $this;
    }

    /**
     * Requires that the next verification matches exactly two times.
     *
     * @return $this This verifier.
     */
    public function twice(): CardinalityVerifier
    {
        $this->cardinality = new Cardinality(2, 2);

        return $this;
    }

    /**
     * Requires that the next verification matches exactly three times.
     *
     * @return $this This verifier.
     */
    public function thrice(): CardinalityVerifier
    {
        $this->cardinality = new Cardinality(3, 3);

        return $this;
    }

    /**
     * Requires that the next verification matches an exact number of times.
     *
     * @param int $times The match count.
     *
     * @return $this This verifier.
     */
    public function times(int $times): CardinalityVerifier
    {
        $this->cardinality = new Cardinality($times, $times);

        return $this;
    }

    /**
     * Requires that the next verification matches a number of times greater
     * than or equal to $minimum.
     *
     * @param int $minimum The minimum match count.
     *
     * @return $this This verifier.
     */
    public function atLeast(int $minimum): CardinalityVerifier
    {
        $this->cardinality = new Cardinality($minimum, -1);

        return $this;
    }

    /**
     * Requires that the next verification matches a number of times less than
     * or equal to $maximum.
     *
     * @param int $maximum The maximum match count.
     *
     * @return $this This verifier.
     */
    public function atMost(int $maximum): CardinalityVerifier
    {
        $this->cardinality = new Cardinality(0, $maximum);

        return $this;
    }

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
    public function between(int $minimum, int $maximum): CardinalityVerifier
    {
        $this->cardinality = new Cardinality($minimum, $maximum);

        return $this;
    }

    /**
     * Requires that the next verification matches for all possible items.
     *
     * @return $this This verifier.
     */
    public function always(): CardinalityVerifier
    {
        $this->cardinality->setIsAlways(true);

        return $this;
    }

    /**
     * Reset the cardinality to its default value.
     *
     * @return Cardinality The current cardinality.
     */
    public function resetCardinality(): Cardinality
    {
        $cardinality = $this->cardinality;
        $this->cardinality = new Cardinality();

        return $cardinality;
    }

    /**
     * Get the cardinality.
     *
     * @return Cardinality The cardinality.
     */
    public function cardinality(): Cardinality
    {
        return $this->cardinality;
    }

    /**
     * @var Cardinality
     */
    protected $cardinality;
}
