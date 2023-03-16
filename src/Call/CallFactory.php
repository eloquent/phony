<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call;

use Eloquent\Phony\Call\Event\CallEventFactory;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Spy\SpyData;
use ReflectionParameter;
use Throwable;

/**
 * Creates calls.
 */
class CallFactory
{
    /**
     * Construct a new call factory.
     *
     * @param CallEventFactory $eventFactory The call event factory to use.
     * @param Invoker          $invoker      The invoker to use.
     */
    public function __construct(
        CallEventFactory $eventFactory,
        Invoker $invoker
    ) {
        $this->eventFactory = $eventFactory;
        $this->invoker = $invoker;
    }

    /**
     * Record call details by invoking a callback.
     *
     * @param callable                       $callback   The callback.
     * @param array<int,ReflectionParameter> $parameters The parameters.
     * @param Arguments                      $arguments  The arguments.
     * @param SpyData                        $spy        The spy to record the call to.
     *
     * @return CallData The newly created call.
     */
    public function record(
        callable $callback,
        array $parameters,
        Arguments $arguments,
        SpyData $spy
    ): CallData {
        $originalArguments = $arguments->copy();

        $call = new CallData(
            $spy->nextIndex(),
            $this->eventFactory
                ->createCalled($spy, $parameters, $originalArguments)
        );
        $spy->addCall($call);

        $returnValue = null;
        $exception = null;

        try {
            $returnValue = $this->invoker->callWith($callback, $arguments);
        } catch (Throwable $exception) {
            // handled below
        }

        if ($exception) {
            $responseEvent = $this->eventFactory->createThrew($exception);
        } else {
            $responseEvent = $this->eventFactory->createReturned($returnValue);
        }

        $call->setResponseEvent($responseEvent);

        return $call;
    }

    /**
     * @var CallEventFactory
     */
    private $eventFactory;

    /**
     * @var Invoker
     */
    private $invoker;
}
