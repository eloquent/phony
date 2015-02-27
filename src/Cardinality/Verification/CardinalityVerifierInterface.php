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

use Eloquent\Phony\Cardinality\CardinalityInterface;
use Eloquent\Phony\Cardinality\Exception\InvalidCardinalityExceptionInterface;

/**
 * The interface implemented by cardinality verifiers.
 */
interface CardinalityVerifierInterface
{
    /**
     * Requires that the next verification never matches.
     *
     * @return CardinalityVerifierInterface This verifier.
     */
    public function never();

    /**
     * Requires that the next verification matches only once.
     *
     * @return CardinalityVerifierInterface This verifier.
     */
    public function once();

    /**
     * Requires that the next verification matches exactly two times.
     *
     * @return CardinalityVerifierInterface This verifier.
     */
    public function twice();

    /**
     * Requires that the next verification matches exactly three times.
     *
     * @return CardinalityVerifierInterface This verifier.
     */
    public function thrice();

    /**
     * Requires that the next verification matches an exact number of times.
     *
     * @param integer|null $times The match count, or null to remove all cardinality requirements.
     *
     * @return CardinalityVerifierInterface This verifier.
     */
    public function times($times);

    /**
     * Requires that the next verification matches a number of times greater
     * than or equal to $minimum.
     *
     * @param integer $minimum The minimum match count.
     *
     * @return CardinalityVerifierInterface This verifier.
     */
    public function atLeast($minimum);

    /**
     * Requires that the next verification matches a number of times less than
     * or equal to $maximum.
     *
     * @param integer $maximum The maximum match count.
     *
     * @return CardinalityVerifierInterface This verifier.
     */
    public function atMost($maximum);

    /**
     * Requires that the next verification matches a number of times greater
     * than or equal to $minimum, and less than or equal to $maximum.
     *
     * @param integer|null $minimum The minimum match count, or null for no minimum.
     * @param integer|null $maximum The maximum match count, or null for no maximum.
     *
     * @return CardinalityVerifierInterface         This verifier.
     * @throws InvalidCardinalityExceptionInterface If the cardinality is invalid.
     */
    public function between($minimum, $maximum);

    /**
     * Requires that the next verification matches for all possible items.
     *
     * @return CardinalityVerifierInterface This verifier.
     */
    public function always();

    /**
     * Reset the cardinality to its default value.
     *
     * @return CardinalityInterface The current cardinality.
     */
    public function resetCardinality();

    /**
     * Get the cardinality.
     *
     * @return CardinalityInterface The cardinality.
     */
    public function cardinality();
}
