<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Integration\Simpletest;

use Eloquent\Phony\Matcher\WrappedMatcher;

/**
 * A matcher that wraps a SimpleTest expectation.
 *
 * @internal
 */
class SimpletestMatcher extends WrappedMatcher
{
    /**
     * Returns true if the supplied value matches.
     *
     * @param mixed $value The value to check.
     *
     * @return boolean True if the value matches.
     */
    public function matches($value)
    {
        return $this->matcher->test($value) && true;
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
}
