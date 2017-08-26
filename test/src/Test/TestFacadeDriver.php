<?php

namespace Eloquent\Phony\Test;

use Eloquent\Phony\Assertion\ExceptionAssertionRecorder;
use Eloquent\Phony\Facade\FacadeDriver;

/**
 * A facade driver for Phony integration tests.
 */
class TestFacadeDriver extends FacadeDriver
{
    public static function instance(): FacadeDriver
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
