<?php

declare(strict_types=1);

namespace Eloquent\Phony\Spy\Detail;

use Eloquent\Phony\Call\Call;
use Eloquent\Phony\Call\Event\CallEventFactory;
use Generator;
use Throwable;

/**
 * A detail class for generator spies under PHP.
 */
abstract class GeneratorSpyFactoryDetailPhp
{
    /**
     * Create a new generator spy.
     *
     * @param Call             $call             The call from which the generator originated.
     * @param Generator        $generator        The generator.
     * @param CallEventFactory $callEventFactory The call event factory to use.
     *
     * @return Generator The newly created generator spy.
     */
    public static function createGeneratorSpy(
        Call $call,
        Generator $generator,
        CallEventFactory $callEventFactory
    ): Generator {
        $call->addIterableEvent($callEventFactory->createUsed());

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
                        $callEventFactory->createReturned($returnValue)
                    );

                    return $returnValue;
                }
            } catch (Throwable $thrown) {
                $call->setEndEvent(
                    $callEventFactory->createThrew($thrown)
                );

                throw $thrown;
            }

            $key = $generator->key();
            $value = $generator->current();
            $received = null;
            $receivedException = null;

            $call->addIterableEvent(
                $callEventFactory->createProduced($key, $value)
            );

            try {
                $received = (yield $key => $value);

                $call->addIterableEvent(
                    $callEventFactory->createReceived($received)
                );
            } catch (Throwable $receivedException) {
                $call->addIterableEvent(
                    $callEventFactory
                        ->createReceivedException($receivedException)
                );
            }

            $isFirst = false;
            unset($value);
        }
    }
}
