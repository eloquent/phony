<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Factory;

use Eloquent\Phony\Call\CallInterface;
use Eloquent\Phony\Call\CallVerifier;
use Eloquent\Phony\Call\CallVerifierInterface;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Factory\MatcherFactoryInterface;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Matcher\Verification\MatcherVerifierInterface;

/**
 * Creates call verifiers.
 */
class CallVerifierFactory implements CallVerifierFactoryInterface
{
    /**
     * Get the static instance of this factory.
     *
     * @return CallVerifierFactoryInterface The static factory.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Construct a new call verifier factory.
     *
     * @param MatcherFactoryInterface|null  $matcherFactory  The matcher factory to use.
     * @param MatcherVerifierInterface|null $matcherVerifier The macther verifier to use.
     */
    public function __construct(
        MatcherFactoryInterface $matcherFactory = null,
        MatcherVerifierInterface $matcherVerifier = null
    ) {
        if (null === $matcherFactory) {
            $matcherFactory = new MatcherFactory();
        }
        if (null === $matcherVerifier) {
            $matcherVerifier = MatcherVerifier::instance();
        }

        $this->matcherFactory = $matcherFactory;
        $this->matcherVerifier = $matcherVerifier;
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
     * Wrap the supplied call in a verifier, or return unchanged if already
     * wrapped.
     *
     * @param CallInterface|CallVerifierInterface $call The call.
     *
     * @return CallVerifierInterface The call verifier.
     */
    public function adapt($call)
    {
        if ($call instanceof CallVerifierInterface) {
            return $call;
        }

        return new CallVerifier(
            $call,
            $this->matcherFactory,
            $this->matcherVerifier
        );
    }

    /**
     * Wrap the supplied calls in verifiers, or return unchanged if already
     * wrapped.
     *
     * @param array<integer,CallInterface|CallVerifierInterface> $calls The calls.
     *
     * @return array<integer,CallVerifierInterface> The call verifiers.
     */
    public function adaptAll(array $calls)
    {
        $verifiers = array();
        foreach ($calls as $call) {
            $verifiers[] = $this->adapt($call);
        }

        return $verifiers;
    }

    private static $instance;
    private $matcherFactory;
    private $matcherVerifier;
}
