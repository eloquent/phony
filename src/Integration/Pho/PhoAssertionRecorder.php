<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Integration\Pho;

use Eloquent\Phony\Assertion\Recorder\AssertionRecorder;
use Eloquent\Phony\Assertion\Recorder\AssertionRecorderInterface;
use Exception;

/**
 * An assertion recorder that uses Pho expectation exceptions.
 *
 * @internal
 */
class PhoAssertionRecorder extends AssertionRecorder
{
    /**
     * Get the static instance of this recorder.
     *
     * @return AssertionRecorderInterface The static recorder.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Create a new assertion failure exception.
     *
     * @param string $description The failure description.
     *
     * @return Exception The appropriate assertion failure exception.
     */
    public function createFailure($description)
    {
        return new PhoAssertionException($description);
    }

    private static $instance;
}
