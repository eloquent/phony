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
     * @return CardinalityVerifierInterface This verifier.
     */
    public function between($minimum, $maximum);

    /**
     * Get the cardinality.
     *
     * @return tuple<integer|null,integer|null> The cardinality.
     */
    public function cardinality();
}
