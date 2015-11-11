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

use Eloquent\Phony\Invocation\InvocableInspector;
use Eloquent\Phony\Invocation\InvocableInspectorInterface;
use Eloquent\Phony\Invocation\Invoker;
use Eloquent\Phony\Invocation\InvokerInterface;
use Eloquent\Phony\Matcher\Factory\MatcherFactory;
use Eloquent\Phony\Matcher\Factory\MatcherFactoryInterface;
use Eloquent\Phony\Matcher\Verification\MatcherVerifier;
use Eloquent\Phony\Matcher\Verification\MatcherVerifierInterface;
use Eloquent\Phony\Sequencer\Sequencer;
use Eloquent\Phony\Sequencer\SequencerInterface;
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
     * @param SequencerInterface|null          $labelSequencer     The label sequencer to use.
     * @param MatcherFactoryInterface|null     $matcherFactory     The matcher factory to use.
     * @param MatcherVerifierInterface|null    $matcherVerifier    The matcher verifier to use.
     * @param InvokerInterface|null            $invoker            The invoker to use.
     * @param InvocableInspectorInterface|null $invocableInspector The invocable inspector to use.
     */
    public function __construct(
        SequencerInterface $labelSequencer = null,
        MatcherFactoryInterface $matcherFactory = null,
        MatcherVerifierInterface $matcherVerifier = null,
        InvokerInterface $invoker = null,
        InvocableInspectorInterface $invocableInspector = null
    ) {
        if (null === $labelSequencer) {
            $labelSequencer = Sequencer::sequence('stub-label');
        }
        if (null === $matcherFactory) {
            $matcherFactory = MatcherFactory::instance();
        }
        if (null === $matcherVerifier) {
            $matcherVerifier = MatcherVerifier::instance();
        }
        if (null === $invoker) {
            $invoker = Invoker::instance();
        }
        if (null === $invocableInspector) {
            $invocableInspector = InvocableInspector::instance();
        }

        $this->labelSequencer = $labelSequencer;
        $this->matcherFactory = $matcherFactory;
        $this->matcherVerifier = $matcherVerifier;
        $this->invoker = $invoker;
        $this->invocableInspector = $invocableInspector;
    }

    /**
     * Get the label sequencer.
     *
     * @return SequencerInterface The label sequencer.
     */
    public function labelSequencer()
    {
        return $this->labelSequencer;
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
     * Get the invoker.
     *
     * @return InvokerInterface The invoker.
     */
    public function invoker()
    {
        return $this->invoker;
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
     * Create a new stub.
     *
     * @param callable|null $callback              The callback, or null to create an unbound stub.
     * @param mixed         $self                  The self value.
     * @param callable|null $defaultAnswerCallback The callback to use when creating a default answer.
     *
     * @return StubInterface The newly created stub.
     */
    public function create(
        $callback = null,
        $self = null,
        $defaultAnswerCallback = null
    ) {
        return new Stub(
            $callback,
            $self,
            strval($this->labelSequencer->next()),
            $defaultAnswerCallback,
            $this->matcherFactory,
            $this->matcherVerifier,
            $this->invoker,
            $this->invocableInspector
        );
    }

    private static $instance;
    private $labelSequencer;
    private $matcherFactory;
    private $matcherVerifier;
    private $invoker;
    private $invocableInspector;
}
