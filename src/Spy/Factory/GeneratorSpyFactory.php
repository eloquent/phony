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
use InvalidArgumentException;
use Traversable;

/**
 * Creates generator spies.
 *
 * @internal
 */
class GeneratorSpyFactory implements TraversableSpyFactoryInterface
{
    /**
     * Get the static instance of this factory.
     *
     * @return TraversableSpyFactoryInterface The static factory.
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
     * Returns true if the supplied value is supported by this factory.
     *
     * @param mixed $value The value to check.
     *
     * @return boolean True if the supplied value is supported.
     */
    public function isSupported($value)
    {
        return $value instanceof Generator;
    }

    /**
     * Create a new traversable spy.
     *
     * @param CallInterface     $call        The call from which the traversable originated.
     * @param Traversable|array $traversable The traversable.
     *
     * @return Traversable              The newly created traversable spy.
     * @throws InvalidArgumentException If the supplied traversable is invalid.
     */
    public function create(CallInterface $call, $traversable)
    {
        if (!$this->isSupported($traversable)) {
            if (is_object($traversable)) {
                $type = var_export(get_class($traversable), true);
            } else {
                $type = gettype($traversable);
            }

            throw new InvalidArgumentException(
                sprintf('Unsupported traversable of type %s.', $type)
            );
        }

        return GeneratorSpyFactoryDetail
            ::createGeneratorSpy($call, $traversable, $this->callEventFactory);
    }

    private static $instance;
    private $callEventFactory;
}
