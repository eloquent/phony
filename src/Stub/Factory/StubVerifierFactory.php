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

use Eloquent\Phony\Assertion\Recorder\AssertionRecorder;
use Eloquent\Phony\Assertion\Recorder\AssertionRecorderInterface;
use Eloquent\Phony\Assertion\Renderer\AssertionRenderer;
use Eloquent\Phony\Assertion\Renderer\AssertionRendererInterface;
use Eloquent\Phony\Call\Factory\CallVerifierFactory;
use Eloquent\Phony\Call\Factory\CallVerifierFactoryInterface;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Invocation\InvokerInterface;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Factory\MatcherFactoryInterface;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Matcher\Verification\MatcherVerifierInterface;
use Eloquent\Phony\Spy\Factory\SpyFactory;
use Eloquent\Phony\Spy\Factory\SpyFactoryInterface;
use Eloquent\Phony\Spy\SpyInterface;
use Eloquent\Phony\Stub\StubInterface;
use Eloquent\Phony\Stub\StubVerifier;

/**
 * Creates stub verifiers.
 */
class StubVerifierFactory implements StubVerifierFactoryInterface
{
    /**
     * Get the static instance of this factory.
     *
     * @return StubVerifierFactoryInterface The static factory.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Construct a new stub verifier factory.
     *
     * @param StubFactoryInterface|null         $stubFactory         The stub factory to use.
     * @param SpyFactoryInterface|null          $spyFactory          The spy factory to use.
     * @param MatcherFactoryInterface|null      $matcherFactory      The matcher factory to use.
     * @param MatcherVerifierInterface|null     $matcherVerifier     The macther verifier to use.
     * @param CallVerifierFactoryInterface|null $callVerifierFactory The call verifier factory to use.
     * @param AssertionRecorderInterface|null   $assertionRecorder   The assertion recorder to use.
     * @param AssertionRendererInterface|null   $assertionRenderer   The assertion renderer to use.
     * @param InvokerInterface|null             $invoker             The invoker to use.
     */
    public function __construct(
        StubFactoryInterface $stubFactory = null,
        SpyFactoryInterface $spyFactory = null,
        MatcherFactoryInterface $matcherFactory = null,
        MatcherVerifierInterface $matcherVerifier = null,
        CallVerifierFactoryInterface $callVerifierFactory = null,
        AssertionRecorderInterface $assertionRecorder = null,
        AssertionRendererInterface $assertionRenderer = null,
        InvokerInterface $invoker = null
    ) {
        if (null === $stubFactory) {
            $stubFactory = StubFactory::instance();
        }
        if (null === $spyFactory) {
            $spyFactory = SpyFactory::instance();
        }
        if (null === $matcherFactory) {
            $matcherFactory = MatcherFactory::instance();
        }
        if (null === $matcherVerifier) {
            $matcherVerifier = MatcherVerifier::instance();
        }
        if (null === $callVerifierFactory) {
            $callVerifierFactory = CallVerifierFactory::instance();
        }
        if (null === $assertionRecorder) {
            $assertionRecorder = AssertionRecorder::instance();
        }
        if (null === $assertionRenderer) {
            $assertionRenderer = AssertionRenderer::instance();
        }
        if (null === $invoker) {
            $invoker = Invoker::instance();
        }

        $this->stubFactory = $stubFactory;
        $this->spyFactory = $spyFactory;
        $this->matcherFactory = $matcherFactory;
        $this->matcherVerifier = $matcherVerifier;
        $this->callVerifierFactory = $callVerifierFactory;
        $this->assertionRecorder = $assertionRecorder;
        $this->assertionRenderer = $assertionRenderer;
        $this->invoker = $invoker;
    }

    /**
     * Get the stub factory.
     *
     * @return StubFactoryInterface The stub factory.
     */
    public function stubFactory()
    {
        return $this->stubFactory;
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
     * Get the assertion recorder.
     *
     * @return AssertionRecorderInterface The assertion recorder.
     */
    public function assertionRecorder()
    {
        return $this->assertionRecorder;
    }

    /**
     * Get the assertion renderer.
     *
     * @return AssertionRendererInterface The assertion renderer.
     */
    public function assertionRenderer()
    {
        return $this->assertionRenderer;
    }

    /**
     * Get the invoker.
     *
     * @return InvokerInterface The invoker.
     */
    public function invoker()
    {
        return $this->invoker;
    }

    /**
     * Create a new stub verifier.
     *
     * @param StubInterface|null $stub The stub, or null to create an unbound stub verifier.
     * @param SpyInterface|null  $spy  The spy, or null to spy on the supplied stub.
     *
     * @return StubVerifierInterface The newly created stub verifier.
     */
    public function create(StubInterface $stub = null, SpyInterface $spy = null)
    {
        if (null === $stub) {
            $stub = $this->stubFactory->create();
        }
        if (null === $spy) {
            $spy = $this->spyFactory->create($stub);
        }

        return new StubVerifier(
            $stub,
            $spy,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invoker
        );
    }

    /**
     * Create a new stub verifier for the supplied callback.
     *
     * @param callable|null $callback              The callback, or null to create an unbound stub verifier.
     * @param mixed         $self                  The self value.
     * @param callable|null $defaultAnswerCallback The callback to use when creating a default answer.
     * @param boolean|null  $useGeneratorSpies     True if generator spies should be used.
     * @param boolean|null  $useTraversableSpies   True if traversable spies should be used.
     *
     * @return StubVerifierInterface The newly created stub verifier.
     */
    public function createFromCallback(
        $callback = null,
        $self = null,
        $defaultAnswerCallback = null,
        $useGeneratorSpies = null,
        $useTraversableSpies = null
    ) {
        $stub = $this->stubFactory->create($callback, $self, $defaultAnswerCallback);

        return $this->create(
            $stub,
            $this->spyFactory
                ->create($stub, $useGeneratorSpies, $useTraversableSpies)
        );
    }

    private static $instance;
    private $stubFactory;
    private $spyFactory;
    private $matcherFactory;
    private $matcherVerifier;
    private $callVerifierFactory;
    private $assertionRecorder;
    private $assertionRenderer;
    private $invoker;
}
