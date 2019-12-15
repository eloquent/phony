<?php

declare(strict_types=1);

namespace Eloquent\Phony\Matcher;

/**
 * Verifies argument lists against matcher lists.
 */
class MatcherVerifier
{
    /**
     * Get the static instance of this class.
     *
     * @return self The static instance.
     */
    public static function instance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Verify that the supplied arguments match the supplied matchers.
     *
     * @param array<int,Matcher> $matchers  The matchers.
     * @param array<int,mixed>   $arguments The arguments.
     *
     * @return bool True if the arguments match.
     */
    public function matches(array $matchers, array $arguments): bool
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
                        $maximumArguments < 0 ||
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
     * @param array<int,Matcher> $matchers  The matchers.
     * @param array<int,mixed>   $arguments The arguments.
     *
     * @return MatcherResult The result of matching.
     */
    public function explain(array $matchers, array $arguments): MatcherResult
    {
        $isMatch = true;
        $matcherMatches = [];
        $argumentMatches = [];
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

                if ($maximumArguments < 0) {
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

    /**
     * @var ?self
     */
    private static $instance;
}
