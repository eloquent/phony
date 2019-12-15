<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call;

use Eloquent\Phony\Call\Event\CallEventFactory;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Spy\SpyData;
use Throwable;

/**
 * Creates calls.
 */
class CallFactory
{
    /**
     * Get the static instance of this class.
     *
     * @return self The static instance.
     */
    public static function instance(): self
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
     * @param callable  $callback  The callback.
     * @param Arguments $arguments The arguments.
     * @param SpyData   $spy       The spy to record the call to.
     *
     * @return CallData The newly created call.
     */
    public function record(
        callable $callback,
        Arguments $arguments,
        SpyData $spy
    ): CallData {
        $originalArguments = $arguments->copy();

        $call = new CallData(
            $spy->nextIndex(),
            $this->eventFactory->createCalled($spy, $originalArguments)
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
     * @var ?self
     */
    private static $instance;

    /**
     * @var CallEventFactory
     */
    private $eventFactory;

    /**
     * @var Invoker
     */
    private $invoker;
}
