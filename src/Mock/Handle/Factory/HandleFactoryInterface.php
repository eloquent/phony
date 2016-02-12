<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Handle\Factory;

use Eloquent\Phony\Mock\Exception\MockExceptionInterface;
use Eloquent\Phony\Mock\Handle\HandleInterface;
use Eloquent\Phony\Mock\Handle\InstanceHandleInterface;
use Eloquent\Phony\Mock\Handle\Stubbing\InstanceStubbingHandleInterface;
use Eloquent\Phony\Mock\Handle\Stubbing\StaticStubbingHandleInterface;
use Eloquent\Phony\Mock\Handle\Verification\InstanceVerificationHandleInterface;
use Eloquent\Phony\Mock\Handle\Verification\StaticVerificationHandleInterface;
use Eloquent\Phony\Mock\MockInterface;
use ReflectionClass;

/**
 * The interface implemented by handle factories.
 */
interface HandleFactoryInterface
{
    /**
     * Create a new stubbing handle.
     *
     * @param MockInterface|InstanceHandleInterface $mock  The mock.
     * @param string|null                           $label The label.
     *
     * @return InstanceStubbingHandleInterface The newly created handle.
     * @throws MockExceptionInterface          If the supplied mock is invalid.
     */
    public function createStubbing($mock, $label = null);

    /**
     * Create a new verification handle.
     *
     * @param MockInterface|InstanceHandleInterface $mock The mock.
     *
     * @return InstanceVerificationHandleInterface The newly created handle.
     * @throws MockExceptionInterface              If the supplied mock is invalid.
     */
    public function createVerification($mock);

    /**
     * Create a new static verification handle.
     *
     * @param MockInterface|HandleInterface|ReflectionClass|string $class The class.
     *
     * @return StaticVerificationHandleInterface The newly created handle.
     * @throws MockExceptionInterface            If the supplied class name is not a mock class.
     */
    public function createVerificationStatic($class);

    /**
     * Create a new static stubbing handle.
     *
     * @param MockInterface|HandleInterface|ReflectionClass|string $class The class.
     *
     * @return StaticStubbingHandleInterface The newly created handle.
     * @throws MockExceptionInterface        If the supplied class name is not a mock class.
     */
    public function createStubbingStatic($class);
}
