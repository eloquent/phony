<?php

declare(strict_types=1);

namespace Eloquent\Phony\Matcher\Verification;

/**
 * Represents an individual matcher result.
 */
class MatcherResult
{
    /**
     * Construct a new matcher result.
     *
     * @param int|string|null $matcherKey  The matcher key, or null if no matcher exists.
     * @param int|string|null $argumentKey The argument key, or null if no argument exists.
     * @param bool            $isMatch     True if this is a match.
     * @param bool            $isWildMatch True if this is a wild match.
     */
    public function __construct(
        public int|string|null $matcherKey,
        public int|string|null $argumentKey,
        public bool $isMatch,
        public bool $isWildMatch,
    ) {
    }
}
