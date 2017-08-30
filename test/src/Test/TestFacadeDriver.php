<?php

namespace Eloquent\Phony\Test;

use Eloquent\Phony\Assertion\ExceptionAssertionRecorder;
use Eloquent\Phony\Facade\FacadeDriverTrait;

/**
 * A facade driver for Phony integration tests.
 */
class TestFacadeDriver
{
    use FacadeDriverTrait;

    public static function instance(): self
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

    private function __construct($assertionRecorder)
    {
        $this->initializeFacadeDriver($assertionRecorder);
    }

    private static $instance;
}
