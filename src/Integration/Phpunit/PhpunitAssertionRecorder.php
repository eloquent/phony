<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Integration\Phpunit;

use Eloquent\Phony\Assertion\AssertionRecorderInterface;
use Exception;
use PHPUnit_Framework_Assert;

/**
 * An assertion recorder that uses PHPUnit_Framework_Assert::assertThat().
 *
 * @see PHPUnit_Framework_Assert::assertThat()
 */
class PhpunitAssertionRecorder implements AssertionRecorderInterface
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
     * Record that a successful assertion occurred.
     */
    public function recordSuccess()
    {
        PHPUnit_Framework_Assert::assertThat(
            null,
            PHPUnit_Framework_Assert::isNull()
        );
    }

    /**
     * Record that an assertion failure occurred.
     *
     * @param string $description The failure description.
     *
     * @throws Exception The appropriate assertion exception.
     */
    public function recordFailure($description)
    {
        PHPUnit_Framework_Assert::assertThat(
            null,
            new PhpunitAssertionFailureConstraint($description)
        );
    } // @codeCoverageIgnore

    private static $instance;
}
