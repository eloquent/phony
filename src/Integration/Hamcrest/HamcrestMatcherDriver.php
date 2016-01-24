<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Integration\Hamcrest;

use Eloquent\Phony\Matcher\Driver\AbstractMatcherDriver;
use Eloquent\Phony\Matcher\Driver\MatcherDriverInterface;

/**
 * A matcher driver for Hamcrest matchers.
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
     * Returns true if this matcher driver's classes or interfaces exist.
     *
     * @return boolean True if available.
     */
    public function isAvailable()
    {
        return interface_exists('Hamcrest\Matcher');
    }

    /**
     * Get the supported matcher class names.
     *
     * @return array<string> The matcher class names.
     */
    public function matcherClassNames()
    {
        return array('Hamcrest\Matcher');
    }

    private static $instance;
}
