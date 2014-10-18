<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Proxy;

use BadMethodCallException;
use Eloquent\Phony\Mock\Exception\MockExceptionInterface;
use Eloquent\Phony\Mock\MockInterface;
use Eloquent\Phony\Stub\StubVerifierInterface;

/**
 * The interface implemented by mock proxies.
 */
interface MockProxyInterface
{
    /**
     * Get the mock.
     *
     * @return MockInterface The mock.
     */
    public function mock();

    /**
     * Get the stubs.
     *
     * @return array<string,StubVerifierInterface> The stubs.
     */
    public function stubs();

    /**
     * Turn the mock into a full mock.
     *
     * @return MockProxyInterface This proxy.
     */
    public function full();

    /**
     * Get a stub verifier.
     *
     * @param string $name The method name.
     *
     * @return StubVerifierInterface  The stub verifier.
     * @throws MockExceptionInterface If the stub does not exist.
     */
    public function stub($name);

    /**
     * Get a stub verifier.
     *
     * @param string $name      The method name.
     * @param array  $arguments Ignored.
     *
     * @return StubVerifierInterface  The stub verifier.
     * @throws BadMethodCallException If the stub does not exist.
     */
    public function __call($name, array $arguments);
}
