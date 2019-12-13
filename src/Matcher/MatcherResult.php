<?php

declare(strict_types=1);

namespace Eloquent\Phony\Matcher;

/**
 * Represents the result of matching arguments against matchers.
 */
class MatcherResult
{
    /**
     * Construct a new matcher result.
     *
     * @param bool            $isMatch         True if successful match.
     * @param array<int,bool> $matcherMatches  The matcher results.
     * @param array<int,bool> $argumentMatches The argument results.
     */
    public function __construct(
        bool $isMatch,
        array $matcherMatches,
        array $argumentMatches
    ) {
        $this->isMatch = $isMatch;
        $this->matcherMatches = $matcherMatches;
        $this->argumentMatches = $argumentMatches;
    }

    /**
     * @var bool
     */
    public $isMatch;

    /**
     * @var array<int,bool>
     */
    public $matcherMatches;

    /**
     * @var array<int,bool>
     */
    public $argumentMatches;
}
