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

use Eloquent\Phony\Assertion\Recorder\AssertionRecorder;
use Eloquent\Phony\Assertion\Recorder\AssertionRecorderInterface;
use Eloquent\Phony\Event\EventCollectionInterface;
use Eloquent\Phony\Event\EventInterface;
use Exception;
use PHPUnit_Framework_Assert;
use PHPUnit_Framework_ExpectationFailedException;

/**
 * An assertion recorder that uses PHPUnit_Framework_Assert::assertThat().
 *
 * @see PHPUnit_Framework_Assert::assertThat()
 * @internal
 */
class PhpunitAssertionRecorder extends AssertionRecorder
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
     *
     * @param array<integer,EventInterface>|null $events The events.
     *
     * @return EventCollectionInterface The result.
     */
    public function createSuccess(array $events = null)
    {
        PHPUnit_Framework_Assert::assertThat(
            true,
            PHPUnit_Framework_Assert::isTrue()
        );

        return parent::createSuccess($events);
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
        return new PHPUnit_Framework_ExpectationFailedException($description);
    }

    private static $instance;
}
