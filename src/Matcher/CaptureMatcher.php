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
 * A matcher that captures the argument to a value.
 *
 * @internal
 */
class CaptureMatcher implements CaptureMatcherInterface
{
    /**
     * Construct a new capture matcher.
     *
     * @param mixed                 &$value  The value to capture to.
     * @param MatcherInterface|null $matcher The internal matcher.
     */
    public function __construct(
        &$value = null,
        MatcherInterface $matcher = null
    ) {
        if (null === $matcher) {
            $matcher = AnyMatcher::instance();
        }

        $this->value = &$value;
        $this->matcher = $matcher;
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
     * Get the wrapped matcher.
     *
     * @return MatcherInterface The matcher.
     */
    public function matcher()
    {
        return $this->matcher;
    }

    /**
     * Returns true if the supplied value matches.
     *
     * @param mixed $value The value to check.
     *
     * @return boolean True if the value matches.
     */
    public function matches($value)
    {
        $this->value = $value;

        return $this->matcher->matches($value);
    }

    /**
     * Describe this matcher.
     *
     * @return string The description.
     */
    public function describe()
    {
        return $this->matcher->describe();
    }

    /**
     * Describe this matcher.
     *
     * @return string The description.
     */
    public function __toString()
    {
        return strval($this->matcher);
    }

    private $value;
    private $matcher;
}
