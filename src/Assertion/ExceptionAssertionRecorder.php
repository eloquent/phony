<?php

declare(strict_types=1);

namespace Eloquent\Phony\Assertion;

use Eloquent\Phony\Assertion\Exception\AssertionException;
use Eloquent\Phony\Call\CallVerifierFactory;
use Eloquent\Phony\Event\Event;
use Eloquent\Phony\Event\EventCollection;
use Eloquent\Phony\Event\EventSequence;

/**
 * An assertion recorder that throws exceptions on failure.
 */
class ExceptionAssertionRecorder implements AssertionRecorder
{
    /**
     * Get the static instance of this class.
     *
     * @return self The static instance.
     */
    public static function instance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Set the call verifier factory.
     *
     * @param CallVerifierFactory $callVerifierFactory The call verifier factory to use.
     */
    public function setCallVerifierFactory(
        CallVerifierFactory $callVerifierFactory
    ): void {
        $this->callVerifierFactory = $callVerifierFactory;
    }

    /**
     * Record that a successful assertion occurred.
     *
     * @param array<int,Event> $events The events.
     *
     * @return EventCollection The result.
     */
    public function createSuccess(array $events = []): EventCollection
    {
        return new EventSequence($events, $this->callVerifierFactory);
    }

    /**
     * Record that a successful assertion occurred.
     *
     * @param EventCollection $events The events.
     *
     * @return EventCollection The result.
     */
    public function createSuccessFromEventCollection(
        EventCollection $events
    ): EventCollection {
        return $events;
    }

    /**
     * Create a new assertion failure exception.
     *
     * @param string $description The failure description.
     *
     * @return null               This method never returns.
     * @throws AssertionException The assertion failure exception.
     */
    public function createFailure(string $description)
    {
        throw new AssertionException($description);
    }

    /**
     * @var ?self
     */
    private static $instance;

    /**
     * @var CallVerifierFactory
     */
    private $callVerifierFactory;
}
