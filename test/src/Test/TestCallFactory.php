<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Test;

use Eloquent\Phony\Call\Argument\Arguments;
use Eloquent\Phony\Call\Call;
use Eloquent\Phony\Call\Event\CalledEventInterface;
use Eloquent\Phony\Call\Event\EndEventInterface;
use Eloquent\Phony\Call\Event\ResponseEventInterface;
use Eloquent\Phony\Call\Factory\CallFactory;
use Eloquent\Phony\Invocation\Invoker;

class TestCallFactory extends CallFactory
{
    public function __construct()
    {
        $this->eventFactory = new TestCallEventFactory();

        parent::__construct(
            $this->eventFactory,
            Invoker::instance()
        );
    }

    public function eventFactory()
    {
        return $this->eventFactory;
    }

    public function reset()
    {
        $this->eventFactory->reset();
    }

    public function create(
        CalledEventInterface $calledEvent = null,
        ResponseEventInterface $responseEvent = null,
        array $traversableEvents = null,
        EndEventInterface $endEvent = null
    ) {
        if (!$calledEvent) {
            $calledEvent = $this->eventFactory
                ->createCalled(function () {}, new Arguments(array()));
        }

        $call = new Call($calledEvent);

        if ($responseEvent) {
            $call->setResponseEvent($responseEvent);
        }

        if ($traversableEvents) {
            foreach ($traversableEvents as $traversableEvent) {
                $call->addTraversableEvent($traversableEvent);
            }
        }

        if ($endEvent) {
            $call->setEndEvent($endEvent);
        }

        return $call;
    }

    private $eventFactory;
}
