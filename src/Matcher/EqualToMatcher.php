<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Matcher;

/**
 * A matcher that tests if the value is equal to (==) another value.
 */
class EqualToMatcher implements MatcherInterface
{
    /**
     * Construct a new equal to matcher.
     *
     * @param mixed $value The value to check against.
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Get the value.
     *
     * @return mixed The value.
     */
    public function value()
    {
        return $this->value;
    }

    /**
     * Returns true if the supplied value matches.
     *
     * @param mixed $value The value to check.
     *
     * @return boolen True if the value matches.
     */
    public function matches($value)
    {
        return $value == $this->value;
    }

    private $value;
}
