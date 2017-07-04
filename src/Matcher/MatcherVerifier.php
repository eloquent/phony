<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2017 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Matcher;

/**
 * Verifies argument lists against matcher lists.
 */
class MatcherVerifier
{
    /**
     * Get the static instance of this verifier.
     *
     * @return MatcherVerifier The static verifier.
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Verify that the supplied arguments match the supplied matchers.
     *
     * @param array<Matchable> $matchers  The matchers.
     * @param array            $arguments The arguments.
     *
     * @return bool True if the arguments match.
     */
    public function matches(array $matchers, array $arguments)
    {
        $argumentCount = count($arguments);
        $index = 0;

        foreach ($matchers as $matcher) {
            if ($matcher instanceof WildcardMatcher) {
                $matchCount = 0;
                $innerMatcher = $matcher->matcher();

                while (
                    $index < $argumentCount &&
                    $innerMatcher->matches($arguments[$index])
                ) {
                    ++$matchCount;
                    ++$index;
                }

                $maximumArguments = $matcher->maximumArguments();

                $isMatch =
                    (
                        null === $maximumArguments ||
                        $matchCount <= $maximumArguments
                    ) &&
                    $matchCount >= $matcher->minimumArguments();

                if (!$isMatch) {
                    return false;
                }

                continue;
            }

            if (
                $index >= $argumentCount ||
                !$matcher->matches($arguments[$index])
            ) {
                return false;
            }

            ++$index;
        }

        return $index === $argumentCount;
    }

    /**
     * Explain which of the supplied arguments match which of the supplied
     * matchers.
     *
     * @param array<Matchable> $matchers  The matchers.
     * @param array            $arguments The arguments.
     *
     * @return MatcherResult The result of matching.
     */
    public function explain(array $matchers, array $arguments)
    {
        $isMatch = true;
        $matcherMatches = array();
        $argumentMatches = array();
        $argumentCount = count($arguments);
        $index = 0;

        foreach ($matchers as $matcher) {
            if ($matcher instanceof WildcardMatcher) {
                $matcherIsMatch = true;
                $innerMatcher = $matcher->matcher();
                $minimumArguments = $matcher->minimumArguments();
                $maximumArguments = $matcher->maximumArguments();

                for ($count = 0; $count < $minimumArguments; ++$count) {
                    if ($index >= $argumentCount) {
                        $matcherIsMatch = false;
                        $argumentMatches[] = false;

                        break;
                    }

                    if ($innerMatcher->matches($arguments[$index])) {
                        $argumentMatches[] = true;
                    } else {
                        $matcherIsMatch = false;
                        $argumentMatches[] = false;
                    }

                    ++$index;
                }

                if (null === $maximumArguments) {
                    while (
                        $index < $argumentCount &&
                        $innerMatcher->matches($arguments[$index])
                    ) {
                        $argumentMatches[] = true;
                        ++$index;
                    }
                } else {
                    for (; $count < $maximumArguments; ++$count) {
                        if (
                            $index >= $argumentCount ||
                            !$innerMatcher->matches($arguments[$index])
                        ) {
                            break;
                        }

                        $argumentMatches[] = true;
                        ++$index;
                    }
                }

                $isMatch = $isMatch && $matcherIsMatch;
                $matcherMatches[] = $matcherIsMatch;

                continue;
            }

            $matcherIsMatch =
                $index < $argumentCount &&
                $matcher->matches($arguments[$index]);

            $isMatch = $isMatch && $matcherIsMatch;
            $matcherMatches[] = $matcherIsMatch;
            $argumentMatches[] = $matcherIsMatch;
            ++$index;
        }

        for (; $index < $argumentCount; ++$index) {
            $argumentMatches[] = false;
            $isMatch = false;
        }

        return new MatcherResult($isMatch, $matcherMatches, $argumentMatches);
    }

    private static $instance;
}
