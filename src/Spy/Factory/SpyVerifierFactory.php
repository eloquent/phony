<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Spy\Factory;

use Eloquent\Phony\Assertion\Recorder\AssertionRecorder;
use Eloquent\Phony\Assertion\Recorder\AssertionRecorderInterface;
use Eloquent\Phony\Assertion\Renderer\AssertionRenderer;
use Eloquent\Phony\Assertion\Renderer\AssertionRendererInterface;
use Eloquent\Phony\Call\Factory\CallVerifierFactory;
use Eloquent\Phony\Call\Factory\CallVerifierFactoryInterface;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Invocation\InvocableInspectorInterface;
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
        if (!self::$instance) {
            self::$instance = new self(
                SpyFactory::instance(),
                MatcherFactory::instance(),
                MatcherVerifier::instance(),
                CallVerifierFactory::instance(),
                AssertionRecorder::instance(),
                AssertionRenderer::instance(),
                InvocableInspector::instance()
            );
        }

        return self::$instance;
    }

    /**
     * Construct a new spy verifier factory.
     *
     * @param SpyFactoryInterface          $spyFactory          The spy factory to use.
     * @param MatcherFactoryInterface      $matcherFactory      The matcher factory to use.
     * @param MatcherVerifierInterface     $matcherVerifier     The macther verifier to use.
     * @param CallVerifierFactoryInterface $callVerifierFactory The call verifier factory to use.
     * @param AssertionRecorderInterface   $assertionRecorder   The assertion recorder to use.
     * @param AssertionRendererInterface   $assertionRenderer   The assertion renderer to use.
     * @param InvocableInspectorInterface  $invocableInspector  The invocable inspector to use.
     */
    public function __construct(
        SpyFactoryInterface $spyFactory,
        MatcherFactoryInterface $matcherFactory,
        MatcherVerifierInterface $matcherVerifier,
        CallVerifierFactoryInterface $callVerifierFactory,
        AssertionRecorderInterface $assertionRecorder,
        AssertionRendererInterface $assertionRenderer,
        InvocableInspectorInterface $invocableInspector
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
     * @param SpyInterface|null $spy The spy, or null to create an anonymous spy.
     *
     * @return SpyVerifierInterface The newly created spy verifier.
     */
    public function create(SpyInterface $spy = null)
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
     * @return SpyVerifierInterface The newly created spy verifier.
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
