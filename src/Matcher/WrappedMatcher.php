<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Matcher;

/**
 * Wraps a typical third party matcher.
 */
class WrappedMatcher extends AbstractMatcher implements
    WrappedMatcherInterface
{
    /**
     * Construct a new wrapped matcher.
     *
     * @param object $matcher The matcher to wrap.
     */
    public function __construct($matcher)
    {
        $this->matcher = $matcher;
    }

    /**
     * Get the wrapped matcher.
     *
     * @return object The matcher.
     */
    public function matcher()
    {
        return $this->matcher;
    }

    /**
     * Returns `true` if `$value` matches this matcher's criteria.
     *
     * @param mixed $value The value to check.
     *
     * @return boolean True if the value matches.
     */
    public function matches($value)
    {
        return (boolean) $this->matcher->matches($value);
    }

    /**
     * Describe this matcher.
     *
     * @return string The description.
     */
    public function describe()
    {
        return '<' . strval($this->matcher) . '>';
    }

    protected $matcher;
}
