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
use Eloquent\Phony\Call\Factory\CallFactory;
use Eloquent\Phony\Call\Factory\CallFactoryInterface;
use Exception;
use Generator;

/**
 * Creates generator spies.
 *
 * @internal
 */
class GeneratorSpyFactory implements GeneratorSpyFactoryInterface
{
    /**
     * Get the static instance of this factory.
     *
     * @return GeneratorSpyFactoryInterface The static factory.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Construct a new generator spy factory.
     *
     * @param CallFactoryInterface|null $callFactory The call factory to use.
     */
    public function __construct(CallFactoryInterface $callFactory = null)
    {
        if (null === $callFactory) {
            $callFactory = CallFactory::instance();
        }

        $this->callFactory = $callFactory;
    }

    /**
     * Get the call factory.
     *
     * @return CallFactoryInterface The call factory.
     */
    public function callFactory()
    {
        return $this->callFactory;
    }

    /**
     * Create a new generator spy.
     *
     * @param CallInterface $call      The call from which the generator originated.
     * @param Generator     $generator The generator.
     *
     * @return Generator The newly created generator spy.
     */
    public function create(CallInterface $call, Generator $generator)
    {
        $isFirst = true;
        $sent = null;
        $sentException = null;

        while (true) {
            $thrown = null;
            $key = null;
            $value = null;

            try {
                if (!$isFirst) {
                    if ($sentException) {
                        $generator->throw($sentException);
                    } else {
                        $generator->send($sent);
                    }
                }

                if (!$generator->valid()) {
                    $call->setEndEvent(
                        $this->callFactory->createReturnedEvent()
                    );

                    break;
                }
            } catch (Exception $thrown) {
                $call->setEndEvent(
                    $this->callFactory->createThrewEvent($thrown)
                );

                return;
            }

            $key = $generator->key();
            $value = $generator->current();
            $sent = null;
            $sentException = null;

            $call->addGeneratorEvent(
                $this->callFactory->createYieldedEvent($value, $key)
            );

            try {
                $sent = (yield $key => $value);

                $call->addGeneratorEvent(
                    $this->callFactory->createSentEvent($sent)
                );
            } catch (Exception $sentException) {
                $call->addGeneratorEvent(
                    $this->callFactory->createSentExceptionEvent($sentException)
                );
            }

            $isFirst = false;
        }
    }

    private static $instance;
    private $callFactory;
}
