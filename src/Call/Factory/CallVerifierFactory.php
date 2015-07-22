<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
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
 *
 * @internal
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
     * @param MatcherFactoryInterface|null     $matcherFactory     The matcher factory to use.
     * @param MatcherVerifierInterface|null    $matcherVerifier    The macther verifier to use.
     * @param AssertionRecorderInterface|null  $assertionRecorder  The assertion recorder to use.
     * @param AssertionRendererInterface|null  $assertionRenderer  The assertion renderer to use.
     * @param InvocableInspectorInterface|null $invocableInspector The invocable inspector to use.
     */
    public function __construct(
        MatcherFactoryInterface $matcherFactory = null,
        MatcherVerifierInterface $matcherVerifier = null,
        AssertionRecorderInterface $assertionRecorder = null,
        AssertionRendererInterface $assertionRenderer = null,
        InvocableInspectorInterface $invocableInspector = null
    ) {
        if (null === $matcherFactory) {
            $matcherFactory = MatcherFactory::instance();
        }
        if (null === $matcherVerifier) {
            $matcherVerifier = MatcherVerifier::instance();
        }
        if (null === $assertionRecorder) {
            $assertionRecorder = AssertionRecorder::instance();
        }
        if (null === $assertionRenderer) {
            $assertionRenderer = AssertionRenderer::instance();
        }
        if (null === $invocableInspector) {
            $invocableInspector = InvocableInspector::instance();
        }

        $this->matcherFactory = $matcherFactory;
        $this->matcherVerifier = $matcherVerifier;
        $this->assertionRecorder = $assertionRecorder;
        $this->assertionRenderer = $assertionRenderer;
        $this->invocableInspector = $invocableInspector;
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
     * Get the invocable inspector.
     *
     * @return InvocableInspectorInterface The invocable inspector.
     */
    public function invocableInspector()
    {
        return $this->invocableInspector;
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
            $this->matcherVerifier,
            $this->assertionRecorder,
            $this->assertionRenderer,
            $this->invocableInspector
        );
    }

    /**
     * Wrap the supplied calls in verifiers, or return unchanged if already
     * wrapped.
     *
     * @param array<CallInterface|CallVerifierInterface> $calls The calls.
     *
     * @return array<CallVerifierInterface> The call verifiers.
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
    private $assertionRecorder;
    private $assertionRenderer;
    private $invocableInspector;
}
