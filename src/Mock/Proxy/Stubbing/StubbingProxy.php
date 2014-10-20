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
use Eloquent\Phony\Mock\Factory\MockFactoryInterface;
use Eloquent\Phony\Mock\MockInterface;
use Eloquent\Phony\Spy\SpyInterface;
use Eloquent\Phony\Stub\Factory\StubVerifierFactoryInterface;
use ReflectionClass;
use ReflectionProperty;

/**
 * A proxy for stubbing a mock.
 *
 * @internal
 */
class StubbingProxy extends AbstractStubbingProxy implements
    InstanceStubbingProxyInterface
{
    /**
     * Construct a new stubbing proxy.
     *
     * @param MockInterface                     $mock                The mock.
     * @param ReflectionClass                   $class               The class.
     * @param array<string,SpyInterface>        $stubs               The stubs.
     * @param ReflectionProperty|null           $magicStubsProperty  The magic stubs property, or null if magic is not available.
     * @param MockFactoryInterface|null         $mockFactory         The mock factory to use.
     * @param StubVerifierFactoryInterface|null $stubVerifierFactory The stub verifier factory to use.
     * @param WildcardMatcherInterface|null     $wildcard            The wildcard matcher to use.
     */
    public function __construct(
        MockInterface $mock,
        ReflectionClass $class,
        array $stubs,
        ReflectionProperty $magicStubsProperty = null,
        MockFactoryInterface $mockFactory = null,
        StubVerifierFactoryInterface $stubVerifierFactory = null,
        WildcardMatcherInterface $wildcard = null
    ) {
        parent::__construct(
            $class,
            $stubs,
            $magicStubsProperty,
            $mockFactory,
            $stubVerifierFactory,
            $wildcard
        );

        $this->mock = $mock;
    }

    /**
     * Get the mock.
     *
     * @return MockInterface The mock.
     */
    public function mock()
    {
        return $this->mock;
    }
}
