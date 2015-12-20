<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Stub\Factory;

use Eloquent\Phony\Spy\SpyInterface;
use Eloquent\Phony\Stub\StubInterface;
use Eloquent\Phony\Stub\StubVerifierInterface;

/**
 * The interface implemented by stub verifier factories.
 */
interface StubVerifierFactoryInterface
{
    /**
     * Create a new stub verifier.
     *
     * @param StubInterface|null $stub The stub, or null to create an unbound stub verifier.
     * @param SpyInterface|null  $spy  The spy, or null to spy on the supplied stub.
     *
     * @return StubVerifierInterface The newly created stub verifier.
     */
    public function create(
        StubInterface $stub = null,
        SpyInterface $spy = null
    );

    /**
     * Create a new stub verifier for the supplied callback.
     *
     * @param callable|null $callback              The callback, or null to create an unbound stub verifier.
     * @param mixed         $self                  The self value.
     * @param callable|null $defaultAnswerCallback The callback to use when creating a default answer.
     * @param boolean       $useGeneratorSpies     True if generator spies should be used.
     * @param boolean       $useTraversableSpies   True if traversable spies should be used.
     *
     * @return StubVerifierInterface The newly created stub verifier.
     */
    public function createFromCallback(
        $callback = null,
        $self = null,
        $defaultAnswerCallback = null,
        $useGeneratorSpies = true,
        $useTraversableSpies = false
    );
}
