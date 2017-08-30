<?php

declare(strict_types=1);

namespace Eloquent\Phony\Facade;

use Eloquent\Phony\Assertion\AssertionRecorder;
use Eloquent\Phony\Assertion\ExceptionAssertionRecorder;

/**
 * A service container that supplies all of the underlying services required by
 * the facades.
 */
class FacadeDriver
{
    use FacadeDriverTrait;

    /**
     * Get the static instance of this driver.
     *
     * @return FacadeDriver The static driver.
     */
    public static function instance(): self
    {
        if (!self::$instance) {
            self::$instance = new self(ExceptionAssertionRecorder::instance());
        }

        return self::$instance;
    }

    /**
     * Construct a new facade driver.
     *
     * @param AssertionRecorder $assertionRecorder The assertion recorder to use.
     */
    public function __construct(AssertionRecorder $assertionRecorder)
    {
        $this->initializeFacadeDriver($assertionRecorder);
    }

    private static $instance;
}
