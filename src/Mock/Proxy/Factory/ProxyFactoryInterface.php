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

use Eloquent\Phony\Mock\Exception\MockExceptionInterface;
use Eloquent\Phony\Mock\MockInterface;
use Eloquent\Phony\Mock\Proxy\Stubbing\InstanceStubbingProxyInterface;
use Eloquent\Phony\Mock\Proxy\Stubbing\StaticStubbingProxyInterface;
use ReflectionClass;

/**
 * The interface implemented by proxy factories.
 */
interface ProxyFactoryInterface
{
    /**
     * Create a new static stubbing proxy.
     *
     * @param ReflectionClass|object|string $class The class.
     *
     * @return StaticStubbingProxyInterface The newly created proxy.
     * @throws MockExceptionInterface       If the supplied class name is not a mock class.
     */
    public function createStubbingStatic($class);

    /**
     * Create a new stubbing proxy.
     *
     * @param MockInterface $mock The mock.
     *
     * @return InstanceStubbingProxyInterface The newly created proxy.
     */
    public function createStubbing(MockInterface $mock);
}
