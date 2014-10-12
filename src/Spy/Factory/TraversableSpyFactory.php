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

use ArrayIterator;
use Eloquent\Phony\Call\CallInterface;
use Eloquent\Phony\Call\Event\Factory\CallEventFactory;
use Eloquent\Phony\Call\Event\Factory\CallEventFactoryInterface;
use Eloquent\Phony\Spy\IteratorSpy;
use Generator;
use InvalidArgumentException;
use IteratorAggregate;
use Traversable;

/**
 * Creates traversable spies.
 *
 * @internal
 */
class TraversableSpyFactory implements TraversableSpyFactoryInterface
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
     * Construct a new traversable spy factory.
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
     * Returns true if the supplied value is traversable.
     *
     * @param mixed $value The value to check.
     *
     * @return boolean True if the supplied value is traversable.
     */
    public function isTraversable($value)
    {
        return is_array($value) || $value instanceof Traversable;
    }

    /**
     * Create a new traversable spy.
     *
     * @param CallInterface     $call              The call from which the traversable originated.
     * @param Traversable|array $traversable       The traversable.
     * @param boolean|null      $useGeneratorSpies True if generator spies should be used.
     *
     * @return Traversable              The newly created traversable spy.
     * @throws InvalidArgumentException If the supplied traversable is invalid.
     */
    public function create(
        CallInterface $call,
        $traversable,
        $useGeneratorSpies = null
    ) {
        if (!$this->isTraversable($traversable)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid traversable of type %s.',
                    var_export(gettype($traversable), true)
                )
            );
        }

        if (null === $useGeneratorSpies) {
            $useGeneratorSpies = !defined('HHVM_VERSION');
        }

        if ($useGeneratorSpies && $traversable instanceof Generator) {
            return TraversableSpyFactoryDetail::createGeneratorSpy(
                $call,
                $traversable,
                $this->callEventFactory
            );
        }

        if (is_array($traversable)) {
            $iterator = new ArrayIterator($traversable);
        } elseif ($traversable instanceof IteratorAggregate) {
            $iterator = $traversable->getIterator();
        } else {
            $iterator = $traversable;
        }

        return new IteratorSpy($call, $iterator, $this->callEventFactory);
    }

    private static $instance;
    private $callEventFactory;
}
