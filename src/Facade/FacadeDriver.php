<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Facade;

use Eloquent\Phony\Event\Verification\EventOrderVerifier;
use Eloquent\Phony\Event\Verification\EventOrderVerifierInterface;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Factory\MatcherFactoryInterface;
use Eloquent\Phony\Mock\Builder\Factory\MockBuilderFactory;
use Eloquent\Phony\Mock\Builder\Factory\MockBuilderFactoryInterface;
use Eloquent\Phony\Mock\Proxy\Factory\ProxyFactory;
use Eloquent\Phony\Mock\Proxy\Factory\ProxyFactoryInterface;
use Eloquent\Phony\Spy\Factory\SpyVerifierFactory;
use Eloquent\Phony\Spy\Factory\SpyVerifierFactoryInterface;
use Eloquent\Phony\Stub\Factory\StubVerifierFactory;
use Eloquent\Phony\Stub\Factory\StubVerifierFactoryInterface;

/**
 * A service container that supplies all of the underlying services required by
 * the facades.
 *
 * @internal
 */
class FacadeDriver implements FacadeDriverInterface
{
    /**
     * Get the static instance of this driver.
     *
     * @return FacadeDriverInterface The static driver.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Construct a new facade driver.
     *
     * @param MockBuilderFactoryInterface|null  $mockBuilderFactory  The mock builder factory to use.
     * @param ProxyFactoryInterface|null        $proxyFactory        The proxy factory to use.
     * @param SpyVerifierFactoryInterface|null  $spyVerifierFactory  The spy verifier factory to use.
     * @param StubVerifierFactoryInterface|null $stubVerifierFactory The stub verifier factory to use.
     * @param EventOrderVerifierInterface|null  $eventOrderVerifier  The event order verifier to use.
     * @param MatcherFactoryInterface|null      $matcherFactory      The matcher factory to use.
     */
    public function __construct(
        MockBuilderFactoryInterface $mockBuilderFactory = null,
        ProxyFactoryInterface $proxyFactory = null,
        SpyVerifierFactoryInterface $spyVerifierFactory = null,
        StubVerifierFactoryInterface $stubVerifierFactory = null,
        EventOrderVerifierInterface $eventOrderVerifier = null,
        MatcherFactoryInterface $matcherFactory = null
    ) {
        if (null === $mockBuilderFactory) {
            $mockBuilderFactory = MockBuilderFactory::instance();
        }
        if (null === $proxyFactory) {
            $proxyFactory = ProxyFactory::instance();
        }
        if (null === $spyVerifierFactory) {
            $spyVerifierFactory = SpyVerifierFactory::instance();
        }
        if (null === $stubVerifierFactory) {
            $stubVerifierFactory = StubVerifierFactory::instance();
        }
        if (null === $eventOrderVerifier) {
            $eventOrderVerifier = EventOrderVerifier::instance();
        }
        if (null === $matcherFactory) {
            $matcherFactory = MatcherFactory::instance();
        }

        $this->mockBuilderFactory = $mockBuilderFactory;
        $this->proxyFactory = $proxyFactory;
        $this->spyVerifierFactory = $spyVerifierFactory;
        $this->stubVerifierFactory = $stubVerifierFactory;
        $this->eventOrderVerifier = $eventOrderVerifier;
        $this->matcherFactory = $matcherFactory;
    }

    /**
     * Get the mock builder factory.
     *
     * @return MockBuilderFactoryInterface The mock builder factory.
     */
    public function mockBuilderFactory()
    {
        return $this->mockBuilderFactory;
    }

    /**
     * Get the proxy factory.
     *
     * @return ProxyFactoryInterface The proxy factory.
     */
    public function proxyFactory()
    {
        return $this->proxyFactory;
    }

    /**
     * Get the spy verifier factory.
     *
     * @return SpyVerifierFactoryInterface The spy verifier factory.
     */
    public function spyVerifierFactory()
    {
        return $this->spyVerifierFactory;
    }

    /**
     * Get the stub verifier factory.
     *
     * @return StubVerifierFactoryInterface The stub verifier factory.
     */
    public function stubVerifierFactory()
    {
        return $this->stubVerifierFactory;
    }

    /**
     * Get the event order verifier.
     *
     * @return EventOrderVerifierInterface The event order verifier.
     */
    public function eventOrderVerifier()
    {
        return $this->eventOrderVerifier;
    }

    /**
     * Get the matcher factory.
     *
     * @return MatcherFactoryInterface The matcher factory.
     */
    public function matcherFactory()
    {
        return $this->matcherFactory;
    }

    private static $instance;
    private $mockBuilderFactory;
    private $proxyFactory;
    private $spyVerifierFactory;
    private $stubVerifierFactory;
    private $eventOrderVerifier;
    private $matcherFactory;
}
