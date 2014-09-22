<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Integration\Mockery;

use Eloquent\Phony\Matcher\MatcherDriverInterface;

/**
 * A matcher driver for Mockery matchers.
 */
class MockeryMatcherDriver implements MatcherDriverInterface
{
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
        if (is_a($matcher, 'Mockery\Matcher\MatcherAbstract')) {
            $matcher = new MockeryMatcher($matcher);

            return true;
        }

        return false;
    }
}
