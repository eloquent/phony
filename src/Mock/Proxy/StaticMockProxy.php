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
use Eloquent\Phony\Mock\Exception\NonMockClassException;
use Eloquent\Phony\Mock\MockInterface;
use Eloquent\Phony\Stub\StubVerifierInterface;
use ReflectionClass;
use ReflectionException;

/**
 * A proxy for controlling a mock class.
 *
 * @internal
 */
class StaticMockProxy extends AbstractMockProxy implements
    StaticMockProxyInterface
{
    /**
     * Construct a new static mock proxy.
     *
     * @param ReflectionClass|string                   $class The class.
     * @param array<string,StubVerifierInterface>|null $stubs The stubs.
     *
     * @throws MockExceptionInterface If the supplied class name is not a mock class.
     */
    public function __construct($class, array $stubs = null)
    {
        if (!$class instanceof ReflectionClass) {
            try {
                $class = new ReflectionClass($class);
            } catch (ReflectionException $e) {
                throw new NonMockClassException($class, $e);
            }
        }

        $className = $class->getName();

        if (!$class->isSubclassOf('Eloquent\Phony\Mock\MockInterface')) {
            throw new NonMockClassException($className);
        }

        if (null === $stubs) {
            $stubsProperty = $class->getProperty('_staticStubs');
            $stubsProperty->setAccessible(true);
            $stubs = $stubsProperty->getValue(null);
        }

        parent::__construct($className, $stubs);
    }
}
