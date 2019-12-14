<?php

declare(strict_types=1);

namespace Eloquent\Phony\Assertion;

use Eloquent\Phony\Call\CallVerifierFactory;
use Eloquent\Phony\Event\Event;
use Eloquent\Phony\Event\EventCollection;
use Throwable;

/**
 * The interface implemented by assertion recorders.
 */
interface AssertionRecorder
{
    /**
     * Set the call verifier factory.
     *
     * @param CallVerifierFactory $callVerifierFactory The call verifier factory to use.
     */
    public function setCallVerifierFactory(
        CallVerifierFactory $callVerifierFactory
    ): void;

    /**
     * Record that a successful assertion occurred.
     *
     * @param array<int,Event> $events The events.
     *
     * @return EventCollection The result.
     */
    public function createSuccess(array $events = []): EventCollection;

    /**
     * Record that a successful assertion occurred.
     *
     * @param EventCollection $events The events.
     *
     * @return EventCollection The result.
     */
    public function createSuccessFromEventCollection(
        EventCollection $events
    ): EventCollection;

    /**
     * Create a new assertion failure exception.
     *
     * @param string $description The failure description.
     *
     * @return null      If this recorder does not throw exceptions.
     * @throws Throwable If this recorder throws exceptions.
     */
    public function createFailure(string $description);
}
