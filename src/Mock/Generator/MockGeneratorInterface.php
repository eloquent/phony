<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Generator;

use Eloquent\Phony\Mock\Builder\MockBuilderInterface;

/**
 * The interface implemented by mock generators.
 */
interface MockGeneratorInterface
{
    /**
     * Generate a mock class and return the source code.
     *
     * @param MockBuilderInterface $builder The builder.
     *
     * @return string The source code.
     */
    public function generate(MockBuilderInterface $builder);
}
