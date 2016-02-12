<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Test;

use Eloquent\Phony\Matcher\Driver\MatcherDriverInterface;
use Eloquent\Phony\Matcher\EqualToMatcher;

class TestMatcherDriverA implements MatcherDriverInterface
{
    public function isAvailable()
    {
        return true;
    }

    public function matcherClassNames()
    {
        return array('Eloquent\Phony\Test\TestMatcherA');
    }

    public function wrapMatcher($matcher)
    {
        return new EqualToMatcher('a');
    }
}
