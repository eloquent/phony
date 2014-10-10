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

use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Factory\MatcherFactoryInterface;
use Eloquent\Phony\Spy\Factory\SpyVerifierFactory;
use Eloquent\Phony\Spy\Factory\SpyVerifierFactoryInterface;
use Eloquent\Phony\Stub\Factory\StubVerifierFactory;
use Eloquent\Phony\Stub\Factory\StubVerifierFactoryInterface;

/**
 * The interface implemented by facade drivers.
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
     * @param SpyVerifierFactoryInterface|null  $spyVerifierFactory  The spy verifier factory to use.
     * @param StubVerifierFactoryInterface|null $stubVerifierFactory The stub verifier factory to use.
     * @param MatcherFactoryInterface|null      $matcherFactory      The matcher factory to use.
     */
    public function __construct(
        SpyVerifierFactoryInterface $spyVerifierFactory = null,
        StubVerifierFactoryInterface $stubVerifierFactory = null,
        MatcherFactoryInterface $matcherFactory = null
    ) {
        if (null === $spyVerifierFactory) {
            $spyVerifierFactory = SpyVerifierFactory::instance();
        }
        if (null === $stubVerifierFactory) {
            $stubVerifierFactory = StubVerifierFactory::instance();
        }
        if (null === $matcherFactory) {
            $matcherFactory = MatcherFactory::instance();
        }

        $this->spyVerifierFactory = $spyVerifierFactory;
        $this->stubVerifierFactory = $stubVerifierFactory;
        $this->matcherFactory = $matcherFactory;
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
     * Get the matcher factory.
     *
     * @return MatcherFactoryInterface The matcher factory.
     */
    public function matcherFactory()
    {
        return $this->matcherFactory;
    }

    private static $instance;
    private $spyVerifierFactory;
    private $stubVerifierFactory;
    private $matcherFactory;
}
