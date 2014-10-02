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

use Eloquent\Phony\Call\Factory\CallFactory;
use Eloquent\Phony\Call\Factory\CallFactoryInterface;
use Eloquent\Phony\Spy\Spy;
use Eloquent\Phony\Spy\SpyInterface;

/**
 * Creates spies.
 *
 * @internal
 */
class SpyFactory implements SpyFactoryInterface
{
    /**
     * Get the static instance of this factory.
     *
     * @return SpyFactoryInterface The static factory.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Construct a new spy factory.
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
     * Create a new spy.
     *
     * @param callable|null $callback The callback, or null to create an unbound spy.
     *
     * @return SpyInterface The newly created spy.
     */
    public function create($callback = null)
    {
        return new Spy($callback, $this->callFactory);
    }

    private static $instance;
    private $callFactory;
}
