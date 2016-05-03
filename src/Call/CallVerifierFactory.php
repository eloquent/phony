<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call;

use Eloquent\Phony\Assertion\AssertionRecorder;
use Eloquent\Phony\Assertion\AssertionRenderer;
use Eloquent\Phony\Assertion\ExceptionAssertionRecorder;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Matcher\MatcherFactory;
use Eloquent\Phony\Matcher\MatcherVerifier;
use Eloquent\Phony\Verification\GeneratorVerifierFactory;
use Eloquent\Phony\Verification\TraversableVerifierFactory;

/**
 * Creates call verifiers.
 */
class CallVerifierFactory
{
    /**
     * Get the static instance of this factory.
     *
     * @return CallVerifierFactory The static factory.
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self(
                MatcherFactory::instance(),
                MatcherVerifier::instance(),
                GeneratorVerifierFactory::instance(),
                TraversableVerifierFactory::instance(),
                ExceptionAssertionRecorder::instance(),
                AssertionRenderer::instance(),
                InvocableInspector::instance()
            );
        }

        return self::$instance;
    }

    /**
     * Construct a new call verifier factory.
     *
     * @param MatcherFactory             $matcherFactory             The matcher factory to use.
     * @param MatcherVerifier            $matcherVerifier            The macther verifier to use.
     * @param GeneratorVerifierFactory   $generatorVerifierFactory   The generator verifier factory to use.
     * @param TraversableVerifierFactory $traversableVerifierFactory The traversable verifier factory to use.
     * @param AssertionRecorder          $assertionRecorder          The assertion recorder to use.
     * @param AssertionRenderer          $assertionRenderer          The assertion renderer to use.
     * @param InvocableInspector         $invocableInspector         The invocable inspector to use.
     */
    public function __construct(
        MatcherFactory $matcherFactory,
        MatcherVerifier $matcherVerifier,
        GeneratorVerifierFactory $generatorVerifierFactory,
        TraversableVerifierFactory $traversableVerifierFactory,
        AssertionRecorder $assertionRecorder,
        AssertionRenderer $assertionRenderer,
        InvocableInspector $invocableInspector
    ) {
        $this->matcherFactory = $matcherFactory;
        $this->matcherVerifier = $matcherVerifier;
        $this->generatorVerifierFactory = $generatorVerifierFactory;
        $this->traversableVerifierFactory = $traversableVerifierFactory;
        $this->assertionRecorder = $assertionRecorder;
        $this->assertionRenderer = $assertionRenderer;
        $this->invocableInspector = $invocableInspector;
    }

    /**
     * Wrap the supplied call in a verifier.
     *
     * @param Call $call The call.
     *
     * @return CallVerifier The call verifier.
     */
    public function fromCall(Call $call)
    {
        return new CallVerifier(
            $call,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->generatorVerifierFactory,
            $this->traversableVerifierFactory,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );
    }

    /**
     * Wrap the supplied calls in verifiers.
     *
     * @param array<Call> $calls The calls.
     *
     * @return array<CallVerifier> The call verifiers.
     */
    public function fromCalls(array $calls)
    {
        $verifiers = array();

        foreach ($calls as $call) {
            $verifiers[] = new CallVerifier(
            $call,
                $this->matcherFactory,
                $this->matcherVerifier,
                $this->generatorVerifierFactory,
                $this->traversableVerifierFactory,
                $this->assertionRecorder,
                $this->assertionRenderer,
                $this->invocableInspector
            );
        }

        return $verifiers;
    }

    private static $instance;
    private $matcherFactory;
    private $matcherVerifier;
    private $generatorVerifierFactory;
    private $traversableVerifierFactory;
    private $assertionRecorder;
    private $assertionRenderer;
    private $invocableInspector;
}
