<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Verification;

use Eloquent\Phony\Verification\Exception\InvalidCardinalityExceptionInterface;
use Eloquent\Phony\Verification\Exception\InvalidSingularCardinalityException;

/**
 * An abstract base class for implementing cardinality verifiers.
 *
 * @internal
 */
abstract class AbstractCardinalityVerifier implements
    CardinalityVerifierInterface
{
    /**
     * Construct a new cardinality verifier.
     */
    public function __construct()
    {
        $this->resetCardinality();
    }

    /**
     * Requires that the next verification never matches.
     *
     * @return CardinalityVerifierInterface This verifier.
     */
    public function never()
    {
        return $this->times(0);
    }

    /**
     * Requires that the next verification matches only once.
     *
     * @return CardinalityVerifierInterface This verifier.
     */
    public function once()
    {
        return $this->times(1);
    }

    /**
     * Requires that the next verification matches an exact number of times.
     *
     * @param integer|null $times The match count, or null to remove all cardinality requirements.
     *
     * @return CardinalityVerifierInterface This verifier.
     */
    public function times($times)
    {
        return $this->between($times, $times);
    }

    /**
     * Requires that the next verification matches a number of times greater
     * than or equal to $minimum.
     *
     * @param integer $minimum The minimum match count.
     *
     * @return CardinalityVerifierInterface This verifier.
     */
    public function atLeast($minimum)
    {
        return $this->between($minimum, null);
    }

    /**
     * Requires that the next verification matches a number of times less than
     * or equal to $maximum.
     *
     * @param integer $maximum The maximum match count.
     *
     * @return CardinalityVerifierInterface This verifier.
     */
    public function atMost($maximum)
    {
        return $this->between(null, $maximum);
    }

    /**
     * Requires that the next verification matches a number of times greater
     * than or equal to $minimum, and less than or equal to $maximum.
     *
     * @param integer|null $minimum The minimum match count, or null for no minimum.
     * @param integer|null $maximum The maximum match count, or null for no maximum.
     *
     * @return CardinalityVerifierInterface This verifier.
     */
    public function between($minimum, $maximum)
    {
        if (null !== $minimum && null !== $maximum && $minimum > $maximum) {
            $temp = $minimum;
            $minimum = $maximum;
            $maximum = $temp;
        }

        $this->cardinality = array($minimum, $maximum);

        return $this;
    }

    /**
     * Get the cardinality.
     *
     * @return tuple<integer|null,integer|null> The cardinality.
     */
    public function cardinality()
    {
        return $this->cardinality;
    }

    /**
     * Returns true if the cardinality is 'never'.
     *
     * @param tuple<integer|null,integer|null>|null $cardinality The cardinality, or null to check the current cardinality.
     *
     * @return boolean True if the cardinality is 'never'.
     */
    protected function cardinalityIsNever(array $cardinality = null)
    {
        if (null === $cardinality) {
            $cardinality = $this->cardinality;
        }

        return 0 === $cardinality[1];
    }

    /**
     * Get the cardinality, and assert that it is suitable for events that can
     * only happen once or not at all.
     *
     * @param tuple<integer|null,integer|null>|null $cardinality The cardinality, or null to check the current cardinality.
     *
     * @return tuple<integer|null,integer|null>     The current cardinality.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     */
    protected function singularCardinality(array $cardinality = null)
    {
        if (null === $cardinality) {
            $cardinality = $this->cardinality();
        }

        if ($cardinality[0] > 1) {
            throw new InvalidSingularCardinalityException($cardinality);
        }

        return $cardinality;
    }

    /**
     * Reset the cardinality to its default value.
     *
     * @return tuple<integer|null,integer|null> The current cardinality.
     */
    protected function resetCardinality()
    {
        $cardinality = $this->cardinality;
        $this->atLeast(1);

        return $cardinality;
    }

    /**
     * Reset the cardinality to its default value, and assert that it is
     * suitable for events that can only happen once or not at all.
     *
     * @return tuple<integer|null,integer|null>     The current cardinality.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     */
    protected function resetSingularCardinality()
    {
        $cardinality = $this->singularCardinality();
        $this->resetCardinality();

        return $cardinality;
    }

    /**
     * Returns true if the supplied match count matches the supplied
     * cardinality.
     *
     * @param integer                               $matchCount  The match count.
     * @param tuple<integer|null,integer|null>|null $cardinality The cardinality, or null to use and reset the current cardinality.
     *
     * @return boolean True if the supplied match count matches the cardinality.
     */
    protected function matchesCardinality(
        $matchCount,
        array $cardinality = null
    ) {
        if (null === $cardinality) {
            $cardinality = $this->resetCardinality();
        }

        list($minimum, $maximum) = $cardinality;
        $result = true;

        if (null !== $minimum && $matchCount < $minimum) {
            $result = false;
        }

        if (null !== $maximum && $matchCount > $maximum) {
            $result = false;
        }

        return $result;
    }

    /**
     * Returns true if the supplied match count matches the supplied
     * cardinality, and asserts that the cardinality is suitable for events that
     * can only happen once or not at all.
     *
     * @param integer|boolean                       $matchCount  The match count.
     * @param tuple<integer|null,integer|null>|null $cardinality The cardinality, or null to use and reset the current cardinality.
     *
     * @return boolean                              True if the supplied match count matches the cardinality.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     */
    protected function matchesSingularCardinality(
        $matchCount,
        array $cardinality = null
    ) {
        if ($matchCount) {
            $matchCount = 1;
        } else {
            $matchCount = 0;
        }

        if (null === $cardinality) {
            $cardinality = $this->resetSingularCardinality();
        } else {
            $this->singularCardinality($cardinality);
        }

        return $this->matchesCardinality($matchCount, $cardinality);
    }

    protected $cardinality;
}
