<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Spy\Factory;

use Eloquent\Phony\Spy\SpyInterface;

/**
 * The interface implemented by spy factories.
 */
interface SpyFactoryInterface
{
    /**
     * Create a new spy.
     *
     * @param callable|null $callback            The callback, or null to create an unbound spy.
     * @param boolean|null  $useGeneratorSpies   True if generator spies should be used.
     * @param boolean|null  $useTraversableSpies True if traversable spies should be used.
     *
     * @return SpyInterface The newly created spy.
     */
    public function create(
        $callback = null,
        $useGeneratorSpies = null,
        $useTraversableSpies = null
    );
}
