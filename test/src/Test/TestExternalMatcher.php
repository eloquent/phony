<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Test;

class TestExternalMatcher
{
    public function matches($value)
    {
        return 'value' === $value;
    }

    public function __toString()
    {
        return __CLASS__;
    }
}
