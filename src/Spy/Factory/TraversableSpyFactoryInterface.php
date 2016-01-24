<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Spy\Factory;

use Eloquent\Phony\Call\CallInterface;
use InvalidArgumentException;
use Traversable;

/**
 * The interface implemented by traversable spy factories.
 */
interface TraversableSpyFactoryInterface
{
    /**
     * Create a new traversable spy.
     *
     * @param CallInterface     $call        The call from which the traversable originated.
     * @param Traversable|array $traversable The traversable.
     *
     * @return Traversable              The newly created traversable spy.
     * @throws InvalidArgumentException If the supplied traversable is invalid.
     */
    public function create(CallInterface $call, $traversable);
}
