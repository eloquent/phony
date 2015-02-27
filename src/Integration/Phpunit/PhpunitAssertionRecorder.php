<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Integration\Phpunit;

use Eloquent\Phony\Assertion\Recorder\AssertionRecorder;
use Eloquent\Phony\Assertion\Recorder\AssertionRecorderInterface;
use Eloquent\Phony\Call\Event\CallEventCollectionInterface;
use Eloquent\Phony\Event\EventInterface;
use Exception;
use PHPUnit_Framework_Assert;

/**
 * An assertion recorder for PHPUnit.
 *
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
     * @return CallEventCollectionInterface The result.
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
     * @throws Exception If this recorder throws exceptions.
     */
    public function createFailure($description)
    {
        throw new PhpunitAssertionException($description);
    }

    private static $instance;
}
