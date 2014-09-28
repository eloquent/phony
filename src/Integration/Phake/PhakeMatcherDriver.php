<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Integration\Phake;

use Eloquent\Phony\Matcher\MatcherDriverInterface;
use Eloquent\Phony\Matcher\WildcardMatcher;

/**
 * A matcher driver for Phake matchers.
 */
class PhakeMatcherDriver implements MatcherDriverInterface
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
        return is_a($matcher, 'Phake_Matchers_IArgumentMatcher');
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
        if (is_a($matcher, 'Phake_Matchers_AnyParameters')) {
            $matcher = WildcardMatcher::instance();

            return true;
        }

        if (is_a($matcher, 'Phake_Matchers_IArgumentMatcher')) {
            $matcher = new PhakeMatcher($matcher);

            return true;
        }

        return false;
    }

    private static $instance;
}
