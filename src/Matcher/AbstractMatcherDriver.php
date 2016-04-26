<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Matcher;

/**
 * An abstract base class for implementing matcher drivers.
 */
abstract class AbstractMatcherDriver implements MatcherDriver
{
    /**
     * Wrap the supplied third party matcher.
     *
     * @param object $matcher The matcher to wrap.
     *
     * @return Matcher The wrapped matcher.
     */
    public function wrapMatcher($matcher)
    {
        return new WrappedMatcher($matcher);
    }
}
