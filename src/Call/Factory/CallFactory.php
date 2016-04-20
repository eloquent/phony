<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Factory;

use Eloquent\Phony\Call\Argument\Arguments;
use Eloquent\Phony\Call\Argument\ArgumentsInterface;
use Eloquent\Phony\Call\Call;
use Eloquent\Phony\Call\CallInterface;
use Eloquent\Phony\Call\Event\Factory\CallEventFactory;
use Eloquent\Phony\Call\Event\Factory\CallEventFactoryInterface;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Invocation\InvokerInterface;
use Eloquent\Phony\Spy\SpyInterface;
use Exception;
use Throwable;

/**
 * Creates calls.
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
        if (!self::$instance) {
            self::$instance = new self(
                CallEventFactory::instance(),
                Invoker::instance()
            );
        }

        return self::$instance;
    }

    /**
     * Construct a new call factory.
     *
     * @param CallEventFactoryInterface $eventFactory The call event factory to use.
     * @param InvokerInterface          $invoker      The invoker to use.
     */
    public function __construct(
        CallEventFactoryInterface $eventFactory,
        InvokerInterface $invoker
    ) {
        $this->eventFactory = $eventFactory;
        $this->invoker = $invoker;
    }

    /**
     * Record call details by invoking a callback.
     *
     * @param callable           $callback  The callback.
     * @param ArgumentsInterface $arguments The arguments.
     * @param SpyInterface       $spy       The spy to record the call to.
     *
     * @return CallInterface The newly created call.
     */
    public function record(
        $callback,
        ArgumentsInterface $arguments,
        SpyInterface $spy
    ) {
        $originalArguments = $arguments->copy();

        $call = new Call(
            $this->eventFactory->createCalled($spy, $originalArguments)
        );
        $spy->addCall($call);

        $returnValue = null;
        $exception = null;

        try {
            $returnValue = $this->invoker->callWith($callback, $arguments);
        } catch (Throwable $exception) {
            // @codeCoverageIgnoreStart
        } catch (Exception $exception) {
        }
        // @codeCoverageIgnoreEnd

        if ($exception) {
            $responseEvent = $this->eventFactory->createThrew($exception);
        } else {
            $responseEvent = $this->eventFactory->createReturned($returnValue);
        }

        $call->setResponseEvent($responseEvent);

        return $call;
    }

    private static $instance;
    private $eventFactory;
    private $invoker;
}
