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
use Eloquent\Phony\Spy\SpyVerifierInterface;

/**
 * The interface implemented by spy verifier factories.
 */
interface SpyVerifierFactoryInterface
{
    /**
     * Create a new spy verifier.
     *
     * @param SpyInterface|null $spy The spy, or null to create an unbound spy verifier.
     *
     * @return SpyVerifierInterface The newly created spy verifier.
     */
    public function create(SpyInterface $spy = null);

    /**
     * Create a new spy verifier.
     *
     * @param callable|null $callback The callback, or null to create an unbound spy.
     *
     * @return SpyVerifierInterface The newly created spy verifier.
     */
    public function createFromCallback($callback = null);
}
