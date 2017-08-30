<?php

declare(strict_types=1);

namespace Eloquent\Phony\Matcher;

/**
 * Wraps a typical third party matcher.
 */
class WrappedMatcher implements Matcher
{
    use WrappedMatcherTrait;

    /**
     * Construct a new wrapped matcher.
     *
     * @param object $matcher The matcher to wrap.
     */
    public function __construct($matcher)
    {
        $this->matcher = $matcher;
    }
}
