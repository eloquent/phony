<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Integration\Simpletest;

use Eloquent\Phony\Facade\FacadeDriverInterface;
use Eloquent\Phony\Integration\AbstractIntegratedFacadeDriver;

/**
 * A facade driver for SimpleTest.
 *
 * @internal
 */
class SimpletestFacadeDriver extends AbstractIntegratedFacadeDriver
{
    /**
     * Get the static instance of this driver.
     *
     * @return FacadeDriverInterface The static driver.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Create the assertion recorder.
     *
     * @return AssertionRecorderInterface The assertion recorder.
     */
    protected function createAssertionRecorder()
    {
        return SimpletestAssertionRecorder::instance();
    }

    private static $instance;
}
