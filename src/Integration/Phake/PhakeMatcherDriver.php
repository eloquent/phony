<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Integration\Phake;

use Eloquent\Phony\Matcher\Driver\AbstractMatcherDriver;
use Eloquent\Phony\Matcher\Driver\MatcherDriverInterface;
use Eloquent\Phony\Matcher\MatcherInterface;
use Eloquent\Phony\Matcher\WildcardMatcher;
use Eloquent\Phony\Matcher\WrappedMatcher;

/**
 * A matcher driver for Phake matchers.
 *
 * @internal
 */
class PhakeMatcherDriver extends AbstractMatcherDriver
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
     * Get the matcher class name.
     *
     * @return string The matcher class name.
     */
    protected function matcherClassName()
    {
        return 'Phake_Matchers_IArgumentMatcher';
    }

    /**
     * Wrap the supplied matcher in a Phony matcher.
     *
     * @param object $matcher The matcher to wrap.
     *
     * @return MatcherInterface The wrapped matcher.
     */
    protected function wrapMatcher($matcher)
    {
        if (is_a($matcher, 'Phake_Matchers_AnyParameters')) {
            return WildcardMatcher::instance();
        }

        return new WrappedMatcher($matcher);
    }

    private static $instance;
}
