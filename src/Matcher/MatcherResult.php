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
     * @param bool                   $isMatch         True if successful match.
     * @param array<int|string,bool> $matcherMatches  The matcher results.
     * @param array<int|string,bool> $argumentMatches The argument results.
     * @param array<int,string>      $parameterNames  The parameter names.
     */
    public function __construct(
        bool $isMatch,
        array $matcherMatches,
        array $argumentMatches,
        array $parameterNames
    ) {
        $this->isMatch = $isMatch;
        $this->matcherMatches = $matcherMatches;
        $this->argumentMatches = $argumentMatches;
        $this->parameterNames = $parameterNames;
    }

    /**
     * @var bool
     */
    public $isMatch;

    /**
     * @var array<int|string,bool>
     */
    public $matcherMatches;

    /**
     * @var array<int|string,bool>
     */
    public $argumentMatches;

    /**
     * @var array<int,string>
     */
    public $parameterNames;
}
