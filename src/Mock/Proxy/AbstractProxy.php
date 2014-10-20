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

use Eloquent\Phony\Matcher\WildcardMatcher;
use Eloquent\Phony\Matcher\WildcardMatcherInterface;
use Eloquent\Phony\Mock\Exception\MockExceptionInterface;
use Eloquent\Phony\Mock\Exception\UndefinedMethodStubException;
use Eloquent\Phony\Stub\StubVerifierInterface;

/**
 * An abstract base class for implementing proxies.
 *
 * @internal
 */
abstract class AbstractProxy implements ProxyInterface
{
    /**
     * Construct a new proxy.
     *
     * @param string                              $className The class name.
     * @param array<string,StubVerifierInterface> $stubs     The stubs.
     * @param WildcardMatcherInterface|null       $wildcard  The wildcard matcher to use.
     */
    public function __construct(
        $className,
        array $stubs,
        WildcardMatcherInterface $wildcard = null
    ) {
        if (null === $wildcard) {
            $wildcard = WildcardMatcher::instance();
        }

        $this->className = $className;
        $this->stubs = $stubs;
        $this->wildcard = $wildcard;
    }

    /**
     * Get the class name.
     *
     * @return string The class name.
     */
    public function className()
    {
        return $this->className;
    }

    /**
     * Get the stubs.
     *
     * @return array<string,StubVerifierInterface> The stubs.
     */
    public function stubs()
    {
        return $this->stubs;
    }

    /**
     * Get a stub verifier.
     *
     * @param string $name The method name.
     *
     * @return StubVerifierInterface  The stub verifier.
     * @throws MockExceptionInterface If the stub does not exist.
     */
    public function stub($name)
    {
        if (isset($this->stubs[$name])) {
            return $this->stubs[$name];
        }

        throw new UndefinedMethodStubException($this->className, $name);
    }

    protected $className;
    protected $stubs;
    protected $wildcard;
}
