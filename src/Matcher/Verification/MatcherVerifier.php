<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Matcher\Verification;

use Eloquent\Phony\Matcher\WildcardMatcherInterface;

/**
 * Verifies argument lists against matcher lists.
 */
class MatcherVerifier implements MatcherVerifierInterface
{
    /**
     * Get the static instance of this verifier.
     *
     * @return MatcherVerifierInterface The static verifier.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Verify that the supplied arguments match the supplied matchers.
     *
     * @param array<MatcherInterface> $matchers  The matchers.
     * @param array                   $arguments The arguments.
     *
     * @return boolean True if the arguments match.
     */
    public function matches(array $matchers, array $arguments)
    {
        $pair = each($arguments);

        foreach ($matchers as $matcher) {
            if ($matcher instanceof WildcardMatcherInterface) {
                $matchCount = 0;

                while ($pair && $matcher->matcher()->matches($pair[1])) {
                    $matchCount++;
                    $pair = each($arguments);
                }

                if ($matchCount < $matcher->minimumArguments()) {
                    return false;
                }

                if (
                    null !== $matcher->maximumArguments() &&
                    $matchCount > $matcher->maximumArguments()
                ) {
                    return false;
                }

                continue;
            } elseif (!$matcher->matches($pair[1])) {
                return false;
            }

            $pair = each($arguments);
        }

        return false === $pair;
    }

    private static $instance;
}
