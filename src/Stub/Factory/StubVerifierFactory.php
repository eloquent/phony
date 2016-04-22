<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Stub\Factory;

use Eloquent\Phony\Assertion\AssertionRecorder;
use Eloquent\Phony\Assertion\AssertionRenderer;
use Eloquent\Phony\Assertion\ExceptionAssertionRecorder;
use Eloquent\Phony\Call\Factory\CallVerifierFactory;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Spy\Factory\SpyFactory;
use Eloquent\Phony\Spy\Spy;
use Eloquent\Phony\Stub\Answer\Builder\Factory\GeneratorAnswerBuilderFactory;
use Eloquent\Phony\Stub\Stub;
use Eloquent\Phony\Stub\StubVerifier;

/**
 * Creates stub verifiers.
 */
class StubVerifierFactory
{
    /**
     * Get the static instance of this factory.
     *
     * @return StubVerifierFactory The static factory.
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self(
                StubFactory::instance(),
                SpyFactory::instance(),
                MatcherFactory::instance(),
                MatcherVerifier::instance(),
                CallVerifierFactory::instance(),
                ExceptionAssertionRecorder::instance(),
                AssertionRenderer::instance(),
                InvocableInspector::instance(),
                Invoker::instance(),
                GeneratorAnswerBuilderFactory::instance()
            );
        }

        return self::$instance;
    }

    /**
     * Construct a new stub verifier factory.
     *
     * @param StubFactory                   $stubFactory                   The stub factory to use.
     * @param SpyFactory                    $spyFactory                    The spy factory to use.
     * @param MatcherFactory                $matcherFactory                The matcher factory to use.
     * @param MatcherVerifier               $matcherVerifier               The macther verifier to use.
     * @param CallVerifierFactory           $callVerifierFactory           The call verifier factory to use.
     * @param AssertionRecorder             $assertionRecorder             The assertion recorder to use.
     * @param AssertionRenderer             $assertionRenderer             The assertion renderer to use.
     * @param InvocableInspector            $invocableInspector            The invocable inspector to use.
     * @param Invoker                       $invoker                       The invoker to use.
     * @param GeneratorAnswerBuilderFactory $generatorAnswerBuilderFactory The generator answer builder factory to use.
     */
    public function __construct(
        StubFactory $stubFactory,
        SpyFactory $spyFactory,
        MatcherFactory $matcherFactory,
        MatcherVerifier $matcherVerifier,
        CallVerifierFactory $callVerifierFactory,
        AssertionRecorder $assertionRecorder,
        AssertionRenderer $assertionRenderer,
        InvocableInspector $invocableInspector,
        Invoker $invoker,
        GeneratorAnswerBuilderFactory $generatorAnswerBuilderFactory
    ) {
        $this->stubFactory = $stubFactory;
        $this->spyFactory = $spyFactory;
        $this->matcherFactory = $matcherFactory;
        $this->matcherVerifier = $matcherVerifier;
        $this->callVerifierFactory = $callVerifierFactory;
        $this->assertionRecorder = $assertionRecorder;
        $this->assertionRenderer = $assertionRenderer;
        $this->invocableInspector = $invocableInspector;
        $this->invoker = $invoker;
        $this->generatorAnswerBuilderFactory = $generatorAnswerBuilderFactory;
    }

    /**
     * Create a new stub verifier.
     *
     * @param Stub|null $stub The stub, or null to create an anonymous stub.
     * @param Spy|null  $spy  The spy, or null to spy on the supplied stub.
     *
     * @return StubVerifier The newly created stub verifier.
     */
    public function create(Stub $stub = null, Spy $spy = null)
    {
        if (!$stub) {
            $stub = $this->stubFactory->create();
        }
        if (!$spy) {
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
            $this->invocableInspector,
            $this->invoker,
            $this->generatorAnswerBuilderFactory
        );
    }

    /**
     * Create a new stub verifier for the supplied callback.
     *
     * @param callable|null $callback The callback, or null to create an anonymous stub.
     *
     * @return StubVerifier The newly created stub verifier.
     */
    public function createFromCallback($callback = null)
    {
        $stub = $this->stubFactory->create($callback);

        return new StubVerifier(
            $stub,
            $this->spyFactory->create($stub),
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->callVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector,
            $this->invoker,
            $this->generatorAnswerBuilderFactory
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
    private $invocableInspector;
    private $invoker;
    private $generatorAnswerBuilderFactory;
}
