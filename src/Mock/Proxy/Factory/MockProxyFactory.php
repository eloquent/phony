<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Proxy\Factory;

use Eloquent\Phony\Mock\MockInterface;
use Eloquent\Phony\Mock\Proxy\MockProxy;
use Eloquent\Phony\Mock\Proxy\MockProxyInterface;

/**
 * Creates mock proxies.
 *
 * @internal
 */
class MockProxyFactory implements MockProxyFactoryInterface
{
    /**
     * Get the static instance of this factory.
     *
     * @return MockProxyFactoryInterface The static factory.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Create a new mock proxy.
     *
     * @param MockInterface $mock The mock.
     *
     * @return MockProxyInterface The newly created mock proxy.
     */
    public function create(MockInterface $mock)
    {
        return new MockProxy($mock);
    }

    private static $instance;
}
