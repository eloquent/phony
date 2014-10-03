<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Stub\Factory;

use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Factory\MatcherFactoryInterface;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Matcher\Verification\MatcherVerifierInterface;
use Eloquent\Phony\Stub\Stub;

/**
 * Creates stubs.
 *
 * @internal
 */
class StubFactory implements StubFactoryInterface
{
    /**
     * Get the static instance of this factory.
     *
     * @return StubFactoryInterface The static factory.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Construct a new stub factory.
     *
     * @param MatcherFactoryInterface|null  $matcherFactory  The matcher factory to use.
     * @param MatcherVerifierInterface|null $matcherVerifier The matcher verifier to use.
     */
    public function __construct(
        MatcherFactoryInterface $matcherFactory = null,
        MatcherVerifierInterface $matcherVerifier = null
    ) {
        if (null === $matcherFactory) {
            $matcherFactory = MatcherFactory::instance();
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
     * Create a new stub.
     *
     * @param callable|null $callback  The callback, or null to create an unbound stub.
     * @param object|null   $thisValue The $this value.
     *
     * @return StubInterface The newly created stub.
     */
    public function create($callback = null, $thisValue = null)
    {
        return new Stub(
            $callback,
            $thisValue,
            $this->matcherFactory,
            $this->matcherVerifier
        );
    }

    private static $instance;
    private $matcherFactory;
    private $matcherVerifier;
}
