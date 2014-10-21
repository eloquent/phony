<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Factory;

use Eloquent\Phony\Call\Argument\Arguments;
use Eloquent\Phony\Call\Argument\ArgumentsInterface;
use Eloquent\Phony\Call\Call;
use Eloquent\Phony\Call\CallInterface;
use Eloquent\Phony\Call\Event\CalledEventInterface;
use Eloquent\Phony\Call\Event\Factory\CallEventFactory;
use Eloquent\Phony\Call\Event\Factory\CallEventFactoryInterface;
use Eloquent\Phony\Call\Event\ResponseEventInterface;
use Eloquent\Phony\Call\Event\TraversableEventInterface;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Invocation\InvokerInterface;
use Eloquent\Phony\Spy\SpyInterface;
use Exception;
use InvalidArgumentException;

/**
 * Creates calls.
 *
 * @internal
 */
class CallFactory implements CallFactoryInterface
{
    /**
     * Get the static instance of this factory.
     *
     * @return CallFactoryInterface The static factory.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Construct a new call factory.
     *
     * @param CallEventFactoryInterface|null $eventFactory The call event factory to use.
     * @param InvokerInterface|null          $invoker      The invoker to use.
     */
    public function __construct(
        CallEventFactoryInterface $eventFactory = null,
        InvokerInterface $invoker = null
    ) {
        if (null === $eventFactory) {
            $eventFactory = CallEventFactory::instance();
        }
        if (null === $invoker) {
            $invoker = Invoker::instance();
        }

        $this->eventFactory = $eventFactory;
        $this->invoker = $invoker;
    }

    /**
     * Get the call event factory.
     *
     * @return CallEventFactoryInterface The call event factory.
     */
    public function eventFactory()
    {
        return $this->eventFactory;
    }

    /**
     * Get the invoker.
     *
     * @return InvokerInterface The invoker.
     */
    public function invoker()
    {
        return $this->invoker;
    }

    /**
     * Record call details by invoking a callback.
     *
     * @param callable|null                                $callback  The callback.
     * @param ArgumentsInterface|array<integer,mixed>|null $arguments The arguments.
     * @param SpyInterface|null                            $spy       The spy to record the call to.
     *
     * @return CallInterface The newly created call.
     */
    public function record(
        $callback = null,
        $arguments = null,
        SpyInterface $spy = null
    ) {
        if (null === $callback) {
            $callback = function () {};
        }

        $arguments = Arguments::adapt($arguments);
        $originalArguments = $arguments->copy();

        if ($spy) {
            $call = $this->create(
                $this->eventFactory->createCalled($spy, $originalArguments)
            );
            $spy->addCall($call);
        } else {
            $call = $this->create(
                $this->eventFactory->createCalled($callback, $originalArguments)
            );
        }

        $returnValue = null;
        $exception = null;

        try {
            $returnValue = $this->invoker->callWith($callback, $arguments);
        } catch (Exception $exception) {}

        $call->setResponseEvent(
            $this->eventFactory->createResponse($returnValue, $exception)
        );

        return $call;
    }

    /**
     * Create a new call.
     *
     * @param CalledEventInterface|null                     $calledEvent       The 'called' event.
     * @param ResponseEventInterface|null                   $responseEvent     The response event, or null if the call has not yet responded.
     * @param array<integer,TraversableEventInterface>|null $traversableEvents The traversable events.
     * @param ResponseEventInterface|null                   $endEvent          The end event, or null if the call has not yet completed.
     *
     * @return CallInterface            The newly created call.
     * @throws InvalidArgumentException If the supplied calls respresent an invalid call state.
     */
    public function create(
        CalledEventInterface $calledEvent = null,
        ResponseEventInterface $responseEvent = null,
        array $traversableEvents = null,
        ResponseEventInterface $endEvent = null
    ) {
        if (null === $calledEvent) {
            $calledEvent = $this->eventFactory->createCalled();
        }

        return
            new Call($calledEvent, $responseEvent, $traversableEvents, $endEvent);
    }

    private static $instance;
    private $eventFactory;
    private $invoker;
}
