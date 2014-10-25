<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Proxy\Stubbing;

use Eloquent\Phony\Matcher\WildcardMatcherInterface;
use Eloquent\Phony\Mock\Exception\MockExceptionInterface;
use Eloquent\Phony\Mock\Exception\UndefinedMethodStubException;
use Eloquent\Phony\Mock\Factory\MockFactoryInterface;
use Eloquent\Phony\Mock\Proxy\AbstractProxy;
use Eloquent\Phony\Mock\Proxy\Exception\UndefinedMethodException;
use Eloquent\Phony\Stub\Factory\StubVerifierFactoryInterface;
use Eloquent\Phony\Stub\StubInterface;
use Eloquent\Phony\Stub\StubVerifierInterface;
use ReflectionClass;
use ReflectionProperty;

/**
 * An abstract base class for implementing stubbing proxies.
 *
 * @internal
 */
abstract class AbstractStubbingProxy extends AbstractProxy implements
    StubbingProxyInterface
{
    /**
     * Construct a new stubbing proxy.
     *
     * @param ReflectionClass                   $class               The class.
     * @param array<string,StubInterface>       $stubs               The stubs.
     * @param ReflectionProperty                $isFullMockProperty  The is full mock property.
     * @param ReflectionProperty|null           $magicStubsProperty  The is full mock property, or null if magic is not available.
     * @param MockFactoryInterface|null         $mockFactory         The mock factory to use.
     * @param StubVerifierFactoryInterface|null $stubVerifierFactory The stub verifier factory to use.
     * @param WildcardMatcherInterface|null     $wildcardMatcher     The wildcard matcher to use.
     */
    public function __construct(
        ReflectionClass $class,
        array $stubs,
        ReflectionProperty $isFullMockProperty,
        ReflectionProperty $magicStubsProperty = null,
        MockFactoryInterface $mockFactory = null,
        StubVerifierFactoryInterface $stubVerifierFactory = null,
        WildcardMatcherInterface $wildcardMatcher = null
    ) {
        parent::__construct(
            $class,
            $stubs,
            $magicStubsProperty,
            $mockFactory,
            $stubVerifierFactory,
            $wildcardMatcher
        );

        $this->isFullMockProperty = $isFullMockProperty;
    }

    /**
     * Get the is full mock property.
     *
     * @return ReflectionProperty The is full mock property.
     */
    public function isFullMockProperty()
    {
        return $this->isFullMockProperty;
    }

    /**
     * Turn the mock into a full mock.
     *
     * @return StubbingProxyInterface This proxy.
     */
    public function full()
    {
        $this->isFullMockProperty->setValue($this->mock, true);

        foreach ($this->stubs as $stub) {
            $stub->callback()->with($this->wildcardMatcher)->returns()
                ->with($this->wildcardMatcher);
        }

        return $this;
    }

    /**
     * Get a stub verifier, and modify its current criteria to match the
     * supplied arguments.
     *
     * @param string               $name      The method name.
     * @param array<integer,mixed> $arguments The arguments.
     *
     * @return StubVerifierInterface  The stub verifier.
     * @throws MockExceptionInterface If the stub does not exist.
     */
    public function __call($name, array $arguments)
    {
        try {
            $stub = $this->stub($name);
        } catch (UndefinedMethodStubException $e) {
            throw new UndefinedMethodException(get_called_class(), $name, $e);
        }

        return call_user_func_array(array($stub, 'with'), $arguments);
    }

    private $isFullMockProperty;
}
