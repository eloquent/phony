<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Call\CallData;
use Eloquent\Phony\Call\CallFactory;
use Eloquent\Phony\Call\Event\CalledEvent;
use Eloquent\Phony\Call\Event\EndEvent;
use Eloquent\Phony\Call\Event\ResponseEvent;
use Eloquent\Phony\Invocation\Invoker;

class TestCallFactory extends CallFactory
{
    public function __construct()
    {
        $this->eventFactory = new TestCallEventFactory();

        parent::__construct(
            $this->eventFactory,
            new Invoker()
        );
    }

    public function eventFactory()
    {
        return $this->eventFactory;
    }

    public function reset()
    {
        $this->eventFactory->reset();
        $this->index = 0;
    }

    public function create(
        CalledEvent $calledEvent = null,
        ResponseEvent $responseEvent = null,
        array $iterableEvents = null,
        EndEvent $endEvent = null
    ) {
        if (!$calledEvent) {
            $calledEvent = $this->eventFactory
                ->createCalled(function () {}, [], new Arguments([]));
        }

        $call = new CallData($this->index++, $calledEvent);

        if ($responseEvent) {
            $call->setResponseEvent($responseEvent);
        }

        if ($iterableEvents) {
            foreach ($iterableEvents as $iterableEvent) {
                $call->addIterableEvent($iterableEvent);
            }
        }

        if ($endEvent) {
            $call->setEndEvent($endEvent);
        }

        return $call;
    }

    private $eventFactory;
    private $index = 0;
}
