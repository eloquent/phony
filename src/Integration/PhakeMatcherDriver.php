<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Integration;

use Eloquent\Phony\Matcher\AbstractMatcherDriver;
use Eloquent\Phony\Matcher\Matcher;
use Eloquent\Phony\Matcher\MatcherDriver;
use Eloquent\Phony\Matcher\WildcardMatcher;
use Eloquent\Phony\Matcher\WrappedMatcher;

/**
 * A matcher driver for Phake matchers.
 */
class PhakeMatcherDriver extends AbstractMatcherDriver
{
    /**
     * Get the static instance of this driver.
     *
     * @return MatcherDriver The static driver.
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Returns true if this matcher driver's classes or interfaces exist.
     *
     * @return boolean True if available.
     */
    public function isAvailable()
    {
        return interface_exists('Phake_Matchers_IArgumentMatcher') ||
            interface_exists('Phake_Matchers_IChainableArgumentMatcher');
    }

    /**
     * Get the supported matcher class names.
     *
     * @return array<string> The matcher class names.
     */
    public function matcherClassNames()
    {
        return array(
            'Phake_Matchers_IArgumentMatcher',
            'Phake_Matchers_IChainableArgumentMatcher',
        );
    }

    /**
     * Wrap the supplied third party matcher.
     *
     * @param object $matcher The matcher to wrap.
     *
     * @return Matcher The wrapped matcher.
     */
    public function wrapMatcher($matcher)
    {
        if (is_a($matcher, 'Phake_Matchers_AnyParameters')) {
            return WildcardMatcher::instance();
        }

        return new WrappedMatcher($matcher);
    }

    private static $instance;
}
