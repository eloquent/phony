<?php

declare(strict_types=1);

namespace Eloquent\Phony\Matcher\Verification;

/**
 * Represents the result of matching arguments against matchers.
 */
class MatcherResultSet
{
    /**
     * Construct a new matcher result set.
     *
     * @param bool                     $isMatch         True if the overall result is a match.
     * @param array<int,MatcherResult> $declaredResults The declared results.
     * @param array<int,MatcherResult> $variadicResults The variadic results.
     * @param ?WildcardMatcherResult   $wildcardResult  The wildcard result, or null if there is no wildcard matcher.
     */
    public function __construct(
        public bool $isMatch,
        public array $declaredResults,
        public array $variadicResults,
        public ?WildcardMatcherResult $wildcardResult,
    ) {
    }
}
