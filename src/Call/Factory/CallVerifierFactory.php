<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Factory;

use Eloquent\Phony\Assertion\Recorder\AssertionRecorder;
use Eloquent\Phony\Assertion\Recorder\AssertionRecorderInterface;
use Eloquent\Phony\Assertion\Renderer\AssertionRenderer;
use Eloquent\Phony\Assertion\Renderer\AssertionRendererInterface;
use Eloquent\Phony\Call\CallInterface;
use Eloquent\Phony\Call\CallVerifier;
use Eloquent\Phony\Call\CallVerifierInterface;
use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Invocation\InvocableInspectorInterface;
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
        if (!self::$instance) {
            self::$instance = new self(
                MatcherFactory::instance(),
                MatcherVerifier::instance(),
                AssertionRecorder::instance(),
                AssertionRenderer::instance(),
                InvocableInspector::instance()
            );
        }

        return self::$instance;
    }

    /**
     * Construct a new call verifier factory.
     *
     * @param MatcherFactoryInterface     $matcherFactory     The matcher factory to use.
     * @param MatcherVerifierInterface    $matcherVerifier    The macther verifier to use.
     * @param AssertionRecorderInterface  $assertionRecorder  The assertion recorder to use.
     * @param AssertionRendererInterface  $assertionRenderer  The assertion renderer to use.
     * @param InvocableInspectorInterface $invocableInspector The invocable inspector to use.
     */
    public function __construct(
        MatcherFactoryInterface $matcherFactory,
        MatcherVerifierInterface $matcherVerifier,
        AssertionRecorderInterface $assertionRecorder,
        AssertionRendererInterface $assertionRenderer,
        InvocableInspectorInterface $invocableInspector
    ) {
        $this->matcherFactory = $matcherFactory;
        $this->matcherVerifier = $matcherVerifier;
        $this->assertionRecorder = $assertionRecorder;
        $this->assertionRenderer = $assertionRenderer;
        $this->invocableInspector = $invocableInspector;
    }

    /**
     * Wrap the supplied call in a verifier.
     *
     * @param CallInterface $call The call.
     *
     * @return CallVerifierInterface The call verifier.
     */
    public function fromCall(CallInterface $call)
    {
        return new CallVerifier(
            $call,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );
    }

    /**
     * Wrap the supplied calls in verifiers.
     *
     * @param array<CallInterface> $calls The calls.
     *
     * @return array<CallVerifierInterface> The call verifiers.
     */
    public function fromCalls(array $calls)
    {
        $verifiers = array();

        foreach ($calls as $call) {
            $verifiers[] = new CallVerifier(
            $call,
                $this->matcherFactory,
                $this->matcherVerifier,
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
    private $assertionRecorder;
    private $assertionRenderer;
    private $invocableInspector;
}
