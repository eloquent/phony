<?php

declare(strict_types=1);

namespace Eloquent\Phony\Matcher;

use InvalidArgumentException;

/**
 * Verifies argument lists against matcher lists.
 */
class MatcherVerifier
{
    /**
     * Verify that the supplied arguments match the supplied matchers.
     *
     * @param array<int|string,Matcher> $matchers       The matchers.
     * @param array<int,string>         $parameterNames The parameter names.
     * @param array<int|string,mixed>   $arguments      The arguments.
     *
     * @return bool                     True if the arguments match.
     * @throws InvalidArgumentException If a wildcard matcher precedes any other matcher.
     */
    public function matches(
        array $matchers,
        array $parameterNames,
        array $arguments,
    ): bool {
        $keyMap = [];

        foreach ($parameterNames as $position => $name) {
            $keyMap[$position] = $name;
            $keyMap[$name] = $position;
        }

        $argumentCount = count($arguments);
        $consumedCount = 0;
        $hasSeenWildcard = false;

        foreach ($matchers as $matcherKey => $matcher) {
            if ($hasSeenWildcard) {
                throw new InvalidArgumentException(
                    'Wildcard matchers cannot be followed by other matchers.'
                );
            }

            if ($matcher instanceof WildcardMatcher) {
                $argumentKeys = array_keys($arguments);
                $hasSeenWildcard = true;
                $matchCount = 0;
                $innerMatcher = $matcher->matcher();

                while (
                    $consumedCount < $argumentCount &&
                    $innerMatcher
                        ->matches($arguments[$argumentKeys[$consumedCount]])
                ) {
                    ++$matchCount;
                    ++$consumedCount;
                }

                $maximumArguments = $matcher->maximumArguments();

                $isMatch =
                    (
                        $maximumArguments < 0 ||
                        $matchCount <= $maximumArguments
                    ) &&
                    $matchCount >= $matcher->minimumArguments();

                if (!$isMatch) {
                    return false;
                }

                continue;
            }

            if (array_key_exists($matcherKey, $arguments)) {
                $argument = $arguments[$matcherKey];
            } else {
                if (array_key_exists($matcherKey, $keyMap)) {
                    $mappedKey = $keyMap[$matcherKey];

                    if (array_key_exists($mappedKey, $arguments)) {
                        $argument = $arguments[$mappedKey];
                    } else {
                        // no positional / named equivalent
                        return false;
                    }
                } else {
                    // extra argument
                    return false;
                }
            }

            if (!$matcher->matches($argument)) {
                return false;
            }

            ++$consumedCount;
        }

        return $consumedCount === $argumentCount;
    }

    /**
     * Explain which of the supplied arguments match which of the supplied
     * matchers.
     *
     * @param array<int|string,Matcher> $matchers       The matchers.
     * @param array<int,string>         $parameterNames The parameter names.
     * @param array<int|string,mixed>   $arguments      The arguments.
     *
     * @return MatcherResult            The result of matching.
     * @throws InvalidArgumentException If a wildcard matcher precedes any other matcher.
     */
    public function explain(
        array $matchers,
        array $parameterNames,
        array $arguments,
    ): MatcherResult {
        $keyMap = [];

        foreach ($parameterNames as $position => $name) {
            $keyMap[$position] = $name;
            $keyMap[$name] = $position;
        }

        $isMatch = true;
        $matcherMatches = [];
        $argumentMatches = [];
        $argumentCount = count($arguments);
        $consumedCount = 0;
        $hasSeenWildcard = false;
        $argumentKeys = array_keys($arguments);

        foreach ($matchers as $matcherKey => $matcher) {
            if ($hasSeenWildcard) {
                throw new InvalidArgumentException(
                    'Wildcard matchers cannot be followed by other matchers.'
                );
            }

            if ($matcher instanceof WildcardMatcher) {
                $hasSeenWildcard = true;
                $matcherIsMatch = true;
                $innerMatcher = $matcher->matcher();
                $minimumArguments = $matcher->minimumArguments();
                $maximumArguments = $matcher->maximumArguments();

                for ($count = 0; $count < $minimumArguments; ++$count) {
                    if ($consumedCount >= $argumentCount) {
                        $matcherIsMatch = false;
                        $argumentMatches[count($argumentMatches)] = false;

                        break;
                    }

                    $argumentKey = $argumentKeys[$consumedCount];

                    if ($innerMatcher->matches($arguments[$argumentKey])) {
                        $argumentMatches[$argumentKey] = true;
                    } else {
                        $matcherIsMatch = false;
                        $argumentMatches[$argumentKey] = false;
                    }

                    ++$consumedCount;
                }

                if ($maximumArguments < 0) {
                    while ($consumedCount < $argumentCount) {
                        $argumentKey = $argumentKeys[$consumedCount];

                        if (!$innerMatcher->matches($arguments[$argumentKey])) {
                            break;
                        }

                        $argumentMatches[$argumentKey] = true;
                        ++$consumedCount;
                    }
                } else {
                    for (; $count < $maximumArguments; ++$count) {
                        if ($consumedCount >= $argumentCount) {
                            break;
                        }

                        $argumentKey = $argumentKeys[$consumedCount];

                        if (!$innerMatcher->matches($arguments[$argumentKey])) {
                            break;
                        }

                        $argumentMatches[$argumentKey] = true;
                        ++$consumedCount;
                    }
                }

                $isMatch = $isMatch && $matcherIsMatch;
                $matcherMatches[$matcherKey] = $matcherIsMatch;

                continue;
            }

            if (array_key_exists($matcherKey, $arguments)) {
                $argumentKey = $matcherKey;
                $matcherIsMatch = $matcher->matches($arguments[$argumentKey]);
            } else {
                if (array_key_exists($matcherKey, $keyMap)) {
                    $argumentKey = $keyMap[$matcherKey];

                    if (array_key_exists($argumentKey, $arguments)) {
                        $matcherIsMatch =
                            $matcher->matches($arguments[$argumentKey]);
                    } else {
                        // no positional / named equivalent
                        $argumentKey = $consumedCount;
                        $matcherIsMatch = false;
                    }
                } else {
                    // extra argument
                    $argumentKey = $consumedCount;
                    $matcherIsMatch = false;
                }
            }

            $isMatch = $isMatch && $matcherIsMatch;
            $matcherMatches[$matcherKey] = $matcherIsMatch;
            $argumentMatches[$argumentKey] = $matcherIsMatch;
            ++$consumedCount;
        }

        for (; $consumedCount < $argumentCount; ++$consumedCount) {
            $argumentKey = $argumentKeys[$consumedCount];
            $argumentMatches[$argumentKey] = false;
            $isMatch = false;
        }

        return new MatcherResult($isMatch, $matcherMatches, $argumentMatches);
    }
}
