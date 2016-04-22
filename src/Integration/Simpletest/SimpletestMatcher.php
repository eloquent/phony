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

use Eloquent\Phony\Matcher\WrappedMatcher;

/**
 * A matcher that wraps a SimpleTest expectation.
 */
class SimpletestMatcher extends WrappedMatcher
{
    /**
     * Returns `true` if `$value` matches this matcher's criteria.
     *
     * @param mixed $value The value to check.
     *
     * @return boolean True if the value matches.
     */
    public function matches($value)
    {
        return (boolean) $this->matcher->test($value);
    }

    /**
     * Describe this matcher.
     *
     * @return string The description.
     */
    public function describe()
    {
        return '<' . get_class($this->matcher) . '>';
    }

    /**
     * Describe this matcher.
     *
     * @return string The description.
     */
    public function __toString()
    {
        return '<' . get_class($this->matcher) . '>';
    }
}
