<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Cardinality\Verification;

use Eloquent\Phony\Cardinality\Cardinality;
use Eloquent\Phony\Cardinality\CardinalityInterface;
use Eloquent\Phony\Cardinality\Exception\InvalidCardinalityExceptionInterface;

/**
 * An abstract base class for implementing cardinality verifiers.
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
     * @return $this This verifier.
     */
    public function never()
    {
        return $this->times(0);
    }

    /**
     * Requires that the next verification matches only once.
     *
     * @return $this This verifier.
     */
    public function once()
    {
        return $this->times(1);
    }

    /**
     * Requires that the next verification matches exactly two times.
     *
     * @return $this This verifier.
     */
    public function twice()
    {
        return $this->times(2);
    }

    /**
     * Requires that the next verification matches exactly three times.
     *
     * @return $this This verifier.
     */
    public function thrice()
    {
        return $this->times(3);
    }

    /**
     * Requires that the next verification matches an exact number of times.
     *
     * @param integer $times The match count.
     *
     * @return $this This verifier.
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
     * @return $this This verifier.
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
     * @return $this This verifier.
     */
    public function atMost($maximum)
    {
        return $this->between(null, $maximum);
    }

    /**
     * Requires that the next verification matches a number of times greater
     * than or equal to $minimum, and less than or equal to $maximum.
     *
     * @param integer      $minimum The minimum match count.
     * @param integer|null $maximum The maximum match count, or null for no maximum.
     *
     * @return $this                                This verifier.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     */
    public function between($minimum, $maximum)
    {
        $this->cardinality = new Cardinality($minimum, $maximum);

        return $this;
    }

    /**
     * Requires that the next verification matches for all possible items.
     *
     * @return $this This verifier.
     */
    public function always()
    {
        $this->cardinality->setIsAlways(true);

        return $this;
    }

    /**
     * Reset the cardinality to its default value.
     *
     * @return CardinalityInterface The current cardinality.
     */
    public function resetCardinality()
    {
        $cardinality = $this->cardinality;
        $this->atLeast(1);

        return $cardinality;
    }

    /**
     * Get the cardinality.
     *
     * @return CardinalityInterface The cardinality.
     */
    public function cardinality()
    {
        return $this->cardinality;
    }

    protected $cardinality;
}
