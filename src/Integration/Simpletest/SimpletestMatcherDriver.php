<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Integration\Simpletest;

use Eloquent\Phony\Matcher\MatcherDriverInterface;

/**
 * A matcher driver for SimpleTest expectations.
 */
class SimpletestMatcherDriver implements MatcherDriverInterface
{
    /**
     * Get the static instance of this driver.
     *
     * @return MatcherDriverInterface The static driver.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Returns true if the supplied matcher is supported by this driver.
     *
     * @param object $matcher The matcher to test.
     *
     * @return boolean True if supported.
     */
    public function isSupported($matcher)
    {
        return is_a($matcher, 'SimpleExpectation');
    }

    /**
     * If the supplied matcher is supported, replace it with an equivalent Phony
     * matcher.
     *
     * @param object &$matcher The matcher to adapt.
     *
     * @return boolean True if the matcher is supported.
     */
    public function adapt(&$matcher)
    {
        if (is_a($matcher, 'SimpleExpectation')) {
            $matcher = new SimpletestMatcher($matcher);

            return true;
        }

        return false;
    }

    private static $instance;
}
