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
     * Wrap the supplied third party matcher.
     *
     * @param object $matcher The matcher to wrap.
     *
     * @return MatcherInterface The wrapped matcher.
     */
    public function wrapMatcher($matcher)
    {
        return new WrappedMatcher($matcher);
    }
}
