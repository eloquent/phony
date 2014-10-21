<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Spy\Factory;

use Eloquent\Phony\Call\CallInterface;
use Eloquent\Phony\Call\Event\Factory\CallEventFactoryInterface;
use Exception;
use Generator;

/**
 * A detail class for generator spy syntax not currently supported by HHVM.
 *
 * @internal
 */
abstract class TraversableSpyFactoryDetail
{
    /**
     * Create a new generator spy.
     *
     * @param CallInterface             $call             The call from which the generator originated.
     * @param Generator                 $generator        The generator.
     * @param CallEventFactoryInterface $callEventFactory The call event factory to use.
     *
     * @return Generator The newly created generator spy.
     */
    public static function createGeneratorSpy(
        CallInterface $call,
        Generator $generator,
        CallEventFactoryInterface $callEventFactory
    ) {
        $isFirst = true;
        $received = null;
        $receivedException = null;

        while (true) {
            $thrown = null;
            $key = null;
            $value = null;

            try {
                if (!$isFirst) {
                    if ($receivedException) {
                        $generator->throw($receivedException);
                    } else {
                        $generator->send($received);
                    }
                }

                if (!$generator->valid()) {
                    $call->setEndEvent($callEventFactory->createReturned());

                    break;
                }
            } catch (Exception $thrown) {
                $call->setEndEvent(
                    $callEventFactory->createThrew($thrown)
                );

                return;
            }

            $key = $generator->key();
            $value = $generator->current();
            $received = null;
            $receivedException = null;

            $call->addTraversableEvent(
                $callEventFactory->createProduced($key, $value)
            );

            try {
                $received = (yield $key => $value);

                $call->addTraversableEvent(
                    $callEventFactory->createReceived($received)
                );
            } catch (Exception $receivedException) {
                $call->addTraversableEvent(
                    $callEventFactory->createReceivedException($receivedException)
                );
            }

            $isFirst = false;
        }
    }
}
