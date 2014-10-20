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

use Eloquent\Phony\Mock\Exception\MockExceptionInterface;
use Eloquent\Phony\Spy\SpyInterface;
use Eloquent\Phony\Stub\StubVerifierInterface;
use ReflectionClass;

/**
 * The interface implemented by proxies.
 */
interface ProxyInterface
{
    /**
     * Get the class.
     *
     * @return ReflectionClass The class.
     */
    public function reflector();

    /**
     * Get the class name.
     *
     * @return string The class name.
     */
    public function className();

    /**
     * Get the stubs.
     *
     * @return array<string,SpyInterface> The stubs.
     */
    public function stubs();

    /**
     * Get the magic stubs.
     *
     * @return array<string,SpyInterface> The magic stubs.
     */
    public function magicStubs();

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
     * Get a magic stub verifier.
     *
     * @param string $name The method name.
     *
     * @return StubVerifierInterface  The stub verifier.
     * @throws MockExceptionInterface If magic calls are not supported.
     */
    public function magicStub($name);

    /**
     * Get a stub verifier.
     *
     * @param string $name The method name.
     *
     * @return StubVerifierInterface  The stub verifier.
     * @throws MockExceptionInterface If the stub does not exist.
     */
    public function __get($name);
}
