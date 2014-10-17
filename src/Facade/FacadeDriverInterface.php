<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Facade;

use Eloquent\Phony\Event\Verification\EventOrderVerifierInterface;
use Eloquent\Phony\Matcher\Factory\MatcherFactoryInterface;
use Eloquent\Phony\Mock\Builder\MockBuilderFactoryInterface;
use Eloquent\Phony\Spy\Factory\SpyVerifierFactoryInterface;
use Eloquent\Phony\Stub\Factory\StubVerifierFactoryInterface;

/**
 * The interface implemented by facade drivers.
 */
interface FacadeDriverInterface
{
    /**
     * Get the mock builder factory.
     *
     * @return MockBuilderFactoryInterface The mock builder factory.
     */
    public function mockBuilderFactory();

    /**
     * Get the spy verifier factory.
     *
     * @return SpyVerifierFactoryInterface The spy verifier factory.
     */
    public function spyVerifierFactory();

    /**
     * Get the stub verifier factory.
     *
     * @return StubVerifierFactoryInterface The stub verifier factory.
     */
    public function stubVerifierFactory();

    /**
     * Get the event order verifier.
     *
     * @return EventOrderVerifierInterface The event order verifier.
     */
    public function eventOrderVerifier();

    /**
     * Get the matcher factory.
     *
     * @return MatcherFactoryInterface The matcher factory.
     */
    public function matcherFactory();
}
