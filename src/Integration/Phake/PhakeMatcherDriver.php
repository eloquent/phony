<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Integration\Phake;

use Eloquent\Phony\Matcher\MatcherDriverInterface;
use Eloquent\Phony\Matcher\WildcardMatcher;

/**
 * A matcher driver for Phake matchers.
 */
class PhakeMatcherDriver implements MatcherDriverInterface
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
        if (is_a($matcher, 'Phake_Matchers_AnyParameters')) {
            $matcher = WildcardMatcher::instance();

            return true;
        }

        if (is_a($matcher, 'Phake_Matchers_IArgumentMatcher')) {
            $matcher = new PhakeMatcher($matcher);

            return true;
        }

        return false;
    }
}
