<?php

declare(strict_types=1);

namespace Eloquent\Phony\Matcher\Verification;

/**
 * Represents a wildcard matcher result.
 */
class WildcardMatcherResult
{
    /**
     * Construct a new wildcard matcher result.
     *
     * @param int  $delta   The delta between desired and actual match counts.
     * @param bool $isMatch True if this is a match.
     */
    public function __construct(
        public int $delta,
        public bool $isMatch,
    ) {
    }
}
