<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Factory;

use Eloquent\Phony\Mock\Builder\MockBuilderInterface;
use Eloquent\Phony\Mock\MockInterface;
use Eloquent\Phony\Stub\StubInterface;

/**
 * The interface implemented by mock factories.
 */
interface MockFactoryInterface
{
    /**
     * Create a new mock instance for the supplied builder.
     *
     * @param MockBuilderInterface $builder The builder.
     *
     * @return MockInterface The newly created mock.
     */
    public function createMock(MockBuilderInterface $builder);

    /**
     * Create static stubs for the supplied builder.
     *
     * @param MockBuilderInterface $builder The builder.
     *
     * @return array<string,StubInterface> The stubs.
     */
    public function createStaticStubs(MockBuilderInterface $builder);

    /**
     * Create the stubs for a regular mock.
     *
     * @param MockBuilderInterface $builder The builder.
     *
     * @return array<string,StubInterface> The stubs.
     */
    public function createStubsForMock(MockBuilderInterface $builder);
}
