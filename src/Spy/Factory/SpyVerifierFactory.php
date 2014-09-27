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

use Eloquent\Phony\Call\Factory\CallVerifierFactory;
use Eloquent\Phony\Call\Factory\CallVerifierFactoryInterface;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Factory\MatcherFactoryInterface;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Matcher\Verification\MatcherVerifierInterface;
use Eloquent\Phony\Spy\SpyInterface;
use Eloquent\Phony\Spy\SpyVerifier;
use Eloquent\Phony\Spy\SpyVerifierInterface;

/**
 * Creates spy verifiers.
 */
class SpyVerifierFactory implements SpyVerifierFactoryInterface
{
    /**
     * Get the static instance of this factory.
     *
     * @return SpyVerifierFactoryInterface The static factory.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Construct a new spy verifier factory.
     *
     * @param MatcherFactoryInterface|null      $matcherFactory      The matcher factory to use.
     * @param MatcherVerifierInterface|null     $matcherVerifier     The macther verifier to use.
     * @param CallVerifierFactoryInterface|null $callVerifierFactory The call verifier factory to use.
     * @param SpyFactoryInterface|null          $spyFactory          The spy factory to use.
     */
    public function __construct(
        MatcherFactoryInterface $matcherFactory = null,
        MatcherVerifierInterface $matcherVerifier = null,
        CallVerifierFactoryInterface $callVerifierFactory = null,
        SpyFactoryInterface $spyFactory = null
    ) {
        if (null === $matcherFactory) {
            $matcherFactory = MatcherFactory::instance();
        }
        if (null === $matcherVerifier) {
            $matcherVerifier = MatcherVerifier::instance();
        }
        if (null === $callVerifierFactory) {
            $callVerifierFactory = CallVerifierFactory::instance();
        }
        if (null === $spyFactory) {
            $spyFactory = SpyFactory::instance();
        }

        $this->matcherFactory = $matcherFactory;
        $this->matcherVerifier = $matcherVerifier;
        $this->callVerifierFactory = $callVerifierFactory;
        $this->spyFactory = $spyFactory;
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

    /**
     * Get the matcher verifier.
     *
     * @return MatcherVerifierInterface The matcher verifier.
     */
    public function matcherVerifier()
    {
        return $this->matcherVerifier;
    }

    /**
     * Get the call verifier factory.
     *
     * @return CallVerifierFactoryInterface The call verifier factory.
     */
    public function callVerifierFactory()
    {
        return $this->callVerifierFactory;
    }

    /**
     * Get the spy factory.
     *
     * @return SpyFactoryInterface The spy factory.
     */
    public function spyFactory()
    {
        return $this->spyFactory;
    }

    /**
     * Create a new spy verifier.
     *
     * @param SpyInterface|null $spy The spy, or null to create an unbound spy verifier.
     *
     * @return SpyVerifierInterface The newly created spy verifier.
     */
    public function create(SpyInterface $spy = null)
    {
        if (null === $spy) {
            $spy = $this->spyFactory->create();
        }

        return new SpyVerifier(
            $spy,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->callVerifierFactory
        );
    }

    /**
     * Create a new spy verifier.
     *
     * @param callable|null $subject The subject, or null to create an unbound spy.
     *
     * @return SpyVerifierInterface The newly created spy verifier.
     */
    public function createFromSubject($subject = null)
    {
        return $this->create($this->spyFactory->create($subject));
    }

    private static $instance;
    private $matcherFactory;
    private $matcherVerifier;
    private $callVerifierFactory;
    private $spyFactory;
}
