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

use Eloquent\Phony\Assertion\ExceptionAssertionRecorder;
use Eloquent\Phony\Facade\FacadeDriver;

/**
 * A facade driver for Phony integration tests.
 */
class TestFacadeDriver extends FacadeDriver
{
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self(ExceptionAssertionRecorder::instance());
        }

        return self::$instance;
    }

    public function reset()
    {
        $this->exporter->reset();

        foreach ($this->sequences as $sequence) {
            $sequence->reset();
        }
    }

    private static $instance;
}
