<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Test;

use Eloquent\Phony\Matcher\EqualToMatcher;
use Eloquent\Phony\Matcher\MatcherDriverInterface;

class TestMatcherDriverB implements MatcherDriverInterface
{
    public function isSupported($matcher)
    {
        return $matcher instanceof TestMatcherB;
    }

    public function adapt(&$matcher)
    {
        if ($this->isSupported($matcher)) {
            $matcher = new EqualToMatcher('b');

            return true;
        }

        return false;
    }
}
