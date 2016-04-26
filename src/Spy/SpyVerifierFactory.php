<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Spy;

use Eloquent\Phony\Assertion\AssertionRecorder;
use Eloquent\Phony\Assertion\AssertionRenderer;
use Eloquent\Phony\Assertion\ExceptionAssertionRecorder;
use Eloquent\Phony\Call\CallVerifierFactory;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Matcher\MatcherFactory;
use Eloquent\Phony\Matcher\MatcherVerifier;

/**
 * Creates spy verifiers.
 */
class SpyVerifierFactory
{
    /**
     * Get the static instance of this factory.
     *
     * @return SpyVerifierFactory The static factory.
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self(
                SpyFactory::instance(),
                MatcherFactory::instance(),
                MatcherVerifier::instance(),
                CallVerifierFactory::instance(),
                ExceptionAssertionRecorder::instance(),
                AssertionRenderer::instance(),
                InvocableInspector::instance()
            );
        }

        return self::$instance;
    }

    /**
     * Construct a new spy verifier factory.
     *
     * @param SpyFactory          $spyFactory          The spy factory to use.
     * @param MatcherFactory      $matcherFactory      The matcher factory to use.
     * @param MatcherVerifier     $matcherVerifier     The macther verifier to use.
     * @param CallVerifierFactory $callVerifierFactory The call verifier factory to use.
     * @param AssertionRecorder   $assertionRecorder   The assertion recorder to use.
     * @param AssertionRenderer   $assertionRenderer   The assertion renderer to use.
     * @param InvocableInspector  $invocableInspector  The invocable inspector to use.
     */
    public function __construct(
        SpyFactory $spyFactory,
        MatcherFactory $matcherFactory,
        MatcherVerifier $matcherVerifier,
        CallVerifierFactory $callVerifierFactory,
        AssertionRecorder $assertionRecorder,
        AssertionRenderer $assertionRenderer,
        InvocableInspector $invocableInspector
    ) {
        $this->spyFactory = $spyFactory;
        $this->matcherFactory = $matcherFactory;
        $this->matcherVerifier = $matcherVerifier;
        $this->callVerifierFactory = $callVerifierFactory;
        $this->assertionRecorder = $assertionRecorder;
        $this->assertionRenderer = $assertionRenderer;
        $this->invocableInspector = $invocableInspector;
    }

    /**
     * Create a new spy verifier.
     *
     * @param Spy|null $spy The spy, or null to create an anonymous spy.
     *
     * @return SpyVerifier The newly created spy verifier.
     */
    public function create(Spy $spy = null)
    {
        if (!$spy) {
            $spy = $this->spyFactory->create();
        }

        return new SpyVerifier(
            $spy,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );
    }

    /**
     * Create a new spy verifier for the supplied callback.
     *
     * @param callable|null $callback The callback, or null to create an anonymous spy.
     *
     * @return SpyVerifier The newly created spy verifier.
     */
    public function createFromCallback($callback = null)
    {
        return new SpyVerifier(
            $this->spyFactory->create($callback),
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );
    }

    private static $instance;
    private $spyFactory;
    private $matcherFactory;
    private $matcherVerifier;
    private $callVerifierFactory;
    private $assertionRecorder;
    private $assertionRenderer;
    private $invocableInspector;
}
