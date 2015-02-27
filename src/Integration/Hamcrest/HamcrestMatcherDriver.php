<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Integration\Hamcrest;

use Eloquent\Phony\Matcher\Driver\AbstractMatcherDriver;
use Eloquent\Phony\Matcher\Driver\MatcherDriverInterface;

/**
 * A matcher driver for Hamcrest matchers.
 *
 * @internal
 */
class HamcrestMatcherDriver extends AbstractMatcherDriver
{
    /**
     * Get the static instance of this driver.
     *
     * @return MatcherDriverInterface The static driver.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Get the matcher class name.
     *
     * @return string The matcher class name.
     */
    protected function matcherClassName()
    {
        return 'Hamcrest\Matcher';
    }

    private static $instance;
}
