<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Integration\Mockery;

use Eloquent\Phony\Matcher\Driver\AbstractMatcherDriver;
use Eloquent\Phony\Matcher\Driver\MatcherDriverInterface;
use Eloquent\Phony\Matcher\MatcherInterface;

/**
 * A matcher driver for Mockery matchers.
 */
class MockeryMatcherDriver extends AbstractMatcherDriver
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
     * Returns true if this matcher driver's classes or interfaces exist.
     *
     * @return boolean True if available.
     */
    public function isAvailable()
    {
        return class_exists('Mockery\Matcher\MatcherAbstract');
    }

    /**
     * Get the supported matcher class names.
     *
     * @return array<string> The matcher class names.
     */
    public function matcherClassNames()
    {
        return array('Mockery\Matcher\MatcherAbstract');
    }

    /**
     * Wrap the supplied third party matcher.
     *
     * @param object $matcher The matcher to wrap.
     *
     * @return MatcherInterface The wrapped matcher.
     */
    public function wrapMatcher($matcher)
    {
        return new MockeryMatcher($matcher);
    }

    private static $instance;
}
