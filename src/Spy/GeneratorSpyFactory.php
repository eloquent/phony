<?php

declare(strict_types=1);

namespace Eloquent\Phony\Spy;

use Eloquent\Phony\Call\Call;
use Eloquent\Phony\Call\Event\CallEventFactory;
use Generator;
use Throwable;

/**
 * Creates generator spies.
 */
class GeneratorSpyFactory
{
    /**
     * Construct a new generator spy factory.
     *
     * @param CallEventFactory $callEventFactory The call event factory to use.
     * @param GeneratorSpyMap  $generatorSpyMap  The generator spy map to use.
     */
    public function __construct(
        CallEventFactory $callEventFactory,
        GeneratorSpyMap $generatorSpyMap
    ) {
        $this->callEventFactory = $callEventFactory;
        $this->generatorSpyMap = $generatorSpyMap;
    }

    /**
     * Create a new generator spy.
     *
     * @param Call             $call      The call from which the generator originated.
     * @param Generator<mixed> $generator The generator.
     *
     * @return Generator<mixed> The newly created generator spy.
     */
    public function create(Call $call, Generator $generator): Generator
    {
        $spy = $this->createSpy($call, $generator);
        $this->generatorSpyMap->set($spy, $generator);

        return $spy;
    }

    /**
     * @param Generator<mixed> $generator
     *
     * @return Generator<mixed>
     */
    private function createSpy(Call $call, Generator $generator): Generator
    {
        $call->addIterableEvent($this->callEventFactory->createUsed());

        $isFirst = true;
        $received = null;
        $receivedException = null;

        while (true) {
            $thrown = null;

            try {
                if (!$isFirst) {
                    if ($receivedException) {
                        $generator->throw($receivedException);
                    } else {
                        $generator->send($received);
                    }
                }

                if (!$generator->valid()) {
                    $returnValue = $generator->getReturn();
                    $call->setEndEvent(
                        $this->callEventFactory->createReturned($returnValue)
                    );

                    return $returnValue;
                }
            } catch (Throwable $thrown) {
                $call->setEndEvent(
                    $this->callEventFactory->createThrew($thrown)
                );

                throw $thrown;
            }

            $key = $generator->key();
            $value = $generator->current();
            $received = null;
            $receivedException = null;

            $call->addIterableEvent(
                $this->callEventFactory->createProduced($key, $value)
            );

            try {
                $received = yield $key => $value;

                $call->addIterableEvent(
                    $this->callEventFactory->createReceived($received)
                );
            } catch (Throwable $receivedException) {
                $call->addIterableEvent(
                    $this->callEventFactory
                        ->createReceivedException($receivedException)
                );
            }

            $isFirst = false;
            unset($value);
        }
    }

    /**
     * @var CallEventFactory
     */
    private $callEventFactory;

    /**
     * @var GeneratorSpyMap
     */
    private $generatorSpyMap;
}
