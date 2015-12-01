<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Proxy;

use Eloquent\Phony\Event\EventCollectionInterface;
use Eloquent\Phony\Mock\Exception\MockExceptionInterface;
use Eloquent\Phony\Stub\StubVerifierInterface;
use Exception;
use ReflectionClass;
use stdClass;

/**
 * The interface implemented by proxies.
 *
 * @api
 */
interface ProxyInterface
{
    /**
     * Get the class.
     *
     * @api
     *
     * @return ReflectionClass The class.
     */
    public function clazz();

    /**
     * Get the class name.
     *
     * @api
     *
     * @return string The class name.
     */
    public function className();

    /**
     * Turn the mock into a full mock.
     *
     * @api
     *
     * @return $this This proxy.
     */
    public function full();

    /**
     * Turn the mock into a partial mock.
     *
     * @api
     *
     * @return $this This proxy.
     */
    public function partial();

    /**
     * Returns true if the mock is a full mock.
     *
     * @api
     *
     * @return boolean True if the mock is a full mock.
     */
    public function isFull();

    /**
     * Get a stub verifier.
     *
     * @api
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
     * @api
     *
     * @param string $name The method name.
     *
     * @return StubVerifierInterface  The stub verifier.
     * @throws MockExceptionInterface If the stub does not exist.
     */
    public function __get($name);

    /**
     * Checks if there was no interaction with the mock.
     *
     * @api
     *
     * @return EventCollectionInterface|null The result.
     */
    public function checkNoInteraction();

    /**
     * Throws an exception unless there was no interaction with the mock.
     *
     * @api
     *
     * @return EventCollectionInterface The result.
     * @throws Exception                If the assertion fails, and the assertion recorder throws exceptions.
     */
    public function noInteraction();

    /**
     * Reset the mock to its initial state.
     *
     * @api
     *
     * @return $this This proxy.
     */
    public function reset();

    /**
     * Get the stubs.
     *
     * @return stdClass The stubs.
     */
    public function stubs();

    /**
     * Get a spy.
     *
     * @param string $name The method name.
     *
     * @return SpyInterface           The stub.
     * @throws MockExceptionInterface If the spy does not exist.
     */
    public function spy($name);

    /**
     * Get the proxy state.
     *
     * @return stdClass The state.
     */
    public function state();
}
