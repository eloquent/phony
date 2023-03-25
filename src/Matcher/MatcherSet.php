<?php

declare(strict_types=1);

namespace Eloquent\Phony\Matcher;

/**
 * Represents a set of declared, variadic, and wildcard matchers.
 */
class MatcherSet
{
    /**
     * Construct a new matcher set.
     *
     * @param array<int,string>            $parameterNames       The parameter names.
     * @param array<int|string,int|string> $keyMap               The key map.
     * @param int                          $declaredCount        The number of declared parameters.
     * @param array<int,?Matcher>          $declaredMatchers     The declared matchers.
     * @param array<int|string,Matcher>    $variadicMatchers     The variadic matchers.
     * @param ?WildcardMatcher             $wildcardMatcher      The wildcard matcher, or null if there is no wildcard matcher.
     * @param ?Matcher                     $wildcardInnerMatcher The wildcard inner matcher, or null if there is no wildcard matcher.
     * @param int                          $wildcardMinimum      The wildcard minimum argument count.
     * @param int                          $wildcardMaximum      The wildcard maximum argument count, or a negative number if there is no maximum.
     */
    public function __construct(
        public array $parameterNames,
        public array $keyMap,
        public int $declaredCount,
        public array $declaredMatchers,
        public array $variadicMatchers,
        public ?WildcardMatcher $wildcardMatcher,
        public ?Matcher $wildcardInnerMatcher,
        public int $wildcardMinimum,
        public int $wildcardMaximum,
    ) {
    }

    /**
     * Returns true if this matcher set contains only an unbound wildcard
     * matcher that matches any arguments.
     */
    public function isUnboundWildcardAny(): bool
    {
        return [] === $this->declaredMatchers &&
        [] === $this->variadicMatchers &&
        $this->wildcardMatcher &&
        $this->wildcardInnerMatcher instanceof AnyMatcher &&
        0 === $this->wildcardMinimum &&
        $this->wildcardMaximum < 0;
    }
}
