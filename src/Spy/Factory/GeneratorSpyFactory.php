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
use Eloquent\Phony\Call\Event\Factory\CallEventFactory;
use Eloquent\Phony\Call\Event\Factory\CallEventFactoryInterface;
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
     * @param CallEventFactoryInterface|null $callEventFactory The call event factory to use.
     */
    public function __construct(
        CallEventFactoryInterface $callEventFactory = null
    ) {
        if (null === $callEventFactory) {
            $callEventFactory = CallEventFactory::instance();
        }

        $this->callEventFactory = $callEventFactory;
    }

    /**
     * Get the call event factory.
     *
     * @return CallEventFactoryInterface The call event factory.
     */
    public function callEventFactory()
    {
        return $this->callEventFactory;
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
        return GeneratorSpyFactoryDetail::create(
            $call,
            $generator,
            $this->callEventFactory
        );
    }

    private static $instance;
    private $callEventFactory;
}
