<?php

declare(strict_types=1);

namespace Eloquent\Phony\Matcher\Verification;

use Eloquent\Phony\Call\ArgumentNormalizer;
use Eloquent\Phony\Matcher\Matcher;
use Eloquent\Phony\Matcher\MatcherSet;
use InvalidArgumentException;

/**
 * Verifies argument sets against matcher sets.
 */
class MatcherVerifier
{
    /**
     * Explain which of the supplied arguments match which of the supplied
     * matchers.
     *
     * @param MatcherSet              $matcherSet The matcher set.
     * @param array<int|string,mixed> $arguments  The arguments.
     *
     * @return MatcherResultSet         The result of matching.
     * @throws InvalidArgumentException If the supplied arguments are invalid.
     */
    public function explain(
        MatcherSet $matcherSet,
        array $arguments,
    ): MatcherResultSet {
        $wildcardInnerMatcher = $matcherSet->wildcardInnerMatcher;

        $declaredArguments = [];
        $variadicArguments = [];
        $hasNamedArgument = false;
        $position = -1;

        foreach ($arguments as $argumentKey => $argument) {
            ++$position;

            if (is_int($argumentKey)) {
                if ($hasNamedArgument) {
                    throw new InvalidArgumentException(
                        'Cannot use a positional argument ' .
                        'after a named argument.'
                    );
                }

                if ($position < $matcherSet->declaredCount) {
                    $declaredArguments[$position] = $argument;
                } else {
                    $variadicArguments[$position] = $argument;
                }
            } else {
                $hasNamedArgument = true;

                if (isset($matcherSet->keyMap[$argumentKey])) {
                    $mappedKey = $matcherSet->keyMap[$argumentKey];

                    if (array_key_exists($mappedKey, $declaredArguments)) {
                        throw new InvalidArgumentException(
                            "Named argument $$argumentKey " .
                            'overwrites previous argument.'
                        );
                    }

                    $declaredArguments[$mappedKey] = $argument;
                } else {
                    $variadicArguments[$argumentKey] = $argument;
                }
            }
        }

        $isMatch = true;
        $declaredResults = [];
        $variadicResults = [];
        $wildcardCount = 0;
        $isWildcardExhausted = false;

        for (
            $position = 0;
            $position < $matcherSet->declaredCount;
            ++$position
        ) {
            $matcherKey = $matcherSet->declaredMatchers[$position]
                ? $position
                : null;
            $argumentKey = array_key_exists($position, $declaredArguments)
                ? $position
                : null;

            $matcher = $matcherSet->declaredMatchers[$position] ?? null;

            if (null === $argumentKey) {
                $isSingularMatch = !$matcher;
                $isWildMatch = false;
            } else {
                $argument = $declaredArguments[$argumentKey];
                $canUseWildcard = !$isWildcardExhausted &&
                    $matcherSet->wildcardMatcher;

                if ($matcher) {
                    $isSingularMatch = $matcher->matches($argument);
                    $isWildMatch = false;
                } elseif ($canUseWildcard) {
                    $isSingularMatch = false;
                    /** @var Matcher $wildcardInnerMatcher */
                    $isWildMatch = $wildcardInnerMatcher->matches($argument);

                    if ($isWildMatch) {
                        ++$wildcardCount;
                        $isWildcardExhausted =
                            $wildcardCount === $matcherSet->wildcardMaximum;
                    }
                } else {
                    $isSingularMatch = false;
                    $isWildMatch = false;
                }
            }

            $declaredResults[$position] = new MatcherResult(
                matcherKey: $matcherKey,
                argumentKey: $argumentKey,
                isMatch: $isSingularMatch,
                isWildMatch: $isWildMatch,
            );

            $isMatch = $isMatch && ($isSingularMatch || $isWildMatch);
        }

        foreach ($matcherSet->variadicMatchers as $matcherKey => $matcher) {
            if (array_key_exists($matcherKey, $variadicArguments)) {
                $argumentKey = $matcherKey;
            } else {
                $argumentKey = null;
            }

            if (null === $argumentKey) {
                $isSingularMatch = false;
            } else {
                $argument = $variadicArguments[$argumentKey];
                unset($variadicArguments[$argumentKey]);

                $isSingularMatch = $matcher->matches($argument);
            }

            $variadicResults[] = new MatcherResult(
                matcherKey: $matcherKey,
                argumentKey: $argumentKey,
                isMatch: $isSingularMatch,
                isWildMatch: false,
            );

            $isMatch = $isMatch && $isSingularMatch;
        }

        uksort(
            $variadicArguments,
            [ArgumentNormalizer::class, 'compareVariadicKeys'],
        );

        foreach ($variadicArguments as $argumentKey => $argument) {
            $canUseWildcard = !$isWildcardExhausted &&
                $matcherSet->wildcardMatcher;

            if ($canUseWildcard) {
                /** @var Matcher $wildcardInnerMatcher */
                $isWildMatch = $wildcardInnerMatcher->matches($argument);

                if ($isWildMatch) {
                    ++$wildcardCount;
                    $isWildcardExhausted =
                        $wildcardCount === $matcherSet->wildcardMaximum;
                }
            } else {
                $isWildMatch = false;
            }

            $variadicResults[] = new MatcherResult(
                matcherKey: null,
                argumentKey: $argumentKey,
                isMatch: false,
                isWildMatch: $isWildMatch,
            );

            $isMatch = $isMatch && $isWildMatch;
        }

        usort($variadicResults, [__CLASS__, 'compareVariadicResults']);

        if ($matcherSet->wildcardMatcher) {
            $isWildMatch = $wildcardCount >= $matcherSet->wildcardMinimum;
            $delta = $isWildMatch
                ? 0
                : $wildcardCount - $matcherSet->wildcardMinimum;

            $wildcardResult = new WildcardMatcherResult(
                delta: $delta,
                isMatch: $isWildMatch,
            );

            $isMatch = $isMatch && $isWildMatch;
        } else {
            $wildcardResult = null;
        }

        return new MatcherResultSet(
            isMatch: $isMatch,
            declaredResults: $declaredResults,
            variadicResults: $variadicResults,
            wildcardResult: $wildcardResult,
        );
    }

    /**
     * Verify that the supplied arguments match the supplied matchers.
     *
     * @param MatcherSet              $matcherSet The matcher set.
     * @param array<int|string,mixed> $arguments  The arguments.
     *
     * @return bool                     True if the arguments match.
     * @throws InvalidArgumentException If the supplied arguments are invalid.
     */
    public function matches(
        MatcherSet $matcherSet,
        array $arguments,
    ): bool {
        $wildcardInnerMatcher = $matcherSet->wildcardInnerMatcher;

        $declaredArguments = [];
        $variadicArguments = [];
        $hasNamedArgument = false;
        $position = -1;

        foreach ($arguments as $argumentKey => $argument) {
            ++$position;

            if (is_int($argumentKey)) {
                if ($hasNamedArgument) {
                    throw new InvalidArgumentException(
                        'Cannot use a positional argument ' .
                        'after a named argument.'
                    );
                }

                if ($position < $matcherSet->declaredCount) {
                    $declaredArguments[$position] = $argument;
                } else {
                    $variadicArguments[$position] = $argument;
                }
            } else {
                $hasNamedArgument = true;

                if (isset($matcherSet->keyMap[$argumentKey])) {
                    $mappedKey = $matcherSet->keyMap[$argumentKey];

                    if (array_key_exists($mappedKey, $declaredArguments)) {
                        throw new InvalidArgumentException(
                            "Named argument $$argumentKey " .
                            'overwrites previous argument.'
                        );
                    }

                    $declaredArguments[$mappedKey] = $argument;
                } else {
                    $variadicArguments[$argumentKey] = $argument;
                }
            }
        }

        $wildcardCount = 0;
        $isWildcardExhausted = false;

        for (
            $position = 0;
            $position < $matcherSet->declaredCount;
            ++$position
        ) {
            $matcherKey = $matcherSet->declaredMatchers[$position]
                ? $position
                : null;
            $argumentKey = array_key_exists($position, $declaredArguments)
                ? $position
                : null;

            $matcher = $matcherSet->declaredMatchers[$position] ?? null;

            if (null === $argumentKey) {
                $isSingularMatch = !$matcher;
                $isWildMatch = false;
            } else {
                $argument = $declaredArguments[$argumentKey];
                $canUseWildcard = !$isWildcardExhausted &&
                    $matcherSet->wildcardMatcher;

                if ($matcher) {
                    $isSingularMatch = $matcher->matches($argument);
                    $isWildMatch = false;
                } elseif ($canUseWildcard) {
                    $isSingularMatch = false;
                    /** @var Matcher $wildcardInnerMatcher */
                    $isWildMatch = $wildcardInnerMatcher->matches($argument);

                    if ($isWildMatch) {
                        ++$wildcardCount;
                        $isWildcardExhausted =
                            $wildcardCount === $matcherSet->wildcardMaximum;
                    }
                } else {
                    $isSingularMatch = false;
                    $isWildMatch = false;
                }
            }

            if (!$isSingularMatch && !$isWildMatch) {
                return false;
            }
        }

        foreach ($matcherSet->variadicMatchers as $matcherKey => $matcher) {
            if (array_key_exists($matcherKey, $variadicArguments)) {
                $argumentKey = $matcherKey;
            } else {
                $argumentKey = null;
            }

            if (null === $argumentKey) {
                $isSingularMatch = false;
            } else {
                $argument = $variadicArguments[$argumentKey];
                unset($variadicArguments[$argumentKey]);

                $isSingularMatch = $matcher->matches($argument);
            }

            if (!$isSingularMatch) {
                return false;
            }
        }

        foreach ($variadicArguments as $argumentKey => $argument) {
            $canUseWildcard = !$isWildcardExhausted &&
                $matcherSet->wildcardMatcher;

            if ($canUseWildcard) {
                /** @var Matcher $wildcardInnerMatcher */
                $isWildMatch = $wildcardInnerMatcher->matches($argument);

                if ($isWildMatch) {
                    ++$wildcardCount;
                    $isWildcardExhausted =
                        $wildcardCount === $matcherSet->wildcardMaximum;
                }
            } else {
                $isWildMatch = false;
            }

            if (!$isWildMatch) {
                return false;
            }
        }

        if ($matcherSet->wildcardMatcher) {
            $isWildMatch = $wildcardCount >= $matcherSet->wildcardMinimum;

            if (!$isWildMatch) {
                return false;
            }
        }

        return true;
    }

    private static function compareVariadicResults(
        MatcherResult $a,
        MatcherResult $b,
    ): int {
        $aKey = $a->argumentKey ?? $a->matcherKey;
        $bKey = $b->argumentKey ?? $b->matcherKey;
        $aIsPositional = is_int($aKey);
        $bIsPositional = is_int($bKey);

        if ($aIsPositional && !$bIsPositional) {
            return -1;
        }
        if (!$aIsPositional && $bIsPositional) {
            return 1;
        }

        return $aKey < $bKey ? -1 : 1;
    }
}
