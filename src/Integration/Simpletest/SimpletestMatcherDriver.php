<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Integration\Simpletest;

use Eloquent\Phony\Matcher\Driver\AbstractMatcherDriver;
use Eloquent\Phony\Matcher\Driver\MatcherDriver;
use Eloquent\Phony\Matcher\Matcher;

/**
 * A matcher driver for SimpleTest expectations.
 */
class SimpletestMatcherDriver extends AbstractMatcherDriver
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
        return class_exists('SimpleExpectation');
    }

    /**
     * Get the supported matcher class names.
     *
     * @return array<string> The matcher class names.
     */
    public function matcherClassNames()
    {
        return array('SimpleExpectation');
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
        return new SimpletestMatcher($matcher);
    }

    private static $instance;
}
