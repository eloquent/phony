<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2017 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Test;

use Eloquent\Phony\Exporter\InlineExporter;
use Eloquent\Phony\Matcher\EqualToMatcher;
use Eloquent\Phony\Matcher\MatcherDriver;

class TestMatcherDriverB implements MatcherDriver
{
    public function isAvailable()
    {
        return true;
    }

    public function matcherClassNames()
    {
        return array('Eloquent\Phony\Test\TestMatcherB');
    }

    public function wrapMatcher($matcher)
    {
        return new EqualToMatcher('b', false, InlineExporter::instance());
    }
}
