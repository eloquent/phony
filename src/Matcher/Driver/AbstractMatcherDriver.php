<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Matcher\Driver;

use Eloquent\Phony\Matcher\MatcherInterface;
use Eloquent\Phony\Matcher\WrappedMatcher;

/**
 * An abstract base class for implementing matcher drivers.
 *
 * @internal
 */
abstract class AbstractMatcherDriver implements MatcherDriverInterface
{
    /**
     * Returns true if the matchers supported by this driver are available.
     *
     * @return boolean True if available.
     */
    public function isAvailable()
    {
        $className = $this->matcherClassName();

        return interface_exists($className) || class_exists($className);
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
        return is_a($matcher, $this->matcherClassName());
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
        if (is_a($matcher, $this->matcherClassName())) {
            $matcher = $this->wrapMatcher($matcher);

            return true;
        }

        return false;
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
        return new WrappedMatcher($matcher);
    }

    /**
     * Get the matcher class name.
     *
     * @return string The matcher class name.
     */
    abstract protected function matcherClassName();
}
